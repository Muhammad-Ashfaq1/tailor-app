@extends('layouts.app')

@section('title', __('members.title'))
@section('page-title', __('members.title'))

@php use Illuminate\Support\Str; @endphp

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('members.title') }}</h5>
            @can('members.create')
                <button class="btn btn-primary" id="btn-new-member"><i class="icon-base ti tabler-plus me-1"></i> {{ __('members.new') }}</button>
            @endcan
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="members-table" style="width:100%">
                <thead><tr><th>{{ __('members.name') }}</th><th>{{ __('members.email') }}</th><th>{{ __('members.role') }}</th><th>{{ __('members.status') }}</th><th class="text-end">{{ __('app.actions') }}</th></tr></thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="member-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="member-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="member-modal-title">{{ __('members.new') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="member-id">
                    <x-form.input name="name" id="member-name" :label="__('members.name')" :placeholder="__('members.ph_name')" required />
                    <x-form.input name="email" id="member-email" type="email" :label="__('members.email')" :placeholder="__('members.ph_email')" required />
                    <x-form.select name="role" id="member-role" :label="__('members.role')">
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ Str::headline($role) }}</option>
                        @endforeach
                    </x-form.select>
                    <div class="row">
                        <x-form.password name="password" id="member-password" :label="__('members.password')" wrapper="col-md-6 mb-3">
                            <x-slot:hint><small class="text-muted" id="pw-hint"></small></x-slot:hint>
                        </x-form.password>
                        <x-form.password name="password_confirmation" id="member-password2" :label="__('members.confirm_password')" :feedback="false" wrapper="col-md-6 mb-3" />
                    </div>
                    <x-form.switch id="member-active" :label="__('members.active')" />
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
@endpush

@push('scripts')
<script>
(function () {
    const T = @json(__('members'));
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
            { data: 'is_active', render: v => v ? `<span class="badge bg-label-success">${window.AppTranslations.active}</span>` : `<span class="badge bg-label-secondary">${window.AppTranslations.inactive}</span>` },
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: (id, t, row) => {
                let html = '';
                if (canUpdate) html += `<button class="btn btn-sm btn-icon edit-member" data-id="${row.id}"><i class="icon-base ti tabler-edit"></i></button>`;
                if (canImpersonate && !row.is_self && row.role !== 'tenant_admin')
                    html += `<button class="btn btn-sm btn-icon impersonate-member" data-id="${row.id}" title="${T.impersonate}"><i class="icon-base ti tabler-user-share"></i></button>`;
                return html || '<span class="text-muted">—</span>';
            }},
        ],
    });

    const clearErrors = () => form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    document.getElementById('btn-new-member')?.addEventListener('click', () => {
        clearErrors(); form.reset();
        document.getElementById('member-id').value = '';
        document.getElementById('member-active').checked = true;
        document.getElementById('pw-hint').textContent = T.password_required_hint;
        document.getElementById('member-modal-title').textContent = T.new;
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
        document.getElementById('pw-hint').textContent = T.password_keep_hint;
        document.getElementById('member-modal-title').textContent = T.edit;
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
            } else { notyf.failure(window.AppTranslations.operationFailed); }
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
