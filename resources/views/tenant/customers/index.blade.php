@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Customers</h5>
            @can('customers.create')
                <button class="btn btn-primary" id="btn-new-customer"><i class="icon-base ti tabler-plus me-1"></i> New Customer</button>
            @endcan
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="customers-table" style="width:100%">
                <thead><tr><th>Name</th><th>Phone</th><th>Type</th><th>Credit</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="customer-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content" id="customer-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="customer-modal-title">New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="customer-id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="customer-name">Name</label>
                            <input type="text" class="form-control" id="customer-name" name="name" required>
                            <div class="invalid-feedback" data-field="name"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="customer-phone">Phone</label>
                            <input type="text" class="form-control" id="customer-phone" name="phone">
                            <div class="invalid-feedback" data-field="phone"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="customer-type">Type</label>
                            <select class="form-select" id="customer-type" name="type">
                                @foreach ($types as $type)
                                    <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-field="type"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="customer-email">Email <small class="text-muted">(optional — for app login)</small></label>
                            <input type="email" class="form-control" id="customer-email" name="email">
                            <div class="invalid-feedback" data-field="email"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="customer-credit-type">Credit reward</label>
                            <select class="form-select" id="customer-credit-type" name="credit_type">
                                @foreach ($creditTypes as $ct)
                                    <option value="{{ $ct['value'] }}">{{ $ct['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-field="credit_type"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="customer-credit-value">Credit value <small class="text-muted" id="credit-hint"></small></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="customer-credit-value" name="credit_value" value="0">
                            <div class="invalid-feedback" data-field="credit_value"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="customer-address">Address</label>
                        <textarea class="form-control" id="customer-address" name="address" rows="2"></textarea>
                        <div class="invalid-feedback" data-field="address"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="customer-notes">Notes</label>
                        <textarea class="form-control" id="customer-notes" name="notes" rows="2"></textarea>
                        <div class="invalid-feedback" data-field="notes"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="customer-password">App password <small class="text-muted">(optional)</small></label>
                            <div class="input-group input-group-merge has-validation">
                                <input type="password" class="form-control" id="customer-password" name="password" autocomplete="new-password">
                                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
                                <div class="invalid-feedback" data-field="password"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="customer-password2">Confirm</label>
                            <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="customer-password2" name="password_confirmation" autocomplete="new-password">
                                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="customer-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="customer-active">Active</label>
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
            { data: 'is_active', render: v => v ? '<span class="badge bg-label-success">Active</span>' : '<span class="badge bg-label-secondary">Inactive</span>' },
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
        document.getElementById('credit-hint').textContent = v === 'percentage' ? '(%)' : (v === 'fixed' ? '(amount)' : '');
    };
    document.getElementById('customer-credit-type').addEventListener('change', updateCreditHint);

    document.getElementById('btn-new-customer')?.addEventListener('click', () => {
        clearErrors(); form.reset();
        document.getElementById('customer-id').value = '';
        document.getElementById('customer-active').checked = true;
        document.getElementById('customer-modal-title').textContent = 'New Customer';
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
            document.getElementById('customer-modal-title').textContent = 'Edit Customer';
            updateCreditHint();
            modal.show();
        }).catch(() => notyf.failure('Could not load customer'));
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

    $('#customers-table tbody').on('click', '.delete-customer', function () {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Delete this customer?', icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Delete', confirmButtonColor: '#dc3545',
        }).then(result => {
            if (!result.isConfirmed) return;
            axios.delete(`${urls.base}/${id}`).then(({ data }) => {
                table.ajax.reload(null, false);
                notyf.success(data.message);
            }).catch(() => notyf.failure('Could not delete customer'));
        });
    });
})();
</script>
@endpush
