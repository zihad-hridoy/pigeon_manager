@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-trophy text-primary me-2"></i>Flight & Performance Records</h3>
        <small class="text-muted">
            {{ $isAdmin ? 'Viewing all loft flight & race performance (Admin)' : 'Viewing flight & race performance for your loft' }}
        </small>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFlightModal">
        <i class="bi bi-plus-lg me-1"></i> Log Flight / Race
    </button>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="text-muted small">Total Logged Flights</div>
                <div class="fs-3 fw-bold text-primary">{{ $stats['total_flights'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="text-muted small">Total Distance Flown</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($stats['total_distance'], 1) }} <span class="fs-6 fw-normal">km</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <div class="text-muted small">Average Speed</div>
                <div class="fs-3 fw-bold text-info">{{ number_format($stats['avg_speed'], 1) }} <span class="fs-6 fw-normal">mpm</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-warning h-100">
            <div class="card-body">
                <div class="text-muted small">Top Recorded Speed</div>
                <div class="fs-3 fw-bold text-warning">{{ number_format($stats['top_speed'], 1) }} <span class="fs-6 fw-normal">mpm</span></div>
            </div>
        </div>
    </div>
</div>

<!-- Bloodline / Family Performance Comparison -->
@if($bloodlineStats->isNotEmpty())
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <span class="fw-semibold"><i class="bi bi-diagram-3 text-success me-2"></i>Bloodline / Strain Performance Summary</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Strain / Bloodline</th>
                    <th>Birds</th>
                    <th>Flights</th>
                    <th>Total Distance</th>
                    <th>Avg Speed</th>
                    <th>Best Speed</th>
                    <th>Top-3 Finishes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloodlineStats as $line)
                    <tr>
                        <td class="fw-semibold">
                            <a href="{{ route('flights.index', ['strain_breed' => $line->strain_breed]) }}" class="text-decoration-none">
                                {{ $line->strain_breed }}
                            </a>
                        </td>
                        <td>{{ $line->bird_count }}</td>
                        <td>{{ $line->total_flights }}</td>
                        <td>{{ number_format($line->total_km, 1) }} km</td>
                        <td>{{ $line->avg_speed ? number_format($line->avg_speed, 1) . ' mpm' : '—' }}</td>
                        <td class="fw-medium text-success">{{ $line->max_speed ? number_format($line->max_speed, 1) . ' mpm' : '—' }}</td>
                        <td><span class="badge bg-warning text-dark">{{ $line->podiums }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Filter Bar -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('flights.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Pigeon</label>
                <select name="pigeon_id" class="form-select form-select-sm">
                    <option value="">All Pigeons</option>
                    @foreach($pigeons as $p)
                        <option value="{{ $p->id }}" @selected(request('pigeon_id') == $p->id)>
                            {{ $p->ring_number }} {{ $p->nickname ? '(' . $p->nickname . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Bloodline / Strain</label>
                <select name="strain_breed" class="form-select form-select-sm">
                    <option value="">All Bloodlines</option>
                    @foreach($strains as $strain)
                        <option value="{{ $strain }}" @selected(request('strain_breed') === $strain)>{{ $strain }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Event Type</label>
                <select name="event_type" class="form-select form-select-sm">
                    <option value="">All Events</option>
                    <option value="training" @selected(request('event_type') === 'training')>Training</option>
                    <option value="race" @selected(request('event_type') === 'race')>Race</option>
                    <option value="endurance_test" @selected(request('event_type') === 'endurance_test')>Endurance Test</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark btn-sm w-100">Filter</button>
                <a href="{{ route('flights.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Flight Log Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-list-ul me-2"></i>Flight Log</span>
        <span class="badge bg-secondary">{{ $flights->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Ring Number</th>
                    @if($isAdmin)<th>Owner</th>@endif
                    <th>Bloodline</th>
                    <th>Event</th>
                    <th>Distance</th>
                    <th>Duration</th>
                    <th>Speed (MPM)</th>
                    <th>Rank</th>
                    <th>Weather</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($flights as $flight)
                    <tr>
                        <td>{{ $flight->flight_date->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ url('/pigeons/' . $flight->pigeon_id) }}" class="fw-semibold text-decoration-none">
                                {{ $flight->pigeon->ring_number }}
                            </a>
                            @if($flight->pigeon->nickname)
                                <div class="small text-muted">{{ $flight->pigeon->nickname }}</div>
                            @endif
                        </td>
                        @if($isAdmin)
                            <td>{{ $flight->pigeon->owner->name ?? 'N/A' }}</td>
                        @endif
                        <td>{{ $flight->pigeon->strain_breed ?: '—' }}</td>
                        <td>
                            @if($flight->event_type === 'race')
                                <span class="badge bg-primary">Race</span>
                            @elseif($flight->event_type === 'endurance_test')
                                <span class="badge bg-info text-dark">Endurance</span>
                            @else
                                <span class="badge bg-secondary">Training</span>
                            @endif
                        </td>
                        <td>{{ $flight->distance_km !== null ? number_format($flight->distance_km, 2) . ' km' : '—' }}</td>
                        <td>{{ $flight->duration_minutes !== null ? $flight->duration_minutes . ' min' : '—' }}</td>
                        <td class="fw-semibold">{{ $flight->flight_speed_mpm !== null ? number_format($flight->flight_speed_mpm, 2) : '—' }}</td>
                        <td>
                            @if($flight->rank_position)
                                <span class="badge {{ $flight->rank_position <= 3 ? 'bg-warning text-dark' : 'bg-light text-dark border' }}">
                                    #{{ $flight->rank_position }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $flight->weather_condition ?: '—' }}</td>
                        <td class="text-end">
                            <form action="{{ route('flights.destroy', $flight) }}" method="POST" onsubmit="return confirm('Delete this flight record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 11 : 10 }}" class="text-center text-muted py-4">
                            No flight records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($flights->hasPages())
        <div class="card-footer bg-white">
            {{ $flights->links() }}
        </div>
    @endif
</div>

<!-- Log Flight Modal -->
<div class="modal fade" id="addFlightModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('flights.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Log Flight / Race Performance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Pigeon <span class="text-danger">*</span></label>
                    <select name="pigeon_id" class="form-select" required>
                        <option value="">-- Select Pigeon --</option>
                        @foreach($pigeons as $pigeon)
                            <option value="{{ $pigeon->id }}">
                                {{ $pigeon->ring_number }} {{ $pigeon->nickname ? '(' . $pigeon->nickname . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Event Type <span class="text-danger">*</span></label>
                        <select name="event_type" class="form-select" required>
                            <option value="training">Training</option>
                            <option value="race">Race</option>
                            <option value="endurance_test">Endurance Test</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Flight Date <span class="text-danger">*</span></label>
                        <input type="date" name="flight_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label">Distance (km)</label>
                        <input type="number" step="0.01" min="0" name="distance_km" id="distance_km" class="form-control" placeholder="e.g. 120.5">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Duration (min)</label>
                        <input type="number" min="1" name="duration_minutes" id="duration_minutes" class="form-control" placeholder="e.g. 95">
                    </div>
                    <div class="col-4">
                        <label class="form-label">Speed (MPM)</label>
                        <input type="number" step="0.01" min="0" name="flight_speed_mpm" id="flight_speed_mpm" class="form-control" placeholder="Auto">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label">Rank / Position</label>
                        <input type="number" min="1" name="rank_position" class="form-control" placeholder="e.g. 1">
                    </div>
                    <div class="col-8">
                        <label class="form-label">Weather Condition</label>
                        <input type="text" name="weather_condition" class="form-control" placeholder="e.g. Clear, Headwind 15 km/h">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Flight Record</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const distInput = document.getElementById('distance_km');
    const durInput = document.getElementById('duration_minutes');
    const speedInput = document.getElementById('flight_speed_mpm');

    function calcSpeed() {
        const d = parseFloat(distInput.value);
        const m = parseFloat(durInput.value);
        if (d > 0 && m > 0) {
            speedInput.value = ((d * 1000) / m).toFixed(2);
        }
    }

    distInput.addEventListener('input', calcSpeed);
    durInput.addEventListener('input', calcSpeed);
});
</script>
@endsection
