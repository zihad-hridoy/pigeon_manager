<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pigeon;
use App\Models\VaccinationRecord;
use App\Models\DiseaseRecord;

class MedicalRecordController extends Controller
{
    public function index(Pigeon $pigeon)
    {
        abort_if($pigeon->owner_id !== Auth::id(), 403);

        $vaccinations = $pigeon->vaccinations()->latest('administered_date')->get();
        $diseases = $pigeon->diseaseRecords()->latest('diagnosis_date')->get();

        return view('medical.index', compact('pigeon', 'vaccinations', 'diseases'));
    }

    public function storeVaccination(Request $request, Pigeon $pigeon)
    {
        abort_if($pigeon->owner_id !== Auth::id(), 403);

        $validated = $request->validate([
            'vaccine_name' => ['required', 'string', 'max:255'],
            'administered_date' => ['required', 'date'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:administered_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $pigeon->vaccinations()->create($validated);

        return back()->with('success', 'Vaccination record added.');
    }

    public function storeDisease(Request $request, Pigeon $pigeon)
    {
        abort_if($pigeon->owner_id !== Auth::id(), 403);

        $validated = $request->validate([
            'disease_name' => ['required', 'string', 'max:255'],
            'diagnosis_date' => ['required', 'date'],
            'treatment_given' => ['nullable', 'string'],
            'is_quarantined' => ['nullable', 'boolean'],
            'recovery_status' => ['required', 'in:under_treatment,recovered,deceased'],
            'recovery_date' => ['nullable', 'date', 'after_or_equal:diagnosis_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_quarantined'] = $request->boolean('is_quarantined');

        $pigeon->diseaseRecords()->create($validated);

        return back()->with('success', 'Medical condition record logged.');
    }

    public function destroyVaccination(VaccinationRecord $record)
    {
        abort_if($record->pigeon->owner_id !== Auth::id(), 403);
        $record->delete();

        return back()->with('success', 'Vaccination entry removed.');
    }

    public function destroyDisease(DiseaseRecord $record)
    {
        abort_if($record->pigeon->owner_id !== Auth::id(), 403);
        $record->delete();

        return back()->with('success', 'Disease entry removed.');
    }
}
