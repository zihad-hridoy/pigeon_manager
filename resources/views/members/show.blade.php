@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div class="d-flex align-items-center gap-3">
        @if($member->profile_pic_path)
            <img src="{{ asset('storage/' . $member->profile_pic_path) }}" alt="{{ $member->name }}" class="rounded-circle object-fit-cover border" width="56" height="56">
        @else
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 56px; height: 56px;">
                {{ strtoupper(substr($member->name, 0, 1)) }}
            </div>
        @endif
        <div>
            <h3 class="mb-0">
                {{ $member->loft_name ?: $member->name . "'s Loft" }}
            </h3>
            <small class="text-muted">
                Owner: <strong>{{ $member->name }}</strong> ({{ $member->email }})
                @if($member->phone) • {{ $member->phone }} @endif
            </small>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($isAdmin)
            <a href="{{ route('members.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> All Members
            </a>
        @endif
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="bi bi-pencil-square me-1"></i> Edit Loft Profile
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="text-muted small">Total Birds</div>
                <div class="fs-4 fw-bold text-primary">{{ $loftStats['total_birds'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="text-muted small">Active Birds</div>
                <div class="fs-4 fw-bold text-success">{{ $loftStats['active_birds'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <div class="text-muted small">Cocks / Hens</div>
                <div class="fs-4 fw-bold text-info">{{ $loftStats['cocks'] }} <span class="fs-6 fw-normal">M</span> / {{ $loftStats['hens'] }} <span class="fs-6 fw-normal">F</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-secondary h-100">
            <div class="card-body">
                <div class="text-muted small">Bloodlines / Strains</div>
                <div class="fs-4 fw-bold text-secondary">{{ $loftStats['strains_count'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Registered Pigeons in This Loft</span>
        <span class="badge bg-secondary">{{ $pigeons->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ring Number</th>
                    <th>Nickname</th>
                    <th>Gender</th>
                    <th>Strain / Bloodline</th>
                    <th>Color</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pigeons as $pigeon)
                    <tr>
                        <td>
                            <a href="{{ url('/pigeons/' . $pigeon->id) }}" class="fw-semibold text-decoration-none">
                                {{ $pigeon->ring_number }}
                            </a>
                        </td>
                        <td>{{ $pigeon->nickname ?: '—' }}</td>
                        <td class="text-capitalize">{{ $pigeon->gender ?: '—' }}</td>
                        <td>{{ $pigeon->strain_breed ?: '—' }}</td>
                        <td>{{ $pigeon->color ?: '—' }}</td>
                        <td>
                            <span class="badge {{ $pigeon->status === 'deceased' ? 'bg-dark' : 'bg-success' }}">
                                {{ ucfirst($pigeon->status ?? 'active') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No pigeons registered under this loft yet.</td>
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

<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('members.update', $member) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title">Edit Loft & Owner Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $member->name }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ $member->email }}" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Loft Name</label>
                        <input type="text" name="loft_name" class="form-control" value="{{ $member->loft_name }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" maxlength="30" value="{{ $member->phone }}">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" name="profile_pic" class="form-control" accept="image/*">
                    </div>
                    <div class="col-6">
                        <label class="form-label">New Password <small class="text-muted">(optional)</small></label>
                        <input type="password" name="password" class="form-control" minlength="6">
                    </div>
                </div>

                @if($isAdmin)
                    <hr>
                    <div class="mb-2">
                        <label class="form-label">Account Role</label>
                        <select name="role" class="form-select">
                            <option value="member" @selected($member->role === 'member')>Member</option>
                            <option value="admin" @selected($member->role === 'admin')>Admin</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="memberActiveCheck" @checked($member->is_active)>
                        <label class="form-check-label" for="memberActiveCheck">Account Active</label>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
