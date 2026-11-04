@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 mb-0">Edit Pigeon: {{ $pigeon->ring_number }}</h1>
            <small class="text-muted">{{ $pigeon->nickname ? '"' . $pigeon->nickname . '"' : 'No Nickname' }}</small>
        </div>
        <div>
            <a href="/pigeons/{{ $pigeon->id }}" class="btn btn-outline-secondary btn-sm">Back to Details</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="/pigeons/{{ $pigeon->id }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ring Number <span class="text-danger">*</span></label>
                        <input type="text" name="ring_number" class="form-control" value="{{ old('ring_number', $pigeon->ring_number) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nickname</label>
                        <input type="text" name="nickname" class="form-control" value="{{ old('nickname', $pigeon->nickname) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="male" {{ old('gender', $pigeon->gender) === 'male' ? 'selected' : '' }}>Cock (Male)</option>
                            <option value="female" {{ old('gender', $pigeon->gender) === 'female' ? 'selected' : '' }}>Hen (Female)</option>
                            <option value="unknown" {{ old('gender', $pigeon->gender) === 'unknown' ? 'selected' : '' }}>Unknown</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Breed Type</label>
                        <select name="breed_type" class="form-select">
                            <option value="pure" {{ old('breed_type', $pigeon->breed_type) === 'pure' ? 'selected' : '' }}>Pure</option>
                            <option value="cross" {{ old('breed_type', $pigeon->breed_type) === 'cross' ? 'selected' : '' }}>Cross</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Strain / Breed</label>
                        <input type="text" name="strain_breed" class="form-control" list="strainOptions" value="{{ old('strain_breed', $pigeon->strain_breed) }}">
                        <datalist id="strainOptions">
                            @foreach ($strains as $strain)
                                <option value="{{ $strain }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Color</label>
                        <input type="text" name="color" class="form-control" list="colorOptions" value="{{ old('color', $pigeon->color) }}">
                        <datalist id="colorOptions">
                            @foreach ($colors as $color)
                                <option value="{{ $color }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Hatch Date</label>
                        <input type="date" name="hatch_date" class="form-control" value="{{ old('hatch_date', $pigeon->hatch_date ? \Carbon\Carbon::parse($pigeon->hatch_date)->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status', $pigeon->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="breeding" {{ old('status', $pigeon->status) === 'breeding' ? 'selected' : '' }}>Breeding</option>
                            <option value="racing" {{ old('status', $pigeon->status) === 'racing' ? 'selected' : '' }}>Racing</option>
                            <option value="sold" {{ old('status', $pigeon->status) === 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="deceased" {{ old('status', $pigeon->status) === 'deceased' ? 'selected' : '' }}>Deceased</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Sire (Father)</label>
                        <select name="father_id" class="form-select">
                            <option value="">-- None / Unknown --</option>
                            @foreach ($potentialFathers as $father)
                                <option value="{{ $father->id }}" {{ old('father_id', $pigeon->father_id) == $father->id ? 'selected' : '' }}>
                                    {{ $father->ring_number }} {{ $father->nickname ? "({$father->nickname})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Dam (Mother)</label>
                        <select name="mother_id" class="form-select">
                            <option value="">-- None / Unknown --</option>
                            @foreach ($potentialMothers as $mother)
                                <option value="{{ $mother->id }}" {{ old('mother_id', $pigeon->mother_id) == $mother->id ? 'selected' : '' }}>
                                    {{ $mother->ring_number }} {{ $mother->nickname ? "({$mother->nickname})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Profile Picture</label>
                        <input type="file" name="profile_pic" class="form-control" accept="image/*">
                        @if($pigeon->profile_pic_path)
                            <small class="text-muted d-block mt-1">Current: <a href="{{ asset('storage/' . $pigeon->profile_pic_path) }}" target="_blank">View File</a></small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Eye Picture</label>
                        <input type="file" name="eye_pic" class="form-control" accept="image/*">
                        @if($pigeon->eye_pic_path)
                            <small class="text-muted d-block mt-1">Current: <a href="{{ asset('storage/' . $pigeon->eye_pic_path) }}" target="_blank">View File</a></small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Wing Picture</label>
                        <input type="file" name="wing_pic" class="form-control" accept="image/*">
                        @if($pigeon->wing_pic_path)
                            <small class="text-muted d-block mt-1">Current: <a href="{{ asset('storage/' . $pigeon->wing_pic_path) }}" target="_blank">View File</a></small>
                        @endif
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $pigeon->notes) }}</textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <a href="/pigeons/{{ $pigeon->id }}" class="btn btn-secondary btn-sm me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Update Pigeon</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
