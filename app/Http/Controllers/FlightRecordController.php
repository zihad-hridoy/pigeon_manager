<?php

namespace App\Http\Controllers;

use App\Models\FlightRecord;
use App\Models\Pigeon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlightRecordController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // Accessible pigeons for dropdowns and filters
        $pigeonsQuery = Pigeon::with('owner')->orderBy('ring_number');
        if (!$isAdmin) {
            $pigeonsQuery->where('owner_id', $user->id);
        }
        $pigeons = $pigeonsQuery->get();

        // Distinct strains/bloodlines for family filtering
        $strains = $pigeons->pluck('strain_breed')->filter()->unique()->sort()->values();

        // Base flight records query
        $flightsQuery = FlightRecord::with(['pigeon.owner'])
            ->orderByDesc('flight_date')
            ->orderByDesc('id');

        if (!$isAdmin) {
            $flightsQuery->whereHas('pigeon', fn ($q) => $q->where('owner_id', $user->id));
        }

        // Apply filters
        if ($request->filled('pigeon_id')) {
            $flightsQuery->where('pigeon_id', $request->pigeon_id);
        }
        if ($request->filled('event_type')) {
            $flightsQuery->where('event_type', $request->event_type);
        }
        if ($request->filled('strain_breed')) {
            $flightsQuery->whereHas('pigeon', fn ($q) => $q->where('strain_breed', $request->strain_breed));
        }

        $flights = $flightsQuery->paginate(20)->withQueryString();

        // Overall summary stats (scoped to user/admin)
        $statsBase = FlightRecord::query();
        if (!$isAdmin) {
            $statsBase->whereHas('pigeon', fn ($q) => $q->where('owner_id', $user->id));
        }

        $stats = [
            'total_flights' => (clone $statsBase)->count(),
            'total_distance' => round((float) (clone $statsBase)->sum('distance_km'), 1),
            'avg_speed' => round((float) (clone $statsBase)->whereNotNull('flight_speed_mpm')->avg('flight_speed_mpm'), 1),
            'top_speed' => round((float) (clone $statsBase)->max('flight_speed_mpm'), 1),
        ];

        // Bloodline / Strain Performance Aggregation
        $bloodlineStatsQuery = FlightRecord::join('pigeons', 'flight_records.pigeon_id', '=', 'pigeons.id')
            ->whereNotNull('pigeons.strain_breed')
            ->where('pigeons.strain_breed', '!=', '');

        if (!$isAdmin) {
            $bloodlineStatsQuery->where('pigeons.owner_id', $user->id);
        }

        $bloodlineStats = $bloodlineStatsQuery
            ->select(
                'pigeons.strain_breed',
                DB::raw('COUNT(flight_records.id) as total_flights'),
                DB::raw('COUNT(DISTINCT pigeons.id) as bird_count'),
                DB::raw('ROUND(SUM(COALESCE(flight_records.distance_km, 0)), 1) as total_km'),
                DB::raw('ROUND(AVG(flight_records.flight_speed_mpm), 1) as avg_speed'),
                DB::raw('ROUND(MAX(flight_records.flight_speed_mpm), 1) as max_speed'),
                DB::raw('SUM(CASE WHEN flight_records.rank_position IS NOT NULL AND flight_records.rank_position <= 3 THEN 1 ELSE 0 END) as podiums')
            )
            ->groupBy('pigeons.strain_breed')
            ->orderByDesc('avg_speed')
            ->get();

        return view('flights.index', compact(
            'pigeons',
            'strains',
            'flights',
            'stats',
            'bloodlineStats',
            'isAdmin'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pigeon_id' => 'required|exists:pigeons,id',
            'event_type' => 'required|in:training,race,endurance_test',
            'flight_date' => 'required|date',
            'distance_km' => 'nullable|numeric|min:0|max:9999.99',
            'duration_minutes' => 'nullable|integer|min:1',
            'flight_speed_mpm' => 'nullable|numeric|min:0|max:99999.99',
            'rank_position' => 'nullable|integer|min:1',
            'weather_condition' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $this->authorizePigeonAccess((int) $validated['pigeon_id']);

        // Auto-calculate speed in meters per minute (MPM) if not manually provided
        if (empty($validated['flight_speed_mpm']) && !empty($validated['distance_km']) && !empty($validated['duration_minutes'])) {
            $validated['flight_speed_mpm'] = round(($validated['distance_km'] * 1000) / $validated['duration_minutes'], 2);
        }

        FlightRecord::create($validated);

        return redirect()->route('flights.index')->with('success', 'Flight record logged successfully.');
    }

    public function destroy(FlightRecord $flight)
    {
        $this->authorizePigeonAccess($flight->pigeon_id);

        $flight->delete();

        return redirect()->route('flights.index')->with('success', 'Flight record deleted.');
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
