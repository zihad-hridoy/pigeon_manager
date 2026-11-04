@extends('layouts.app')

@section('content')
<style>
@media print {
    nav, .navbar, .btn, .alert, footer, .d-print-none {
        display: none !important;
    }
    body, .container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}
</style>

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 mb-0">{{ $pigeon->ring_number }}</h1>
            <small class="text-muted">{{ $pigeon->nickname ? '"' . $pigeon->nickname . '"' : 'No Nickname' }}</small>
        </div>
        <div class="d-print-none">
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm me-2">Print Pedigree</button>
<a href="/pigeons/{{ $pigeon->id }}/medical" class="btn btn-outline-info btn-sm me-2">Medical History</a>
            <a href="/pigeons/{{ $pigeon->id }}/edit" class="btn btn-warning btn-sm me-2">Edit</a>
            <a href="/pigeons" class="btn btn-outline-secondary btn-sm">Back to List</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 d-print-none">{{ session('success') }}</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Overview</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <strong>Gender:</strong>
                            <span>{{ match(strtolower($pigeon->gender ?? 'unknown')) { 'male' => 'Cock', 'female' => 'Hen', default => 'Unknown' } }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <strong>Breed Type:</strong>
                            <span class="badge bg-secondary text-capitalize">{{ $pigeon->breed_type ?? 'pure' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <strong>Strain / Breed:</strong>
                            <span>{{ $pigeon->strain_breed ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <strong>Color:</strong>
                            <span>{{ $pigeon->color ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <strong>Status:</strong>
                            <span class="badge bg-primary text-capitalize">{{ $pigeon->status ?? 'active' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <strong>Hatch Date:</strong>
                            <span>{{ $pigeon->hatch_date ? \Carbon\Carbon::parse($pigeon->hatch_date)->format('M d, Y') : '—' }}</span>
                        </li>
                    </ul>
                    @if($pigeon->notes)
                        <div class="mt-3">
                            <strong class="d-block text-muted small">Notes:</strong>
                            <p class="small text-secondary mb-0">{{ $pigeon->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-7 col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Photos</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-4 text-center">
                            <span class="small fw-bold text-muted d-block mb-2">Profile</span>
                            @if($pigeon->profile_pic_path)
                                <img src="{{ asset('storage/' . $pigeon->profile_pic_path) }}" class="img-fluid rounded border shadow-sm" style="max-height: 150px; object-fit: cover;" alt="Profile">
                            @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted small" style="height: 150px;">No Profile Image</div>
                            @endif
                        </div>
                        <div class="col-sm-4 text-center">
                            <span class="small fw-bold text-muted d-block mb-2">Eye</span>
                            @if($pigeon->eye_pic_path)
                                <img src="{{ asset('storage/' . $pigeon->eye_pic_path) }}" class="img-fluid rounded border shadow-sm" style="max-height: 150px; object-fit: cover;" alt="Eye">
                            @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted small" style="height: 150px;">No Eye Image</div>
                            @endif
                        </div>
                        <div class="col-sm-4 text-center">
                            <span class="small fw-bold text-muted d-block mb-2">Wing</span>
                            @if($pigeon->wing_pic_path)
                                <img src="{{ asset('storage/' . $pigeon->wing_pic_path) }}" class="img-fluid rounded border shadow-sm" style="max-height: 150px; object-fit: cover;" alt="Wing">
                            @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted small" style="height: 150px;">No Wing Image</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold">3-Generation Pedigree</div>
        <div class="card-body overflow-auto">
            <div class="row text-center g-2" style="min-width: 650px;">
                <div class="col-4">
                    <div class="fw-bold text-uppercase small text-muted mb-2">Parents (Gen 1)</div>
                    <div class="p-3 border rounded mb-3 bg-light">
                        <span class="badge bg-primary mb-1">Sire</span><br>
                        @if($pigeon->father)
                            <a href="/pigeons/{{ $pigeon->father->id }}" class="fw-bold text-decoration-none">{{ $pigeon->father->ring_number }}</a>
                            <div class="small text-muted">{{ $pigeon->father->strain_breed ?? 'No strain' }}</div>
                        @else
                            <span class="text-muted small">Unknown</span>
                        @endif
                    </div>
                    <div class="p-3 border rounded bg-light">
                        <span class="badge bg-danger mb-1">Dam</span><br>
                        @if($pigeon->mother)
                            <a href="/pigeons/{{ $pigeon->mother->id }}" class="fw-bold text-decoration-none">{{ $pigeon->mother->ring_number }}</a>
                            <div class="small text-muted">{{ $pigeon->mother->strain_breed ?? 'No strain' }}</div>
                        @else
                            <span class="text-muted small">Unknown</span>
                        @endif
                    </div>
                </div>

                <div class="col-4">
                    <div class="fw-bold text-uppercase small text-muted mb-2">Grandparents (Gen 2)</div>
                    <div class="p-2 border rounded mb-2 bg-white small">
                        <span class="text-muted d-block small">Paternal Grandsire</span>
                        @if($pigeon->father && $pigeon->father->father)
                            <a href="/pigeons/{{ $pigeon->father->father->id }}">{{ $pigeon->father->father->ring_number }}</a>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </div>
                    <div class="p-2 border rounded mb-3 bg-white small">
                        <span class="text-muted d-block small">Paternal Granddam</span>
                        @if($pigeon->father && $pigeon->father->mother)
                            <a href="/pigeons/{{ $pigeon->father->mother->id }}">{{ $pigeon->father->mother->ring_number }}</a>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </div>
                    <div class="p-2 border rounded mb-2 bg-white small">
                        <span class="text-muted d-block small">Maternal Grandsire</span>
                        @if($pigeon->mother && $pigeon->mother->father)
                            <a href="/pigeons/{{ $pigeon->mother->father->id }}">{{ $pigeon->mother->father->ring_number }}</a>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </div>
                    <div class="p-2 border rounded bg-white small">
                        <span class="text-muted d-block small">Maternal Granddam</span>
                        @if($pigeon->mother && $pigeon->mother->mother)
                            <a href="/pigeons/{{ $pigeon->mother->mother->id }}">{{ $pigeon->mother->mother->ring_number }}</a>
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </div>
                </div>

                <div class="col-4">
                    <div class="fw-bold text-uppercase small text-muted mb-2">Great-Grandparents (Gen 3)</div>
                    <div class="p-1 border rounded mb-1 bg-white" style="font-size: 0.75rem;">
                        {{ $pigeon->father?->father?->father?->ring_number ?? 'Unknown' }}
                    </div>
                    <div class="p-1 border rounded mb-1 bg-white" style="font-size: 0.75rem;">
                        {{ $pigeon->father?->father?->mother?->ring_number ?? 'Unknown' }}
                    </div>
                    <div class="p-1 border rounded mb-1 bg-white" style="font-size: 0.75rem;">
                        {{ $pigeon->father?->mother?->father?->ring_number ?? 'Unknown' }}
                    </div>
                    <div class="p-1 border rounded mb-2 bg-white" style="font-size: 0.75rem;">
                        {{ $pigeon->father?->mother?->mother?->ring_number ?? 'Unknown' }}
                    </div>
                    <div class="p-1 border rounded mb-1 bg-white" style="font-size: 0.75rem;">
                        {{ $pigeon->mother?->father?->father?->ring_number ?? 'Unknown' }}
                    </div>
                    <div class="p-1 border rounded mb-1 bg-white" style="font-size: 0.75rem;">
                        {{ $pigeon->mother?->father?->mother?->ring_number ?? 'Unknown' }}
                    </div>
                    <div class="p-1 border rounded mb-1 bg-white" style="font-size: 0.75rem;">
                        {{ $pigeon->mother?->mother?->father?->ring_number ?? 'Unknown' }}
                    </div>
                    <div class="p-1 border rounded bg-white" style="font-size: 0.75rem;">
                        {{ $pigeon->mother?->mother?->mother?->ring_number ?? 'Unknown' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
