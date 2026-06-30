@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/apex-charts/apex-charts.css') }}">
@endpush

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-label-primary rounded p-2 me-2"><i class="icon-base ti tabler-folder"></i></span>
                        <span class="text-muted">Projects</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($payload['stats']['total_projects']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-label-info rounded p-2 me-2"><i class="icon-base ti tabler-checklist"></i></span>
                        <span class="text-muted">Tasks</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($payload['stats']['total_tasks']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-label-warning rounded p-2 me-2"><i class="icon-base ti tabler-clock"></i></span>
                        <span class="text-muted">Open tasks</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($payload['stats']['open_tasks']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-label-success rounded p-2 me-2"><i class="icon-base ti tabler-users"></i></span>
                        <span class="text-muted">Members</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($payload['stats']['active_members']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Tasks created (last 14 days)</h5></div>
                <div class="card-body">
                    <div id="tasks-trend-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Projects by status</h5></div>
                <div class="card-body">
                    <div id="projects-status-chart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('vendor-scripts')
    <script src="{{ asset('organization/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endpush

@push('scripts')
<script>
(function () {
    const payload = @json($payload);

    new ApexCharts(document.querySelector('#tasks-trend-chart'), {
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        series: [{ name: 'Tasks', data: payload.trend.data }],
        xaxis: { categories: payload.trend.labels },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        colors: ['#696cff'],
    }).render();

    const projects = payload.projects_by_status;
    new ApexCharts(document.querySelector('#projects-status-chart'), {
        chart: { type: 'donut', height: 320 },
        series: projects.map(s => s.count),
        labels: projects.map(s => s.label),
        legend: { position: 'bottom' },
    }).render();
})();
</script>
@endpush
