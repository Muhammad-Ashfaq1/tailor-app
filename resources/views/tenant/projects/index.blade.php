@extends('layouts.app')

@section('title', 'Projects')
@section('page-title', 'Projects')

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/select2/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Projects</h5>
            @can('projects.create')
                <button class="btn btn-primary" id="btn-new-project">
                    <i class="icon-base ti tabler-plus me-1"></i> New Project
                </button>
            @endcan
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="projects-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Tasks</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Single save modal: create (no id) + update (with id) --}}
    <div class="modal fade" id="project-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="project-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="project-modal-title">New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="project-id">
                    <div class="mb-3">
                        <label class="form-label" for="project-name">Name</label>
                        <input type="text" class="form-control" id="project-name" name="name" required>
                        <div class="invalid-feedback" data-field="name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="project-status">Status</label>
                        <select class="form-select" id="project-status" name="status">
                            @foreach ($statuses as $status)
                                <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="status"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="project-description">Description</label>
                        <textarea class="form-control" id="project-description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" data-field="description"></div>
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
    const canUpdate = @json(auth()->user()->can('projects.update'));
    const canDelete = @json(auth()->user()->can('projects.delete'));
    const listingUrl = @json(route('tenant.projects.listing'));
    const saveUrl = @json(route('tenant.projects.save'));
    const showUrlBase = @json(url('tenant/projects'));

    const modalEl = document.getElementById('project-modal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('project-form');

    const table = $('#projects-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: listingUrl },
        columns: [
            { data: 'name' },
            { data: 'status', render: (d, t, row) =>
                `<span class="badge bg-label-${row.status_color}">${row.status_label}</span>` },
            { data: 'tasks_count' },
            { data: 'created_at' },
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: (id, t, row) => {
                let html = '';
                if (canUpdate) html += `<button class="btn btn-sm btn-icon edit-project" data-slug="${row.slug}"><i class="icon-base ti tabler-edit"></i></button>`;
                if (canDelete) html += `<button class="btn btn-sm btn-icon text-danger delete-project" data-slug="${row.slug}" data-name="${row.name}"><i class="icon-base ti tabler-trash"></i></button>`;
                return html || '<span class="text-muted">—</span>';
            }},
        ],
        order: [[3, 'desc']],
    });

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function openCreate() {
        clearErrors();
        form.reset();
        document.getElementById('project-id').value = '';
        document.getElementById('project-modal-title').textContent = 'New Project';
        modal.show();
    }

    document.getElementById('btn-new-project')?.addEventListener('click', openCreate);

    $('#projects-table tbody').on('click', '.edit-project', function () {
        clearErrors();
        axios.get(`${showUrlBase}/${this.dataset.slug}`).then(({ data }) => {
            document.getElementById('project-id').value = data.id;
            document.getElementById('project-name').value = data.name;
            document.getElementById('project-status').value = data.status;
            document.getElementById('project-description').value = data.description || '';
            document.getElementById('project-modal-title').textContent = 'Edit Project';
            modal.show();
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        axios.post(saveUrl, Object.fromEntries(new FormData(form)))
            .then(({ data }) => {
                modal.hide();
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: data.message, timer: 1400, showConfirmButton: false });
            })
            .catch(err => {
                if (err.response && err.response.status === 422) {
                    const errors = err.response.data.errors || {};
                    Object.keys(errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        const fb = form.querySelector(`[data-field="${field}"]`);
                        if (input) input.classList.add('is-invalid');
                        if (fb) fb.textContent = errors[field][0];
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Something went wrong' });
                }
            });
    });

    $('#projects-table tbody').on('click', '.delete-project', function () {
        const slug = this.dataset.slug, name = this.dataset.name;
        Swal.fire({
            title: `Delete "${name}"?`, icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Delete', customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false,
        }).then(result => {
            if (!result.isConfirmed) return;
            axios.delete(`${showUrlBase}/${slug}`).then(({ data }) => {
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: data.message, timer: 1400, showConfirmButton: false });
            });
        });
    });
})();
</script>
@endpush
