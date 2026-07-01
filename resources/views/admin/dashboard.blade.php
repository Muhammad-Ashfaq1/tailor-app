@extends('layouts.app')

@section('title', __('dashboard.platform_dashboard'))
@section('page-title', __('dashboard.platform_dashboard'))

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-label-primary rounded p-2 me-2"><i class="icon-base ti tabler-building"></i></span>
                        <span class="text-muted">{{ __('dashboard.organizations') }}</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($totalOrganizations) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-label-info rounded p-2 me-2"><i class="icon-base ti tabler-users"></i></span>
                        <span class="text-muted">{{ __('dashboard.users') }}</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($totalUsers) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ __('dashboard.organizations_by_status') }}</h5></div>
        <div class="card-body">
            <div class="row g-4">
                @foreach ($orgsByStatus as $status)
                    <div class="col-sm-6 col-xl-3">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-label-{{ $status['color'] }} rounded p-2 me-2"><i class="icon-base ti tabler-circle"></i></span>
                            <div>
                                <h4 class="mb-0">{{ number_format($status['count']) }}</h4>
                                <small class="text-muted">{{ $status['label'] }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
