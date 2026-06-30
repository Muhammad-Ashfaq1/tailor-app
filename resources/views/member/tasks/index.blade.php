@extends('layouts.member-portal')

@section('title', 'My Tasks')
@section('page-title', 'My Tasks')

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Tasks assigned to me</h5></div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="my-tasks-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Due</th>
                        <th>Status</th>
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
    const listingUrl = @json(route('member.tasks.listing'));
    const statusBase = @json(url('member/tasks'));
    const statuses = @json($statuses);

    const table = $('#my-tasks-table').DataTable({
        processing: true, serverSide: true,
        ajax: { url: listingUrl },
        columns: [
            { data: 'title' },
            { data: 'project' },
            { data: 'due_date', render: d => d || '—' },
            { data: 'status', orderable: false, render: (d, t, row) => {
                const opts = statuses.map(s => `<option value="${s.value}" ${s.value === row.status ? 'selected' : ''}>${s.label}</option>`).join('');
                return `<select class="form-select form-select-sm task-status" data-id="${row.id}">${opts}</select>`;
            }},
        ],
    });

    $('#my-tasks-table tbody').on('change', '.task-status', function () {
        const id = this.dataset.id;
        axios.post(`${statusBase}/${id}/status`, { status: this.value })
            .then(({ data }) => Swal.fire({ icon: 'success', title: data.message, timer: 1100, showConfirmButton: false }))
            .catch(() => { Swal.fire({ icon: 'error', title: 'Could not update' }); table.ajax.reload(null, false); });
    });
})();
</script>
@endpush
