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
                        <span class="badge bg-label-primary rounded p-2 me-2"><i class="icon-base ti tabler-users"></i></span>
                        <span class="text-muted">Members</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($payload['stats']['total_members']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-label-success rounded p-2 me-2"><i class="icon-base ti tabler-user-check"></i></span>
                        <span class="text-muted">Active members</span>
                    </div>
                    <h3 class="mb-0">{{ number_format($payload['stats']['active_members']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Members joined (last 14 days)</h5></div>
                <div class="card-body">
                    <div id="members-trend-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Members by role</h5></div>
                <div class="card-body">
                    <div id="members-role-chart"></div>
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

    new ApexCharts(document.querySelector('#members-trend-chart'), {
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        series: [{ name: 'Members', data: payload.trend.data }],
        xaxis: { categories: payload.trend.labels },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        colors: ['#696cff'],
    }).render();

    const roles = payload.members_by_role;
    new ApexCharts(document.querySelector('#members-role-chart'), {
        chart: { type: 'donut', height: 320 },
        series: roles.map(r => r.count),
        labels: roles.map(r => r.role),
        legend: { position: 'bottom' },
    }).render();
})();
</script>
@endpush
