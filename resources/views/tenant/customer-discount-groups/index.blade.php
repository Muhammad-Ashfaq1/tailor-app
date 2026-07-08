@extends('layouts.app')

@section('title', __('customer_discount_groups.title'))
@section('page-title', __('customer_discount_groups.title'))

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('customer_discount_groups.title') }}</h5>
            @can('customer_discount_groups.create')
                <button class="btn btn-primary" id="btn-new-group"><i class="icon-base ti tabler-plus me-1"></i> {{ __('customer_discount_groups.new') }}</button>
            @endcan
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="groups-table" style="width:100%">
                <thead>
                    <tr>
                        <th>{{ __('customer_discount_groups.fields.name') }}</th>
                        <th>{{ __('customer_discount_groups.fields.discount_percentage') }}</th>
                        <th>{{ __('customer_discount_groups.fields.description') }}</th>
                        <th>{{ __('customer_discount_groups.fields.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="group-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="group-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="group-modal-title">{{ __('customer_discount_groups.new') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="group-id">
                    <div class="row">
                        <x-form.input name="name" id="group-name" :label="__('customer_discount_groups.fields.name')" :placeholder="__('customer_discount_groups.placeholders.name')" wrapper="col-md-12 mb-3" required />
                    </div>
                    <div class="row">
                        <x-form.input name="discount_percentage" id="group-discount-percentage" type="number" step="0.01" min="0" max="100" :label="__('customer_discount_groups.fields.discount_percentage')" :placeholder="__('customer_discount_groups.placeholders.discount_percentage')" wrapper="col-md-12 mb-3" required />
                    </div>
                    <div class="row">
                        <x-form.textarea name="description" id="group-description" :label="__('customer_discount_groups.fields.description')" :placeholder="__('customer_discount_groups.placeholders.description')" wrapper="col-md-12 mb-3" />
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <x-form.switch id="group-active" :label="__('customer_discount_groups.fields.active')" name="is_active" />
                        </div>
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

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.css') }}">
@endpush

@push('vendor-scripts')
    <script src="{{ asset('organization/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endpush

@push('scripts')
<script>
(function () {
    const T = @json(__('customer_discount_groups'));
    const canUpdate = @json(auth()->user()->can('customer_discount_groups.update'));
    const canDelete = @json(auth()->user()->can('customer_discount_groups.delete'));
    const urls = {
        listing: @json(route('tenant.customer-discount-groups.listing')),
        save: @json(route('tenant.customer-discount-groups.save')),
        base: @json(url('tenant/customer-discount-groups')),
    };
    const modal = new bootstrap.Modal(document.getElementById('group-modal'));
    const form = document.getElementById('group-form');

    const table = $('#groups-table').DataTable({
        processing: true, serverSide: true, ajax: { url: urls.listing },
        columns: [
            { data: 'name' },
            { data: 'discount_percentage', render: v => `${Number(v)}%` },
            { data: 'description', render: v => v || '<span class="text-muted">—</span>' },
            { data: 'is_active', render: v => v ? `<span class="badge bg-label-success">${window.AppTranslations.active}</span>` : `<span class="badge bg-label-secondary">${window.AppTranslations.inactive}</span>` },
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: (id, t, row) => {
                let html = '';
                if (canUpdate) html += `<button class="btn btn-sm btn-icon edit-group" data-id="${row.id}"><i class="icon-base ti tabler-edit"></i></button>`;
                if (canDelete) html += `<button class="btn btn-sm btn-icon text-danger delete-group" data-id="${row.id}"><i class="icon-base ti tabler-trash"></i></button>`;
                return html || '<span class="text-muted">—</span>';
            }},
        ],
    });

    const clearErrors = () => form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    document.getElementById('btn-new-group')?.addEventListener('click', () => {
        clearErrors(); form.reset();
        document.getElementById('group-id').value = '';
        document.getElementById('group-active').checked = true;
        document.getElementById('group-modal-title').textContent = T.new;
        modal.show();
    });

    $('#groups-table tbody').on('click', '.edit-group', function () {
        clearErrors();
        const id = this.dataset.id;
        axios.get(`${urls.base}/${id}`).then(({ data }) => {
            document.getElementById('group-id').value = data.id;
            document.getElementById('group-name').value = data.name || '';
            document.getElementById('group-discount-percentage').value = data.discount_percentage || 0;
            document.getElementById('group-description').value = data.description || '';
            document.getElementById('group-active').checked = !!data.is_active;
            document.getElementById('group-modal-title').textContent = T.edit;
            modal.show();
        }).catch(() => notyf.failure(T.load_failed));
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        
        const formData = new FormData(form);
        // Explicitly handle checkbox/switch value for Laravel boolean cast
        if (!formData.has('is_active')) {
            formData.append('is_active', '0');
        } else {
            formData.set('is_active', '1');
        }

        axios.post(urls.save, Object.fromEntries(formData)).then(({ data }) => {
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

    $('#groups-table tbody').on('click', '.delete-group', function () {
        const id = this.dataset.id;
        Swal.fire({
            title: T.delete_confirm, icon: 'warning', showCancelButton: true,
            confirmButtonText: window.AppTranslations.delete, confirmButtonColor: '#dc3545',
        }).then(result => {
            if (!result.isConfirmed) return;
            axios.delete(`${urls.base}/${id}`).then(({ data }) => {
                table.ajax.reload(null, false);
                notyf.success(data.message);
            }).catch(() => notyf.failure(T.delete_failed));
        });
    });
})();
</script>
@endpush
