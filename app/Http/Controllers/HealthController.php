<?php

namespace App\Http\Controllers;

use App\Models\DiseaseRecord;
use App\Models\Pigeon;
use App\Models\VaccinationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // Load only lightweight columns needed for dropdowns & counts
        $pigeonsQuery = Pigeon::select('id', 'ring_number', 'nickname', 'status', 'owner_id')
            ->with('owner:id,name')
            ->orderBy('ring_number');

        if (!$isAdmin) {
            $pigeonsQuery->where('owner_id', $user->id);
        }
        $pigeons = $pigeonsQuery->get();

        $activeDiseasesQuery = DiseaseRecord::with(['pigeon:id,ring_number,nickname,owner_id', 'pigeon.owner:id,name'])
            ->where(function ($q) {
                $q->where('recovery_status', 'under_treatment')
                  ->orWhere('is_quarantined', true);
            })
            ->orderByDesc('diagnosis_date');

        if (!$isAdmin) {
            $activeDiseasesQuery->whereHas('pigeon', fn ($q) => $q->where('owner_id', $user->id));
        }
        $activeDiseases = $activeDiseasesQuery->get();

        $upcomingVaccinationsQuery = VaccinationRecord::with(['pigeon:id,ring_number,nickname,owner_id', 'pigeon.owner:id,name'])
            ->whereNotNull('next_due_date')
            ->orderBy('next_due_date', 'asc');

        if (!$isAdmin) {
            $upcomingVaccinationsQuery->whereHas('pigeon', fn ($q) => $q->where('owner_id', $user->id));
        }
        $upcomingVaccinations = $upcomingVaccinationsQuery->get();

        $recentVaccinationsQuery = VaccinationRecord::with(['pigeon:id,ring_number,nickname,owner_id', 'pigeon.owner:id,name'])
            ->orderByDesc('id')
            ->limit(50);

        if (!$isAdmin) {
            $recentVaccinationsQuery->whereHas('pigeon', fn ($q) => $q->where('owner_id', $user->id));
        }
        $recentVaccinations = $recentVaccinationsQuery->get();

        $diseaseHistoryQuery = DiseaseRecord::with(['pigeon:id,ring_number,nickname,owner_id', 'pigeon.owner:id,name'])
            ->orderByDesc('diagnosis_date')
            ->limit(25);

        if (!$isAdmin) {
            $diseaseHistoryQuery->whereHas('pigeon', fn ($q) => $q->where('owner_id', $user->id));
        }
        $diseaseHistory = $diseaseHistoryQuery->get();

        $stats = [
            'quarantined_count' => $activeDiseases->where('is_quarantined', true)->count(),
            'under_treatment_count' => $activeDiseases->where('recovery_status', 'under_treatment')->count(),
            'overdue_vaccines_count' => $upcomingVaccinations->filter(fn ($v) => $v->next_due_date->isPast() && !$v->next_due_date->isToday())->count(),
            'due_soon_vaccines_count' => $upcomingVaccinations->filter(fn ($v) => $v->next_due_date->isToday() || ($v->next_due_date->isFuture() && $v->next_due_date->diffInDays(now()) <= 14))->count(),
        ];

        return view('health.index', compact(
            'pigeons',
            'activeDiseases',
            'upcomingVaccinations',
            'recentVaccinations',
            'diseaseHistory',
            'stats',
            'isAdmin'
        ));
    }

    public function storeVaccination(Request $request)
    {
        $validated = $request->validate([
            'pigeon_id' => 'required|exists:pigeons,id',
            'vaccine_name' => 'required|string|max:255',
            'administered_date' => 'required|date',
            'next_due_date' => 'nullable|date|after_or_equal:administered_date',
            'notes' => 'nullable|string',
        ]);

        $this->authorizePigeonAccess((int) $validated['pigeon_id']);

        VaccinationRecord::create($validated);

        return redirect()->route('health.index')->with('success', 'Vaccination record logged.');
    }

    public function storeBulkVaccination(Request $request)
    {
        $validated = $request->validate([
            'target_scope' => 'required|in:all_active,selected',
            'pigeon_ids' => 'required_if:target_scope,selected|array',
            'pigeon_ids.*' => 'exists:pigeons,id',
            'vaccine_name' => 'required|string|max:255',
            'administered_date' => 'required|date',
            'next_due_date' => 'nullable|date|after_or_equal:administered_date',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $query = Pigeon::query();

        if ($user->role !== 'admin') {
            $query->where('owner_id', $user->id);
        }

        if ($validated['target_scope'] === 'all_active') {
            $query->where('status', '!=', 'deceased');
        } else {
            $query->whereIn('id', $validated['pigeon_ids'] ?? []);
        }

        $validPigeonIds = $query->pluck('id')->all();

        if (empty($validPigeonIds)) {
            return redirect()->route('health.index')->with('error', 'No eligible pigeons found to vaccinate.');
        }

        $now = now();
        $records = array_map(fn ($id) => [
            'pigeon_id' => $id,
            'vaccine_name' => $validated['vaccine_name'],
            'administered_date' => $validated['administered_date'],
            'next_due_date' => $validated['next_due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $validPigeonIds);

        // Chunk inserts into batches of 150 to stay well under SQLite variable limits
        DB::transaction(function () use ($records) {
            foreach (array_chunk($records, 150) as $chunk) {
                DB::table('vaccination_records')->insert($chunk);
            }
        });

        return redirect()->route('health.index')
            ->with('success', 'Vaccination logged for ' . count($validPigeonIds) . ' pigeon(s).');
    }

    public function storeDisease(Request $request)
    {
        $validated = $request->validate([
            'pigeon_id' => 'required|exists:pigeons,id',
            'disease_name' => 'required|string|max:255',
            'diagnosis_date' => 'required|date',
            'treatment_given' => 'nullable|string',
            'is_quarantined' => 'nullable|boolean',
            'recovery_status' => 'required|in:under_treatment,recovered,deceased',
            'recovery_date' => 'nullable|date|after_or_equal:diagnosis_date',
            'notes' => 'nullable|string',
        ]);

        $this->authorizePigeonAccess((int) $validated['pigeon_id']);

        $validated['is_quarantined'] = $request->boolean('is_quarantined');

        DiseaseRecord::create($validated);

        if ($validated['recovery_status'] === 'deceased') {
            Pigeon::where('id', $validated['pigeon_id'])->update(['status' => 'deceased']);
        }

        return redirect()->route('health.index')->with('success', 'Disease record logged.');
    }

    public function updateDiseaseStatus(Request $request, DiseaseRecord $disease)
    {
        $this->authorizePigeonAccess($disease->pigeon_id);

        $validated = $request->validate([
            'recovery_status' => 'required|in:under_treatment,recovered,deceased',
            'is_quarantined' => 'nullable|boolean',
            'recovery_date' => 'nullable|date',
        ]);

        $isQuarantined = in_array($validated['recovery_status'], ['recovered', 'deceased'])
            ? false
            : $request->boolean('is_quarantined');

        $recoveryDate = $validated['recovery_date']
            ?? ($validated['recovery_status'] === 'recovered' ? now()->toDateString() : null);

        $disease->update([
            'recovery_status' => $validated['recovery_status'],
            'is_quarantined' => $isQuarantined,
            'recovery_date' => $recoveryDate,
        ]);

        if ($validated['recovery_status'] === 'deceased') {
            $disease->pigeon()->update(['status' => 'deceased']);
        }

        return redirect()->route('health.index')->with('success', 'Health status updated.');
    }

    private function authorizePigeonAccess(int $pigeonId): void
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return;
        }

        $ownsPigeon = Pigeon::where('id', $pigeonId)
            ->where('owner_id', $user->id)
            ->exists();

        abort_unless($ownsPigeon, 403, 'Unauthorized action for this pigeon.');
    }
}
