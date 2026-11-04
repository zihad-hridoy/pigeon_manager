@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Pigeon Loft Registry</h2>
    <a href="/pigeons/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Register New Pigeon</a>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="/pigeons" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search Ring or Name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="gender" class="form-select">
                    <option value="">All Genders</option>
                    <option value="Cock" {{ request('gender') == 'Cock' ? 'selected' : '' }}>Cock</option>
                    <option value="Hen" {{ request('gender') == 'Hen' ? 'selected' : '' }}>Hen</option>
                    <option value="Unknown" {{ request('gender') == 'Unknown' ? 'selected' : '' }}>Unknown</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="strain_breed" class="form-select">
                    <option value="">All Strains</option>
                    @foreach($strains as $sb)
                        <option value="{{ $sb }}" {{ request('strain_breed') == $sb ? 'selected' : '' }}>{{ $sb }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">Filter</button>
                <a href="/pigeons" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Photo</th>
                    <th>Ring Number</th>
                    <th>Nickname</th>
                    <th>Gender</th>
                    <th>Strain / Breed</th>
                    <th>Color</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pigeons as $pigeon)
                    <tr>
                        <td style="width: 60px;">
                            @if($pigeon->profile_pic_path)
                                <img src="{{ asset('storage/' . $pigeon->profile_pic_path) }}" class="rounded-circle object-fit-cover" width="48" height="48" alt="Profile">
                            @else
                                <div class="bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                    <i class="bi bi-feather fs-4"></i>
                                </div>
                            @endif
                        </td>
                        <td class="fw-bold"><a href="/pigeons/{{ $pigeon->id }}" class="text-decoration-none">{{ $pigeon->ring_number }}</a></td>
                        <td>{{ $pigeon->nickname ?? '—' }}</td>
                        <td>
                            @if(in_array($pigeon->gender, ['male', 'Cock']))
                                <span class="badge bg-primary">Cock</span>
                            @elseif(in_array($pigeon->gender, ['female', 'Hen']))
                                <span class="badge bg-danger">Hen</span>
                            @else
                                <span class="badge bg-secondary">Unknown</span>
                            @endif
                        </td>
                        <td>{{ $pigeon->strain_breed ?? '—' }}</td>
                        <td>{{ $pigeon->color ?? '—' }}</td>
                        <td>
                            <span class="badge bg-info text-dark">{{ $pigeon->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="/pigeons/{{ $pigeon->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="/pigeons/{{ $pigeon->id }}/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No pigeons found. Click "Register New Pigeon" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pigeons->hasPages())
        <div class="card-footer bg-white">
            {{ $pigeons->links() }}
        </div>
    @endif
</div>
@endsection
