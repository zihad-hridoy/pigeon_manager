<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Pigeon;
use App\Models\PigeonMetadata;
use App\Models\RingGeneratorConfig;

class PigeonController extends Controller
{
    public function index(Request $request)
    {
        $query = Pigeon::where('owner_id', Auth::id());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('ring_number', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        if ($request->filled('gender')) {
            $mappedGender = match(strtolower($request->gender)) {
                'cock', 'male' => 'male',
                'hen', 'female' => 'female',
                default => 'unknown',
            };
            $query->where('gender', $mappedGender);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('strain_breed')) {
            $query->where('strain_breed', $request->strain_breed);
        }

        $pigeons = $query->latest()->paginate(15)->withQueryString();

        $strains = PigeonMetadata::where('type', 'strain_breed')->pluck('item_value');
        $statuses = PigeonMetadata::where('type', 'status_tag')->pluck('item_value');

        return view('pigeons.index', compact('pigeons', 'strains', 'statuses'));
    }

    public function create()
    {
        $userId = Auth::id();
        $strains = PigeonMetadata::where('type', 'strain_breed')->pluck('item_value');
        $colors = PigeonMetadata::where('type', 'color')->pluck('item_value');
        $statuses = PigeonMetadata::where('type', 'status_tag')->pluck('item_value');

        $potentialFathers = Pigeon::where('owner_id', $userId)->where('gender', 'male')->get();
        $potentialMothers = Pigeon::where('owner_id', $userId)->where('gender', 'female')->get();

        $config = RingGeneratorConfig::where('owner_id', $userId)->first();
        $suggestedRing = null;
        if ($config) {
            $suggestedRing = $config->prefix . $config->year_format . str_pad($config->last_sequence_number + 1, 4, '0', STR_PAD_LEFT);
        }

        return view('pigeons.create', compact('strains', 'colors', 'statuses', 'potentialFathers', 'potentialMothers', 'suggestedRing'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ring_number' => ['required', 'string', 'max:255', 'unique:pigeons,ring_number'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string'],
            'breed_type' => ['nullable', 'string'],
            'strain_breed' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'hatch_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'father_id' => ['nullable', 'exists:pigeons,id'],
            'mother_id' => ['nullable', 'exists:pigeons,id'],
            'profile_pic' => ['nullable', 'image', 'max:4096'],
            'eye_pic' => ['nullable', 'image', 'max:4096'],
            'wing_pic' => ['nullable', 'image', 'max:4096'],
            'notes' => ['nullable', 'string'],
        ]);

        $gender = match(strtolower($request->input('gender', 'unknown'))) {
            'cock', 'male' => 'male',
            'hen', 'female' => 'female',
            default => 'unknown',
        };

        $breedType = strtolower($request->input('breed_type', 'pure'));
        if (!in_array($breedType, ['pure', 'cross'])) {
            $breedType = 'pure';
        }

        $data = [
            'owner_id' => Auth::id(),
            'ring_number' => $request->ring_number,
            'nickname' => $request->nickname,
            'gender' => $gender,
            'breed_type' => $breedType,
            'strain_breed' => $request->strain_breed,
            'color' => $request->color,
            'hatch_date' => $request->hatch_date,
            'status' => $request->input('status', 'active'),
            'father_id' => $request->filled('father_id') ? $request->father_id : null,
            'mother_id' => $request->filled('mother_id') ? $request->mother_id : null,
            'notes' => $request->notes,
        ];

        if ($request->hasFile('profile_pic')) {
            $data['profile_pic_path'] = $request->file('profile_pic')->store('pigeons/profiles', 'public');
        }
        if ($request->hasFile('eye_pic')) {
            $data['eye_pic_path'] = $request->file('eye_pic')->store('pigeons/eyes', 'public');
        }
        if ($request->hasFile('wing_pic')) {
            $data['wing_pic_path'] = $request->file('wing_pic')->store('pigeons/wings', 'public');
        }

        $pigeon = Pigeon::create($data);

        $config = RingGeneratorConfig::where('owner_id', Auth::id())->first();
        if ($config && str_starts_with($request->ring_number, $config->prefix . $config->year_format)) {
            $config->increment('last_sequence_number');
        }

        return redirect('/pigeons')->with('success', "Pigeon {$pigeon->ring_number} registered successfully.");
    }

