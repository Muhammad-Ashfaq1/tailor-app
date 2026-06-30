@extends('layouts.app')

@section('title', 'Tasks')
@section('page-title', 'Tasks')

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/select2/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tasks</h5>
            @can('tasks.create')
                <button class="btn btn-primary" id="btn-new-task">
                    <i class="icon-base ti tabler-plus me-1"></i> New Task
                </button>
            @endcan
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="tasks-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Assignee</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="task-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="task-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="task-modal-title">New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="task-id">
                    <div class="mb-3">
                        <label class="form-label" for="task-title">Title</label>
                        <input type="text" class="form-control" id="task-title" name="title" required>
                        <div class="invalid-feedback" data-field="title"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="task-project">Project</label>
                        <select class="form-select" id="task-project" name="project_id" style="width:100%"></select>
                        <div class="invalid-feedback" data-field="project_id"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="task-assignee">Assignee</label>
                        <select class="form-select" id="task-assignee" name="assigned_to" style="width:100%"></select>
                        <div class="invalid-feedback" data-field="assigned_to"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label" for="task-status">Status</label>
                            <select class="form-select" id="task-status" name="status">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="task-due">Due date</label>
                            <input type="date" class="form-control" id="task-due" name="due_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('vendor-scripts')
    <script src="{{ asset('organization/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('organization/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endpush

@push('scripts')
<script>
(function () {
    const canUpdate = @json(auth()->user()->can('tasks.update'));
    const canDelete = @json(auth()->user()->can('tasks.delete'));
    const urls = {
        listing: @json(route('tenant.tasks.listing')),
        save: @json(route('tenant.tasks.save')),
        show: @json(url('tenant/tasks')),
        projects: @json(route('tenant.tasks.dropdowns.projects')),
        assignees: @json(route('tenant.tasks.dropdowns.assignees')),
    };

    const modal = new bootstrap.Modal(document.getElementById('task-modal'));
    const form = document.getElementById('task-form');

    // Hydrate Select2 dropdowns from the /dropdowns/* JSON endpoints.
    function fillSelect(el, options, placeholder) {
        $(el).empty().append(new Option(placeholder, '', false, false));
        options.forEach(o => $(el).append(new Option(o.label, o.value, false, false)));
    }
    Promise.all([axios.get(urls.projects), axios.get(urls.assignees)]).then(([p, a]) => {
        fillSelect('#task-project', p.data, 'Select project');
        fillSelect('#task-assignee', a.data, 'Unassigned');
        $('#task-project, #task-assignee').select2({ dropdownParent: $('#task-modal') });
    });

    const table = $('#tasks-table').DataTable({
        processing: true, serverSide: true,
        ajax: { url: urls.listing },
        columns: [
            { data: 'title' },
            { data: 'project' },
            { data: 'assignee', render: d => d || '<span class="text-muted">Unassigned</span>' },
            { data: 'status', render: (d, t, row) => `<span class="badge bg-label-${row.status_color}">${row.status_label}</span>` },
            { data: 'due_date', render: d => d || '—' },
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: (id, t, row) => {
                let html = '';
                if (canUpdate) html += `<button class="btn btn-sm btn-icon edit-task" data-id="${row.id}"><i class="icon-base ti tabler-edit"></i></button>`;
                if (canDelete) html += `<button class="btn btn-sm btn-icon text-danger delete-task" data-id="${row.id}" data-title="${row.title}"><i class="icon-base ti tabler-trash"></i></button>`;
                return html || '<span class="text-muted">—</span>';
            }},
        ],
        order: [[0, 'asc']],
    });

    const clearErrors = () => form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    document.getElementById('btn-new-task')?.addEventListener('click', () => {
        clearErrors(); form.reset();
        document.getElementById('task-id').value = '';
        $('#task-project, #task-assignee').val('').trigger('change');
        document.getElementById('task-modal-title').textContent = 'New Task';
        modal.show();
    });

    $('#tasks-table tbody').on('click', '.edit-task', function () {
        clearErrors();
        axios.get(`${urls.show}/${this.dataset.id}`).then(({ data }) => {
            document.getElementById('task-id').value = data.id;
            document.getElementById('task-title').value = data.title;
            document.getElementById('task-status').value = data.status;
            document.getElementById('task-due').value = data.due_date || '';
            $('#task-project').val(data.project_id).trigger('change');
            $('#task-assignee').val(data.assigned_to || '').trigger('change');
            document.getElementById('task-modal-title').textContent = 'Edit Task';
            modal.show();
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        axios.post(urls.save, Object.fromEntries(new FormData(form)))
            .then(({ data }) => {
                modal.hide(); table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: data.message, timer: 1400, showConfirmButton: false });
            })
            .catch(err => {
                if (err.response && err.response.status === 422) {
                    const errors = err.response.data.errors || {};
                    Object.keys(errors).forEach(field => {
                        const fb = form.querySelector(`[data-field="${field}"]`);
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) input.classList.add('is-invalid');
                        if (fb) fb.textContent = errors[field][0];
                    });
                } else { Swal.fire({ icon: 'error', title: 'Something went wrong' }); }
            });
    });

    $('#tasks-table tbody').on('click', '.delete-task', function () {
        const id = this.dataset.id, title = this.dataset.title;
        Swal.fire({ title: `Delete "${title}"?`, icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Delete', buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-label-secondary' } })
        .then(r => {
            if (!r.isConfirmed) return;
            axios.delete(`${urls.show}/${id}`).then(({ data }) => {
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: data.message, timer: 1400, showConfirmButton: false });
            });
        });
    });
})();
</script>
@endpush
