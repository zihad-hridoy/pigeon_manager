@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 mb-0">Health & Medical History</h1>
            <small class="text-muted">Pigeon: <strong>{{ $pigeon->ring_number }}</strong> {{ $pigeon->nickname ? "({$pigeon->nickname})" : '' }}</small>
        </div>
        <a href="/pigeons/{{ $pigeon->id }}" class="btn btn-outline-secondary btn-sm">Back to Bird Profile</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Vaccination Records</span>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#vaccineForm">+ Add Vaccine</button>
                </div>

                <div class="collapse p-3 bg-light border-bottom" id="vaccineForm">
                    <form action="{{ route('medical.vaccinations.store', $pigeon->id) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Vaccine Name *</label>
                            <input type="text" name="vaccine_name" class="form-control form-control-sm" placeholder="e.g. PMV-1, Pox" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Administered Date *</label>
                                <input type="date" name="administered_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Booster Due Date</label>
                                <input type="date" name="next_due_date" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Batch number, dosage..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Vaccine</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Vaccine</th>
                                <th>Administered</th>
                                <th>Booster Due</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vaccinations as $vax)
                                <tr>
                                    <td>
                                        <strong>{{ $vax->vaccine_name }}</strong>
                                        @if($vax->notes)<small class="d-block text-muted">{{ $vax->notes }}</small>@endif
                                    </td>
                                    <td>{{ $vax->administered_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($vax->next_due_date)
                                            <span class="badge {{ $vax->next_due_date->isPast() ? 'bg-danger' : 'bg-success' }}">
                                                {{ $vax->next_due_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('medical.vaccinations.destroy', $vax->id) }}" method="POST" onsubmit="return confirm('Delete record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">×</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted small">No vaccination records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Disease & Recovery Records</span>
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#diseaseForm">+ Log Condition</button>
                </div>

                <div class="collapse p-3 bg-light border-bottom" id="diseaseForm">
                    <form action="{{ route('medical.diseases.store', $pigeon->id) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Illness / Disease *</label>
                            <input type="text" name="disease_name" class="form-control form-control-sm" placeholder="e.g. Canker, Coccidiosis" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Diagnosis Date *</label>
                                <input type="date" name="diagnosis_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Recovery Status *</label>
                                <select name="recovery_status" class="form-select form-select-sm" required>
                                    <option value="under_treatment">Under Treatment</option>
                                    <option value="recovered">Recovered</option>
                                    <option value="deceased">Deceased</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Treatment Administered</label>
                            <input type="text" name="treatment_given" class="form-control form-control-sm" placeholder="Medication, isolation notes">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Recovery Date</label>
                                <input type="date" name="recovery_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 d-flex align-items-center mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_quarantined" value="1" id="quarantineCheck">
                                    <label class="form-check-label small fw-bold text-danger" for="quarantineCheck">
                                        Quarantine Isolated
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm">Save Medical Log</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Disease / Condition</th>
                                <th>Status</th>
                                <th>Quarantine</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($diseases as $dis)
                                <tr>
                                    <td>
                                        <strong>{{ $dis->disease_name }}</strong>
                                        <small class="d-block text-muted">Diagnosed: {{ $dis->diagnosis_date->format('M d, Y') }}</small>
                                        @if($dis->treatment_given)<small class="d-block text-secondary">Tx: {{ $dis->treatment_given }}</small>@endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($dis->recovery_status) {
                                                'recovered' => 'bg-success',
                                                'deceased' => 'bg-dark',
                                                default => 'bg-warning text-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} text-capitalize">
                                            {{ str_replace('_', ' ', $dis->recovery_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($dis->is_quarantined)
                                            <span class="badge bg-danger">Isolated</span>
                                        @else
                                            <span class="text-muted small">No</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('medical.diseases.destroy', $dis->id) }}" method="POST" onsubmit="return confirm('Delete record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">×</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted small">No disease records logged.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