    public function show(Pigeon $pigeon)
    {
        abort_if($pigeon->owner_id !== Auth::id(), 403);
        $pigeon->load(['father.father', 'father.mother', 'mother.father', 'mother.mother']);
        return view('pigeons.show', compact('pigeon'));
    }

    public function edit(Pigeon $pigeon)
    {
        abort_if($pigeon->owner_id !== Auth::id(), 403);
        $userId = Auth::id();

        $strains = PigeonMetadata::where('type', 'strain_breed')->pluck('item_value');
        $colors = PigeonMetadata::where('type', 'color')->pluck('item_value');
        $statuses = PigeonMetadata::where('type', 'status_tag')->pluck('item_value');

        $potentialFathers = Pigeon::where('owner_id', $userId)->where('gender', 'male')->where('id', '!=', $pigeon->id)->get();
        $potentialMothers = Pigeon::where('owner_id', $userId)->where('gender', 'female')->where('id', '!=', $pigeon->id)->get();

        return view('pigeons.edit', compact('pigeon', 'strains', 'colors', 'statuses', 'potentialFathers', 'potentialMothers'));
    }

    public function update(Request $request, Pigeon $pigeon)
    {
        abort_if($pigeon->owner_id !== Auth::id(), 403);

        $request->validate([
            'ring_number' => ['required', 'string', 'max:255', 'unique:pigeons,ring_number,' . $pigeon->id],
            'nickname' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string'],
            'breed_type' => ['nullable', 'string'],
            'strain_breed' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'hatch_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'father_id' => ['nullable', 'exists:pigeons,id'],
            'mother_id' => ['nullable', 'exists:pigeons,id'],
            'profile_pic' => ['nullable', 'image', 'max:4096'],
            'eye_pic' => ['nullable', 'image', 'max:4096'],
            'wing_pic' => ['nullable', 'image', 'max:4096'],
            'notes' => ['nullable', 'string'],
        ]);

        $gender = match(strtolower($request->input('gender', $pigeon->gender))) {
            'cock', 'male' => 'male',
            'hen', 'female' => 'female',
            default => 'unknown',
        };

        $breedType = strtolower($request->input('breed_type', $pigeon->breed_type ?? 'pure'));
        if (!in_array($breedType, ['pure', 'cross'])) {
            $breedType = 'pure';
        }

        $data = [
            'ring_number' => $request->ring_number,
            'nickname' => $request->nickname,
            'gender' => $gender,
            'breed_type' => $breedType,
            'strain_breed' => $request->strain_breed,
            'color' => $request->color,
            'hatch_date' => $request->hatch_date,
            'status' => $request->input('status', $pigeon->status),
            'father_id' => $request->filled('father_id') ? $request->father_id : null,
            'mother_id' => $request->filled('mother_id') ? $request->mother_id : null,
            'notes' => $request->notes,
        ];

        if ($request->hasFile('profile_pic')) {
            if ($pigeon->profile_pic_path) Storage::disk('public')->delete($pigeon->profile_pic_path);
            $data['profile_pic_path'] = $request->file('profile_pic')->store('pigeons/profiles', 'public');
        }
        if ($request->hasFile('eye_pic')) {
            if ($pigeon->eye_pic_path) Storage::disk('public')->delete($pigeon->eye_pic_path);
            $data['eye_pic_path'] = $request->file('eye_pic')->store('pigeons/eyes', 'public');
        }
        if ($request->hasFile('wing_pic')) {
            if ($pigeon->wing_pic_path) Storage::disk('public')->delete($pigeon->wing_pic_path);
            $data['wing_pic_path'] = $request->file('wing_pic')->store('pigeons/wings', 'public');
        }

        $pigeon->update($data);

        return redirect('/pigeons/' . $pigeon->id)->with('success', "Pigeon updated successfully.");
    }

    public function destroy(Pigeon $pigeon)
    {
        abort_if($pigeon->owner_id !== Auth::id(), 403);

        if ($pigeon->profile_pic_path) Storage::disk('public')->delete($pigeon->profile_pic_path);
        if ($pigeon->eye_pic_path) Storage::disk('public')->delete($pigeon->eye_pic_path);
        if ($pigeon->wing_pic_path) Storage::disk('public')->delete($pigeon->wing_pic_path);

        $pigeon->delete();

        return redirect('/pigeons')->with('success', 'Pigeon removed from records.');
    }
}
