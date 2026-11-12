@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="mb-0"><i class="bi bi-heart-pulse text-danger me-2"></i>Loft Health & Medical Overview</h3>
        <small class="text-muted">
            {{ $isAdmin ? 'Viewing all loft medical records (Admin)' : 'Viewing medical records for your loft' }}
        </small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkVaccinationModal">
            <i class="bi bi-people-fill me-1"></i> Vaccinate All / Bulk
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVaccinationModal">
            <i class="bi bi-shield-plus me-1"></i> Log Single Vaccination
        </button>
        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addDiseaseModal">
            <i class="bi bi-bandaid me-1"></i> Log Illness / Quarantine
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-danger h-100">
            <div class="card-body">
                <div class="text-muted small">In Quarantine</div>
                <div class="fs-3 fw-bold text-danger">{{ $stats['quarantined_count'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-warning h-100">
            <div class="card-body">
                <div class="text-muted small">Under Treatment</div>
                <div class="fs-3 fw-bold text-warning">{{ $stats['under_treatment_count'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-dark h-100">
            <div class="card-body">
                <div class="text-muted small">Overdue Boosters</div>
                <div class="fs-3 fw-bold text-dark">{{ $stats['overdue_vaccines_count'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <div class="text-muted small">Boosters Due (14 Days)</div>
                <div class="fs-3 fw-bold text-info">{{ $stats['due_soon_vaccines_count'] }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Active Quarantine & Treatment Cases -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Active Quarantine & Treatment Cases</span>
        <span class="badge bg-secondary">{{ $activeDiseases->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ring Number</th>
                    @if($isAdmin)<th>Owner</th>@endif
                    <th>Condition / Disease</th>
                    <th>Diagnosed</th>
                    <th>Treatment</th>
                    <th>Quarantine</th>
                    <th>Status</th>
                    <th class="text-end">Quick Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeDiseases as $record)
                    <tr>
                        <td>
                            <a href="{{ url('/pigeons/' . $record->pigeon_id) }}" class="fw-semibold text-decoration-none">
                                {{ $record->pigeon->ring_number }}
                            </a>
                            @if($record->pigeon->nickname)
                                <div class="small text-muted">{{ $record->pigeon->nickname }}</div>
                            @endif
                        </td>
                        @if($isAdmin)
                            <td>{{ $record->pigeon->owner->name ?? 'N/A' }}</td>
                        @endif
                        <td class="fw-medium">{{ $record->disease_name }}</td>
                        <td>{{ $record->diagnosis_date->format('Y-m-d') }}</td>
                        <td>{{ $record->treatment_given ?: '—' }}</td>
                        <td>
                            @if($record->is_quarantined)
                                <span class="badge bg-danger">Quarantined</span>
                            @else
                                <span class="badge bg-light text-dark border">No</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">Under Treatment</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <form action="{{ route('health.diseases.update', $record) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="recovery_status" value="recovered">
                                    <button type="submit" class="btn btn-success btn-sm" title="Mark Recovered">
                                        <i class="bi bi-check-circle"></i> Recovered
                                    </button>
                                </form>
                                @if($record->is_quarantined)
                                    <form action="{{ route('health.diseases.update', $record) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="recovery_status" value="under_treatment">
                                        <input type="hidden" name="is_quarantined" value="0">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="End Quarantine">
                                            Release Quarantine
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 8 : 7 }}" class="text-center text-muted py-4">
                            No active quarantine or treatment cases.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Upcoming & Overdue Vaccinations -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-calendar-check text-primary me-2"></i>Vaccination Booster Schedule</span>
        <span class="badge bg-secondary">{{ $upcomingVaccinations->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ring Number</th>
                    @if($isAdmin)<th>Owner</th>@endif
                    <th>Vaccine</th>
                    <th>Administered</th>
                    <th>Next Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($upcomingVaccinations as $vac)
                    @php
                        $isOverdue = $vac->next_due_date->isPast() && !$vac->next_due_date->isToday();
                        $isDueSoon = $vac->next_due_date->isToday() || ($vac->next_due_date->isFuture() && $vac->next_due_date->diffInDays(now()) <= 14);
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ url('/pigeons/' . $vac->pigeon_id) }}" class="fw-semibold text-decoration-none">
                                {{ $vac->pigeon->ring_number }}
                            </a>
                            @if($vac->pigeon->nickname)
                                <div class="small text-muted">{{ $vac->pigeon->nickname }}</div>
                            @endif
                        </td>
                        @if($isAdmin)
                            <td>{{ $vac->pigeon->owner->name ?? 'N/A' }}</td>
                        @endif
                        <td>{{ $vac->vaccine_name }}</td>
                        <td>{{ $vac->administered_date->format('Y-m-d') }}</td>
                        <td>{{ $vac->next_due_date->format('Y-m-d') }}</td>
                        <td>
                            @if($isOverdue)
                                <span class="badge bg-danger">Overdue</span>
                            @elseif($isDueSoon)
                                <span class="badge bg-warning text-dark">Due Soon</span>
                            @else
                                <span class="badge bg-success">Scheduled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 6 : 5 }}" class="text-center text-muted py-4">
                            No upcoming vaccination boosters scheduled.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Vaccinations Log (All Logged Vaccines) -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-shield-check text-success me-2"></i>Recent Vaccinations Log (Last 50)</span>
        <span class="badge bg-secondary">{{ $recentVaccinations->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ring Number</th>
                    @if($isAdmin)<th>Owner</th>@endif
                    <th>Vaccine</th>
                    <th>Administered Date</th>
                    <th>Next Due Date</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentVaccinations as $vac)
                    <tr>
                        <td>
                            <a href="{{ url('/pigeons/' . $vac->pigeon_id) }}" class="fw-semibold text-decoration-none">
                                {{ $vac->pigeon->ring_number }}
                            </a>
                            @if($vac->pigeon->nickname)
                                <div class="small text-muted">{{ $vac->pigeon->nickname }}</div>
                            @endif
                        </td>
                        @if($isAdmin)
                            <td>{{ $vac->pigeon->owner->name ?? 'N/A' }}</td>
                        @endif
                        <td>{{ $vac->vaccine_name }}</td>
                        <td>{{ $vac->administered_date->format('Y-m-d') }}</td>
                        <td>{{ $vac->next_due_date ? $vac->next_due_date->format('Y-m-d') : '—' }}</td>
                        <td>{{ $vac->notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 6 : 5 }}" class="text-center text-muted py-3">
                            No vaccinations logged yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Disease & Recovery Log -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <span class="fw-semibold"><i class="bi bi-journal-medical text-secondary me-2"></i>Recent Illness & Recovery Log (Last 25)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ring Number</th>
                    @if($isAdmin)<th>Owner</th>@endif
                    <th>Disease</th>
                    <th>Diagnosed</th>
                    <th>Recovered Date</th>
                    <th>Outcome</th>
                </tr>
            </thead>
            <tbody>
                @forelse($diseaseHistory as $item)
                    <tr>
                        <td>
                            <a href="{{ url('/pigeons/' . $item->pigeon_id) }}" class="text-decoration-none">
                                {{ $item->pigeon->ring_number }}
                            </a>
                        </td>
                        @if($isAdmin)
                            <td>{{ $item->pigeon->owner->name ?? 'N/A' }}</td>
                        @endif
                        <td>{{ $item->disease_name }}</td>
                        <td>{{ $item->diagnosis_date->format('Y-m-d') }}</td>
                        <td>{{ $item->recovery_date ? $item->recovery_date->format('Y-m-d') : '—' }}</td>
                        <td>
                            @if($item->recovery_status === 'recovered')
                                <span class="badge bg-success">Recovered</span>
                            @elseif($item->recovery_status === 'deceased')
                                <span class="badge bg-dark">Deceased</span>
                            @else
                                <span class="badge bg-warning text-dark">Under Treatment</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 6 : 5 }}" class="text-center text-muted py-3">
                            No medical history recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Bulk / Vaccinate All Modal (Scalable for 2,000+ birds) -->
<div class="modal fade" id="bulkVaccinationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('health.vaccinations.bulk') }}" method="POST" class="modal-content" id="bulkVaccinationForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-people-fill text-success me-2"></i>Vaccinate All / Multiple Pigeons</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php $activeCount = $pigeons->where('status', '!=', 'deceased')->count(); @endphp

                <!-- Target Mode Toggle -->
                <div class="mb-3 p-3 bg-light border rounded">
                    <label class="form-label fw-semibold d-block mb-2">Vaccination Target</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="target_scope" id="scopeAllActive" value="all_active" checked>
                        <label class="form-check-label" for="scopeAllActive">
                            <strong>All Active Pigeons ({{ $activeCount }} birds)</strong> — Fast server-side batch apply (recommended for large lofts)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_scope" id="scopeSelected" value="selected">
                        <label class="form-check-label" for="scopeSelected">
                            Select Specific Pigeons Manually
                        </label>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Vaccine Name <span class="text-danger">*</span></label>
                        <input type="text" name="vaccine_name" class="form-control" placeholder="e.g. Paramyxovirus (PMV-1)" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Administered Date <span class="text-danger">*</span></label>
                        <input type="date" name="administered_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Next Due Date</label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                </div>

                <!-- Manual Selection Box (Hidden when "All Active" is chosen so 2,000+ inputs are never submitted) -->
                <div class="mb-3 d-none" id="manualPigeonSelector">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                        <input type="text" id="bulkSearchInput" class="form-control form-control-sm w-auto" placeholder="Filter ring number...">
                        <span class="small text-muted"><span id="selectedPigeonCount">0</span> selected</span>
                    </div>
                    <div class="border rounded p-2 bg-light" style="max-height: 240px; overflow-y: auto;">
                        <div class="row g-2" id="bulkPigeonList">
                            @forelse($pigeons as $pigeon)
                                <div class="col-md-6 bulk-pigeon-item" data-search="{{ strtolower($pigeon->ring_number . ' ' . ($pigeon->nickname ?? '')) }}">
                                    <div class="form-check">
                                        <input class="form-check-input bulk-pigeon-checkbox" type="checkbox"
                                               name="pigeon_ids[]" value="{{ $pigeon->id }}"
                                               id="bulk_pigeon_{{ $pigeon->id }}" disabled>
                                        <label class="form-check-label small" for="bulk_pigeon_{{ $pigeon->id }}">
                                            <strong>{{ $pigeon->ring_number }}</strong>
                                            {{ $pigeon->nickname ? '(' . $pigeon->nickname . ')' : '' }}
                                            @if($isAdmin && $pigeon->owner)
                                                <span class="text-muted">— {{ $pigeon->owner->name }}</span>
                                            @endif
                                            @if($pigeon->status === 'deceased')
                                                <span class="badge bg-dark">Deceased</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-muted small">No pigeons available.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label">Notes (Applied to all)</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Apply Vaccination</button>
            </div>
        </form>
    </div>
</div>

<!-- Log Single Vaccination Modal -->
<div class="modal fade" id="addVaccinationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('health.vaccinations.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Log Single Vaccination Record</h5>
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
                <div class="mb-3">
                    <label class="form-label">Vaccine Name <span class="text-danger">*</span></label>
                    <input type="text" name="vaccine_name" class="form-control" placeholder="e.g. Paramyxovirus (PMV-1)" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Administered Date <span class="text-danger">*</span></label>
                        <input type="date" name="administered_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Next Due Date</label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Vaccination</button>
            </div>
        </form>
    </div>
</div>

<!-- Log Disease / Quarantine Modal -->
<div class="modal fade" id="addDiseaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('health.diseases.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Log Illness / Quarantine</h5>
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
                <div class="mb-3">
                    <label class="form-label">Condition / Disease Name <span class="text-danger">*</span></label>
                    <input type="text" name="disease_name" class="form-control" placeholder="e.g. Canker, Coccidiosis" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Diagnosis Date <span class="text-danger">*</span></label>
                        <input type="date" name="diagnosis_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="recovery_status" class="form-select" required>
                            <option value="under_treatment" selected>Under Treatment</option>
                            <option value="recovered">Recovered</option>
                            <option value="deceased">Deceased</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Treatment Given</label>
                    <input type="text" name="treatment_given" class="form-control" placeholder="Medication or protocol">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_quarantined" value="1" id="quarantineCheck" checked>
                    <label class="form-check-label" for="quarantineCheck">
                        Place in Quarantine
                    </label>
                </div>
                <div class="mb-0">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Save Record</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const scopeAll = document.getElementById('scopeAllActive');
    const scopeSelected = document.getElementById('scopeSelected');
    const selectorBox = document.getElementById('manualPigeonSelector');
    const checkboxes = document.querySelectorAll('.bulk-pigeon-checkbox');
    const countSpan = document.getElementById('selectedPigeonCount');
    const searchInput = document.getElementById('bulkSearchInput');
    const items = document.querySelectorAll('.bulk-pigeon-item');

    function toggleScope() {
        const isManual = scopeSelected.checked;
        selectorBox.classList.toggle('d-none', !isManual);
        checkboxes.forEach(cb => cb.disabled = !isManual);
    }

    function updateCount() {
        const checked = document.querySelectorAll('.bulk-pigeon-checkbox:checked').length;
        if (countSpan) countSpan.textContent = checked;
    }

    scopeAll.addEventListener('change', toggleScope);
    scopeSelected.addEventListener('change', toggleScope);
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            items.forEach(item => {
                item.classList.toggle('d-none', !item.dataset.search.includes(term));
            });
        });
    }
});
</script>
@endsection
