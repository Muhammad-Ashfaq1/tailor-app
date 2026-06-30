@extends($layout)

@section('title', $definition->label())
@section('page-title', $definition->label())

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
@endpush

@php
    $columns = $definition->columnMap();
    $sortable = $definition->sortableColumns();
    $statusFilter = collect($definition->filters())->firstWhere('key', 'status');
@endphp

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <form id="report-filters" class="row g-3 align-items-end">
                @if ($definition->dateColumn() !== null)
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label" for="date_from">From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label" for="date_to">To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
                    </div>
                @endif
                @if ($statusFilter)
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label" for="status">{{ $statusFilter['label'] }}</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All</option>
                            @foreach ($statusFilter['options'] as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-filter me-1"></i> Apply</button>
                    <button type="button" class="btn btn-label-secondary" id="report-export"><i class="icon-base ti tabler-download me-1"></i> Export</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ $definition->label() }}</h5></div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="report-table" style="width:100%">
                <thead>
                    <tr>
                        @foreach ($columns as $key => $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('vendor-scripts')
    <script src="{{ asset('organization/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endpush

@push('scripts')
<script>
(function () {
    const listingUrl = @json(route("{$prefix}.reports.listing", $definition->key()));
    const exportUrl = @json(route("{$prefix}.reports.export", $definition->key()));
    const columns = @json(array_keys($columns));
    const sortable = @json(array_values($sortable));
    const form = document.getElementById('report-filters');

    function filters() {
        return Object.fromEntries(new FormData(form));
    }

    const table = $('#report-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: listingUrl,
            data: d => Object.assign(d, filters()),
        },
        columns: columns.map(key => ({
            data: key,
            orderable: sortable.includes(key),
            defaultContent: '',
        })),
        order: [],
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    });

    document.getElementById('report-export').addEventListener('click', function () {
        const params = new URLSearchParams(filters());
        window.location = `${exportUrl}?${params.toString()}`;
    });
})();
</script>
@endpush
