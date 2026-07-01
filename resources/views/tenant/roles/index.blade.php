@extends('layouts.app')

@section('title', __('roles.roles_permissions'))
@section('page-title', __('roles.roles_permissions'))

@php use Illuminate\Support\Str; @endphp

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('roles.title') }}</h5>
            <button class="btn btn-primary" id="btn-new-role"><i class="icon-base ti tabler-plus me-1"></i> {{ __('roles.new') }}</button>
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="roles-table" style="width:100%">
                <thead><tr><th>{{ __('roles.role') }}</th><th>{{ __('roles.permissions') }}</th><th class="text-end">{{ __('app.actions') }}</th></tr></thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="role-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" id="role-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="role-modal-title">{{ __('roles.new') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="role-id">
                    <x-form.input name="name" id="role-name" :label="__('roles.role_name')" required>
                        <div class="form-text" id="role-protected-note" style="display:none">{{ __('roles.protected_note') }}</div>
                    </x-form.input>
                    <label class="form-label">{{ __('roles.permissions') }}</label>
                    <div class="row">
                        @foreach ($permissionGroups as $resource => $permissions)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-medium mb-2 text-capitalize">{{ Str::headline($resource) }}</div>
                                    @foreach ($permissions as $permission)
                                        <div class="form-check">
                                            <input class="form-check-input perm-check" type="checkbox"
                                                   name="permissions[]" value="{{ $permission }}" id="perm-{{ $permission }}">
                                            <label class="form-check-label" for="perm-{{ $permission }}">{{ Str::afterLast($permission, '.') }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                </div>
            </form>
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
    const T = @json(__('roles'));
    const urls = {
        listing: @json(route('tenant.roles.listing')),
        save: @json(route('tenant.roles.save')),
        base: @json(url('tenant/roles')),
    };
    const modal = new bootstrap.Modal(document.getElementById('role-modal'));
    const form = document.getElementById('role-form');
    const nameInput = document.getElementById('role-name');
    const protectedNote = document.getElementById('role-protected-note');

    const table = $('#roles-table').DataTable({
        processing: true, serverSide: true, ajax: { url: urls.listing },
        columns: [
            { data: 'name', render: (d, t, row) => row.protected ? `${d} <span class="badge bg-label-secondary">${T.protected}</span>` : d },
            { data: 'permissions_count' },
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: (id, t, row) => {
                let html = `<button class="btn btn-sm btn-icon edit-role" data-id="${row.id}">`+
                    `<i class="icon-base ti tabler-edit"></i></button>`;
                if (!row.protected) html += `<button class="btn btn-sm btn-icon text-danger delete-role" data-id="${row.id}" data-name="${row.name}"><i class="icon-base ti tabler-trash"></i></button>`;
                return html;
            }},
        ],
    });

    const clearErrors = () => form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    const setChecks = (perms) => {
        document.querySelectorAll('.perm-check').forEach(c => { c.checked = perms.includes(c.value); });
    };

    document.getElementById('btn-new-role').addEventListener('click', () => {
        clearErrors(); form.reset();
        document.getElementById('role-id').value = '';
        nameInput.disabled = false; protectedNote.style.display = 'none';
        setChecks([]);
        document.getElementById('role-modal-title').textContent = T.new;
        modal.show();
    });

    // Edit prefilled from the listing row data (includes permissions[]).
    $('#roles-table tbody').on('click', '.edit-role', function () {
        clearErrors();
        const row = table.row($(this).closest('tr')).data();
        document.getElementById('role-id').value = row.id;
        nameInput.value = row.name;
        nameInput.disabled = row.protected;
        protectedNote.style.display = row.protected ? 'block' : 'none';
        setChecks(row.permissions || []);
        document.getElementById('role-modal-title').textContent = T.edit;
        modal.show();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        const fd = new FormData(form);
        // Ensure disabled (protected) name still submits.
        if (nameInput.disabled) fd.set('name', nameInput.value);
        const payload = { id: fd.get('id'), name: fd.get('name'), permissions: fd.getAll('permissions[]') };
        axios.post(urls.save, payload).then(({ data }) => {
            modal.hide(); table.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: data.message, timer: 1400, showConfirmButton: false });
        }).catch(err => {
            if (err.response && err.response.status === 422) {
                const errors = err.response.data.errors || {};
                Object.keys(errors).forEach(f => {
                    const fb = form.querySelector(`[data-field="${f}"]`);
                    const input = form.querySelector(`[name="${f}"]`);
                    if (input) input.classList.add('is-invalid');
                    if (fb) fb.textContent = errors[f][0];
                });
            } else { Swal.fire({ icon: 'error', title: window.AppTranslations.operationFailed }); }
        });
    });

    $('#roles-table tbody').on('click', '.delete-role', function () {
        const id = this.dataset.id, name = this.dataset.name;
        Swal.fire({ title: T.delete_confirm.replace(':name', name), icon: 'warning', showCancelButton: true, buttonsStyling: false,
            confirmButtonText: window.AppTranslations.delete, customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-label-secondary' } })
        .then(r => { if (!r.isConfirmed) return;
            axios.delete(`${urls.base}/${id}`).then(({ data }) => {
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: data.message, timer: 1400, showConfirmButton: false });
            }).catch(err => Swal.fire({ icon: 'error', title: err.response?.data?.message || T.cannot_delete }));
        });
    });
})();
</script>
@endpush
