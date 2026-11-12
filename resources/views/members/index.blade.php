@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-people text-primary me-2"></i>Member Management & Lofts</h3>
        <small class="text-muted">Manage registered breeders and loft profiles</small>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
        <i class="bi bi-person-plus me-1"></i> Register New Member
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="text-muted small">Total Accounts</div>
                <div class="fs-3 fw-bold text-primary">{{ $stats['total_members'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="text-muted small">Active Members</div>
                <div class="fs-3 fw-bold text-success">{{ $stats['active_members'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-info h-100">
            <div class="card-body">
                <div class="text-muted small">Total Birds Across Lofts</div>
                <div class="fs-3 fw-bold text-info">{{ number_format($stats['total_birds']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Registered Owners & Lofts</span>
        <span class="badge bg-secondary">{{ $members->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Owner Name</th>
                    <th>Loft Name</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Birds</th>
                    <th>Status</th>
                    <th class="text-end">Profile</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $m)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($m->profile_pic_path)
                                    <img src="{{ asset('storage/' . $m->profile_pic_path) }}" alt="{{ $m->name }}" class="rounded-circle object-fit-cover" width="36" height="36">
                                @else
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                        {{ strtoupper(substr($m->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('members.show', $m) }}" class="fw-semibold text-decoration-none">
                                        {{ $m->name }}
                                    </a>
                                    <div class="small text-muted">{{ $m->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $m->loft_name ?: '—' }}</td>
                        <td>{{ $m->phone ?: '—' }}</td>
                        <td>
                            @if($m->role === 'admin')
                                <span class="badge bg-dark">Admin</span>
                            @else
                                <span class="badge bg-light text-dark border">Member</span>
                            @endif
                        </td>
                        <td>{{ $m->active_pigeons_count }} active / {{ $m->pigeons_count }} total</td>
                        <td>
                            @if($m->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Suspended</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('members.show', $m) }}" class="btn btn-outline-primary btn-sm">
                                Manage Loft
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Register New Loft Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Owner Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="member" selected>Member (Owner)</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Temporary Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Loft Name</label>
                        <input type="text" name="loft_name" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" maxlength="30">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="profile_pic" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>
@endsection
