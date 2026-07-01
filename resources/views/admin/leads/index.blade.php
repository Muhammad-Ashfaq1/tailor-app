@extends('layouts.app')

@section('title', __('leads.title'))
@section('page-title', __('leads.title'))

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ __('leads.triage') }}</h5></div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="leads-table" style="width:100%">
                <thead>
                    <tr>
                        <th>{{ __('leads.name') }}</th>
                        <th>{{ __('leads.email') }}</th>
                        <th>{{ __('leads.company') }}</th>
                        <th>{{ __('leads.created') }}</th>
                        <th>{{ __('leads.status_label') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('vendor-scripts')
    <script src="{{ asset('organization/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endpush

@push('scripts')
<script>
(function () {
    const T = @json(__('leads'));
    const listingUrl = @json(route('admin.leads.listing'));
    const statusBase = @json(url('admin/leads'));
    const statuses = @json($statuses);

    const table = $('#leads-table').DataTable({
        processing: true, serverSide: true,
        ajax: { url: listingUrl },
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'company', render: d => d || '—' },
            { data: 'created_at' },
            { data: 'status', orderable: false, render: (d, t, row) => {
                const opts = statuses.map(s => `<option value="${s.value}" ${s.value === row.status ? 'selected' : ''}>${s.label}</option>`).join('');
                return `<select class="form-select form-select-sm lead-status" data-id="${row.id}">${opts}</select>`;
            }},
        ],
        order: [[3, 'desc']],
    });

    $('#leads-table tbody').on('change', '.lead-status', function () {
        const id = this.dataset.id;
        axios.post(`${statusBase}/${id}/status`, { status: this.value })
            .then(({ data }) => Swal.fire({ icon: 'success', title: data.message, timer: 1100, showConfirmButton: false }))
            .catch(() => { Swal.fire({ icon: 'error', title: T.update_failed }); table.ajax.reload(null, false); });
    });
})();
</script>
@endpush
