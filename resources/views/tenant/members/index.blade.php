@extends('layouts.app')

@section('title', 'Members')
@section('page-title', 'Members')

@php use Illuminate\Support\Str; @endphp

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Members</h5>
            @can('members.create')
                <button class="btn btn-primary" id="btn-new-member"><i class="icon-base ti tabler-plus me-1"></i> New Member</button>
            @endcan
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="members-table" style="width:100%">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="member-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="member-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="member-modal-title">New Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="member-id">
                    <div class="mb-3">
                        <label class="form-label" for="member-name">Name</label>
                        <input type="text" class="form-control" id="member-name" name="name" required>
                        <div class="invalid-feedback" data-field="name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="member-email">Email</label>
                        <input type="email" class="form-control" id="member-email" name="email" required>
                        <div class="invalid-feedback" data-field="email"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="member-role">Role</label>
                        <select class="form-select" id="member-role" name="role">
                            @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ Str::headline($role) }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="role"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="member-password">Password <small class="text-muted" id="pw-hint"></small></label>
                            <div class="input-group input-group-merge has-validation">
                                <input type="password" class="form-control" id="member-password" name="password">
                                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
                                <div class="invalid-feedback" data-field="password"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="member-password2">Confirm</label>
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="member-password2" name="password_confirmation">
                                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="member-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="member-active">Active</label>
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
@endpush

@push('scripts')
<script>
(function () {
    const canUpdate = @json(auth()->user()->can('members.update'));
    const canImpersonate = @json(auth()->user()->can('members.impersonate'));
    const urls = {
        listing: @json(route('tenant.members.listing')),
        save: @json(route('tenant.members.save')),
        base: @json(url('tenant/members')),
    };
    const modal = new bootstrap.Modal(document.getElementById('member-modal'));
    const form = document.getElementById('member-form');

    const table = $('#members-table').DataTable({
        processing: true, serverSide: true, ajax: { url: urls.listing },
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'role_label' },
            { data: 'is_active', render: v => v ? '<span class="badge bg-label-success">Active</span>' : '<span class="badge bg-label-secondary">Inactive</span>' },
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: (id, t, row) => {
                let html = '';
                if (canUpdate) html += `<button class="btn btn-sm btn-icon edit-member" data-id="${row.id}"><i class="icon-base ti tabler-edit"></i></button>`;
                if (canImpersonate && !row.is_self && row.role !== 'tenant_admin')
                    html += `<button class="btn btn-sm btn-icon impersonate-member" data-id="${row.id}" title="Impersonate"><i class="icon-base ti tabler-user-share"></i></button>`;
                return html || '<span class="text-muted">—</span>';
            }},
        ],
    });

    const clearErrors = () => form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    document.getElementById('btn-new-member')?.addEventListener('click', () => {
        clearErrors(); form.reset();
        document.getElementById('member-id').value = '';
        document.getElementById('member-active').checked = true;
        document.getElementById('pw-hint').textContent = '(required)';
        document.getElementById('member-modal-title').textContent = 'New Member';
        modal.show();
    });

    $('#members-table tbody').on('click', '.edit-member', function () {
        clearErrors();
        const row = table.row($(this).closest('tr')).data();
        document.getElementById('member-id').value = row.id;
        document.getElementById('member-name').value = row.name;
        document.getElementById('member-email').value = row.email;
        document.getElementById('member-role').value = row.role;
        document.getElementById('member-active').checked = !!row.is_active;
        document.getElementById('member-password').value = '';
        document.getElementById('pw-hint').textContent = '(leave blank to keep)';
        document.getElementById('member-modal-title').textContent = 'Edit Member';
        modal.show();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        axios.post(urls.save, Object.fromEntries(new FormData(form))).then(({ data }) => {
            modal.hide(); table.ajax.reload(null, false);
            notyf.success(data.message);
        }).catch(err => {
            if (err.response && err.response.status === 422) {
                const errors = err.response.data.errors || {};
                Object.keys(errors).forEach(f => {
                    const fb = form.querySelector(`[data-field="${f}"]`);
                    const input = form.querySelector(`[name="${f}"]`);
                    if (input) input.classList.add('is-invalid');
                    if (fb) fb.textContent = errors[f][0];
                });
            } else { notyf.failure('Something went wrong'); }
        });
    });

    $('#members-table tbody').on('click', '.impersonate-member', function () {
        const id = this.dataset.id;
        const f = document.createElement('form');
        f.method = 'POST'; f.action = `${urls.base}/${id}/impersonate`;
        f.innerHTML = `@csrf`;
        document.body.appendChild(f); f.submit();
    });
})();
</script>
@endpush
