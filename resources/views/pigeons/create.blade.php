@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Register Pigeon</h3>
            <a href="/pigeons" class="btn btn-outline-secondary btn-sm">Back to List</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
@if ($errors->any())
<div class="alert alert-danger">
<ul class="mb-0">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif
                <form action="/pigeons" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ring Number *</label>
                            <input type="text" name="ring_number" class="form-control @error('ring_number') is-invalid @enderror" value="{{ old('ring_number', $suggestedRing) }}" required>
                            @error('ring_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nickname</label>
                            <input type="text" name="nickname" class="form-control" value="{{ old('nickname') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="male" {{ old('gender') == 'Cock' ? 'selected' : '' }}>Cock</option>
                                <option value="female" {{ old('gender') == 'Hen' ? 'selected' : '' }}>Hen</option>
                                <option value="unknown" {{ old('gender') == 'Unknown' ? 'selected' : '' }}>Unknown</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Strain / Breed</label>
                            <input list="strainsList" name="strain_breed" class="form-control" value="{{ old('strain_breed') }}" placeholder="Type or select...">
                            <datalist id="strainsList">
                                @foreach($strains as $strain)
                                    <option value="{{ $strain }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input list="colorsList" name="color" class="form-control" value="{{ old('color') }}" placeholder="Type or select...">
                            <datalist id="colorsList">
                                @foreach($colors as $color)
                                    <option value="{{ $color }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Hatch Date</label>
                            <input type="date" name="hatch_date" class="form-control" value="{{ old('hatch_date') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status *</label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $st)
                                    <option value="{{ $st }}" {{ old('status', 'Active') == $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Father (Sire)</label>
                            <select name="father_id" class="form-select">
                                <option value="">-- No Father Assigned --</option>
                                @foreach($potentialFathers as $father)
                                    <option value="{{ $father->id }}" {{ old('father_id') == $father->id ? 'selected' : '' }}>
                                        {{ $father->ring_number }} {{ $father->nickname ? "({$father->nickname})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mother (Dam)</label>
                            <select name="mother_id" class="form-select">
                                <option value="">-- No Mother Assigned --</option>
                                @foreach($potentialMothers as $mother)
                                    <option value="{{ $mother->id }}" {{ old('mother_id') == $mother->id ? 'selected' : '' }}>
                                        {{ $mother->ring_number }} {{ $mother->nickname ? "({$mother->nickname})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="profile_pic" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Eye Photo</label>
                            <input type="file" name="eye_pic" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Wing Photo</label>
                            <input type="file" name="wing_pic" class="form-control" accept="image/*">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4">Save Pigeon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
