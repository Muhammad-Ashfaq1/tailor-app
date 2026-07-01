@extends('layouts.app')

@section('title', __('customers.title'))
@section('page-title', __('customers.title'))

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('customers.title') }}</h5>
            @can('customers.create')
                <button class="btn btn-primary" id="btn-new-customer"><i class="icon-base ti tabler-plus me-1"></i> {{ __('customers.new') }}</button>
            @endcan
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="customers-table" style="width:100%">
                <thead><tr><th>{{ __('customers.name') }}</th><th>{{ __('customers.phone') }}</th><th>{{ __('customers.type') }}</th><th>{{ __('customers.credit') }}</th><th>{{ __('customers.status') }}</th><th class="text-end">{{ __('app.actions') }}</th></tr></thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="customer-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content" id="customer-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="customer-modal-title">{{ __('customers.new') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="customer-id">
                    <div class="row">
                        <x-form.input name="name" id="customer-name" :label="__('customers.name')" wrapper="col-md-6 mb-3" required />
                        <x-form.input name="phone" id="customer-phone" :label="__('customers.phone')" wrapper="col-md-6 mb-3" />
                    </div>
                    <div class="row">
                        <x-form.select name="type" id="customer-type" :label="__('customers.type')" wrapper="col-md-6 mb-3">
                            @foreach ($types as $type)
                                <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.input name="email" id="customer-email" type="email" :label="__('customers.email')" wrapper="col-md-6 mb-3">
                            <x-slot:hint><small class="text-muted">{{ __('customers.email_hint') }}</small></x-slot:hint>
                        </x-form.input>
                    </div>
                    <div class="row">
                        <x-form.select name="credit_type" id="customer-credit-type" :label="__('customers.credit_reward')" wrapper="col-md-6 mb-3">
                            @foreach ($creditTypes as $ct)
                                <option value="{{ $ct['value'] }}">{{ $ct['label'] }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.input name="credit_value" id="customer-credit-value" type="number" step="0.01" min="0" value="0" :label="__('customers.credit_value')" wrapper="col-md-6 mb-3">
                            <x-slot:hint><small class="text-muted" id="credit-hint"></small></x-slot:hint>
                        </x-form.input>
                    </div>
                    <x-form.textarea name="address" id="customer-address" :label="__('customers.address')" />
                    <x-form.textarea name="notes" id="customer-notes" :label="__('customers.notes')" />
                    <div class="row">
                        <x-form.password name="password" id="customer-password" :label="__('customers.app_password')" wrapper="col-md-6 mb-3">
                            <x-slot:hint><small class="text-muted">{{ __('customers.app_password_hint') }}</small></x-slot:hint>
                        </x-form.password>
                        <x-form.password name="password_confirmation" id="customer-password2" :label="__('customers.confirm_password')" :feedback="false" wrapper="col-md-6 mb-3" />
                    </div>
                    <x-form.switch id="customer-active" :label="__('customers.active')" />
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
    const T = @json(__('customers'));
    const canUpdate = @json(auth()->user()->can('customers.update'));
    const canDelete = @json(auth()->user()->can('customers.delete'));
    const urls = {
        listing: @json(route('tenant.customers.listing')),
        save: @json(route('tenant.customers.save')),
        base: @json(url('tenant/customers')),
    };
    const modal = new bootstrap.Modal(document.getElementById('customer-modal'));
    const form = document.getElementById('customer-form');

    const creditCell = (row) => {
        if (row.credit_type === 'percentage') return `${Number(row.credit_value)}%`;
        if (row.credit_type === 'fixed') return window.formatMoney(row.credit_value);
        return '<span class="text-muted">—</span>';
    };

    const table = $('#customers-table').DataTable({
        processing: true, serverSide: true, ajax: { url: urls.listing },
        columns: [
            { data: 'name' },
            { data: 'phone', render: v => v || '<span class="text-muted">—</span>' },
            { data: 'type_label', render: (v, t, row) => `<span class="badge bg-label-${row.type_color}">${v}</span>` },
            { data: 'credit_value', orderable: false, searchable: false, render: (v, t, row) => creditCell(row) },
            { data: 'is_active', render: v => v ? `<span class="badge bg-label-success">${window.AppTranslations.active}</span>` : `<span class="badge bg-label-secondary">${window.AppTranslations.inactive}</span>` },
            { data: 'id', orderable: false, searchable: false, className: 'text-end', render: (id, t, row) => {
                let html = '';
                if (canUpdate) html += `<button class="btn btn-sm btn-icon edit-customer" data-id="${row.id}"><i class="icon-base ti tabler-edit"></i></button>`;
                if (canDelete) html += `<button class="btn btn-sm btn-icon text-danger delete-customer" data-id="${row.id}"><i class="icon-base ti tabler-trash"></i></button>`;
                return html || '<span class="text-muted">—</span>';
            }},
        ],
    });

    const clearErrors = () => form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    const updateCreditHint = () => {
        const v = document.getElementById('customer-credit-type').value;
        document.getElementById('credit-hint').textContent = v === 'percentage' ? T.credit_hint_percentage : (v === 'fixed' ? T.credit_hint_fixed : '');
    };
    document.getElementById('customer-credit-type').addEventListener('change', updateCreditHint);

    document.getElementById('btn-new-customer')?.addEventListener('click', () => {
        clearErrors(); form.reset();
        document.getElementById('customer-id').value = '';
        document.getElementById('customer-active').checked = true;
        document.getElementById('customer-modal-title').textContent = T.new;
        updateCreditHint();
        modal.show();
    });

    $('#customers-table tbody').on('click', '.edit-customer', function () {
        clearErrors();
        const id = this.dataset.id;
        axios.get(`${urls.base}/${id}`).then(({ data }) => {
            document.getElementById('customer-id').value = data.id;
            document.getElementById('customer-name').value = data.name || '';
            document.getElementById('customer-phone').value = data.phone || '';
            document.getElementById('customer-email').value = data.email || '';
            document.getElementById('customer-type').value = data.type;
            document.getElementById('customer-credit-type').value = data.credit_type;
            document.getElementById('customer-credit-value').value = data.credit_value ?? 0;
            document.getElementById('customer-address').value = data.address || '';
            document.getElementById('customer-notes').value = data.notes || '';
            document.getElementById('customer-password').value = '';
            document.getElementById('customer-password2').value = '';
            document.getElementById('customer-active').checked = !!data.is_active;
            document.getElementById('customer-modal-title').textContent = T.edit;
            updateCreditHint();
            modal.show();
        }).catch(() => notyf.failure(T.load_failed));
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

    $('#customers-table tbody').on('click', '.delete-customer', function () {
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
