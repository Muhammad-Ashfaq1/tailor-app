@extends('layouts.member-portal')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-label-primary rounded p-2 me-2"><i class="icon-base ti tabler-checklist"></i></span>
                        <span class="text-muted">My tasks</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($totalTasks) }}</h3>
                </div>
            </div>
        </div>
        @foreach ($byStatus as $status)
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-label-{{ $status['color'] }} rounded p-2 me-2"><i class="icon-base ti tabler-point"></i></span>
                            <span class="text-muted">{{ $status['label'] }}</span>
                        </div>
                        <h3 class="mb-0">{{ number_format($status['count']) }}</h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            <a href="{{ route('member.tasks.index') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-arrow-right me-1"></i> Go to my tasks
            </a>
        </div>
    </div>
@endsection
