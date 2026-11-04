@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h4>Welcome, {{ Auth::user()->name }}!</h4>
                <p class="text-muted mb-0">Loft: {{ Auth::user()->loft_name ?? 'Default Loft' }} | Role: <span class="badge bg-secondary">{{ strtoupper(Auth::user()->role) }}</span></p>
            </div>
        </div>
    </div>
</div>
@endsection
