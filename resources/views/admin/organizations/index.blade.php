@extends('layouts.app')

@section('title', __('organizations.title'))
@section('page-title', __('organizations.title'))

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('organizations.title') }}</h5>
            <button class="btn btn-primary" id="btn-new-org"><i class="icon-base ti tabler-plus me-1"></i> {{ __('organizations.new') }}</button>
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="orgs-table" style="width:100%">
                <thead><tr><th>{{ __('organizations.name') }}</th><th>{{ __('organizations.slug') }}</th><th>{{ __('organizations.users') }}</th><th>{{ __('organizations.status_label') }}</th><th>{{ __('organizations.created') }}</th><th class="text-end">{{ __('app.actions') }}</th></tr></thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="org-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="org-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="org-modal-title">{{ __('organizations.new') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="org-id">
                    <x-form.input name="name" id="org-name" :label="__('organizations.name')" :placeholder="__('organizations.ph_name')" required />
                    <x-form.select name="status" id="org-status" :label="__('organizations.status_label')">
                        @foreach ($statuses as $status)
                            <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                        @endforeach
                    </x-form.select>
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
    const T = @json(__('organizations'));
    const statuses = @json($statuses);
    const urls = {
        listing: @json(route('admin.organizations.listing')),
        save: @json(route('admin.organizations.save')),
        base: @json(url('admin/organizations')),
    };
    const modal = new bootstrap.Modal(document.getElementById('org-modal'));
    const form = document.getElementById('org-form');

    const table = $('#orgs-table').DataTable({
        processing: true, serverSide: true, ajax: { url: urls.listing },
        columns: [
            { data: 'name' },
            { data: 'slug' },
            { data: 'users_count' },
            { data: 'status', orderable: false, render: (d, t, row) => {
                const opts = statuses.map(s => `<option value="${s.value}" ${s.value === row.status ? 'selected' : ''}>${s.label}</option>`).join('');
                return `<select class="form-select form-select-sm org-status" data-id="${row.id}">${opts}</select>`;
            }},
            { data: 'created_at' },
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: (id, t, row) =>
                `<button class="btn btn-sm btn-icon edit-org" data-id="${row.id}"><i class="icon-base ti tabler-edit"></i></button>`+
                `<button class="btn btn-sm btn-icon impersonate-org" data-id="${row.id}" title="${T.impersonate_admin}"><i class="icon-base ti tabler-user-share"></i></button>`
            },
        ],
        order: [[4, 'desc']],
    });

    const clearErrors = () => form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    document.getElementById('btn-new-org').addEventListener('click', () => {
        clearErrors(); form.reset(); document.getElementById('org-id').value = '';
        document.getElementById('org-modal-title').textContent = T.new;
        modal.show();
    });

    $('#orgs-table tbody').on('click', '.edit-org', function () {
        clearErrors();
        axios.get(`${urls.base}/${this.dataset.id}`).then(({ data }) => {
            document.getElementById('org-id').value = data.id;
            document.getElementById('org-name').value = data.name;
            document.getElementById('org-status').value = data.status;
            document.getElementById('org-modal-title').textContent = T.edit;
            modal.show();
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault(); clearErrors();
        axios.post(urls.save, Object.fromEntries(new FormData(form))).then(({ data }) => {
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

    $('#orgs-table tbody').on('change', '.org-status', function () {
        axios.post(`${urls.base}/${this.dataset.id}/status`, { status: this.value })
            .then(({ data }) => Swal.fire({ icon: 'success', title: data.message, timer: 1200, showConfirmButton: false }))
            .catch(() => { Swal.fire({ icon: 'error', title: T.update_failed }); table.ajax.reload(null, false); });
    });

    $('#orgs-table tbody').on('click', '.impersonate-org', function () {
        const id = this.dataset.id;
        const f = document.createElement('form');
        f.method = 'POST'; f.action = `${urls.base}/${id}/impersonate`;
        f.innerHTML = `@csrf`;
        document.body.appendChild(f); f.submit();
    });
})();
</script>
@endpush
