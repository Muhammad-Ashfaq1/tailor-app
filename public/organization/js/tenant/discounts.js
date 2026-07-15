/**
 * Discount Groups — tenant-side DataTable + CRUD modal
 * Handles only Discount Groups module.
 */

$(function () {
    const $groupTable = $('#discount-groups-table');
    if (!$groupTable.length) return;

    // ── Configuration & DOM Cache ──
    const urls = {
        listing: $groupTable.data('listing-url'),
        save: $groupTable.data('save-url'),
        base: $groupTable.data('base-url'),
    };

    const canUpdate = $groupTable.data('can-update') == 1;
    const canDelete = $groupTable.data('can-delete') == 1;

    const T = window.AppTranslations?.discount_groups || {
        percentage: 'Percentage', fixed: 'Fixed', title: 'Customer Discount Group',
        edit: 'Edit Discount Group', load_failed: 'Failed to load group details.',
        delete_confirm: 'Are you sure?', delete_warning: 'You want to delete this discount group',
        yes_delete: 'Yes', delete_failed: 'Could not delete. Please try again.',
    };

    const modalElement = document.getElementById('discount-group-modal');
    const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
    const form = document.getElementById('discount-group-form');

    const els = {
        id: document.getElementById('dg-id'),
        name: document.getElementById('dg-name'),
        type: document.getElementById('dg-type'),
        value: document.getElementById('dg-value'),
        minLimit: document.getElementById('dg-min-limit'),
        minWrap: document.getElementById('dg-min-limit-wrap'),
        isActive: document.getElementById('dg-is-active'),
        title: document.getElementById('discount-group-modal-title'),
        submitBtn: document.getElementById('dg-submit-btn')
    };

    let dataTableInstance = null;

    // ── Core Functions ──

    const initSelect2 = () => {
        if (typeof $.fn.select2 !== 'function') return;
        $('.select2').each(function () {
            const $this = $(this);
            if ($this.data('select2')) {
                return;
            }
            const dropdownParentSelector = $this.data('dropdown-parent');
            if (!dropdownParentSelector && !$this.parent().hasClass('position-relative')) {
                $this.wrap('<div class="position-relative"></div>');
            }
            $this.select2({
                dropdownParent: dropdownParentSelector ? $(dropdownParentSelector) : $this.parent(),
                placeholder: $this.data('placeholder'),
                allowClear: Boolean($this.data('allow-clear')),
                minimumResultsForSearch: $this.data('minimum-results-for-search') ?? 0,
            });
        });
    };

    const initDataTable = () => {
        dataTableInstance = $groupTable.DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: urls.listing },
            order: [[0, 'asc']],
            language: {
                search: '',
                searchPlaceholder: window.AppTranslations?.searchPlaceholder || 'Search…'
            },
            columns: [
                { data: 'name' },
                { data: 'slug' },
                { data: 'type', render: v => v === 'percentage' ? T.percentage : T.fixed },
                { data: 'value_label' },
                { data: 'min_limit_label', orderable: false },
                {
                    data: 'is_active',
                    render: (v, t, row) => {
                        const label = v ? (window.AppTranslations?.active || 'Active') : (window.AppTranslations?.inactive || 'Inactive');
                        return `<span class="badge ${row.status_badge_class}">${label}</span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: id => {
                        let html = '';
                        if (canUpdate) html += `<button class="btn btn-sm btn-icon edit-group" data-id="${id}" title="Edit"><i class="icon-base ti tabler-edit"></i></button>`;
                        if (canDelete) html += `<button class="btn btn-sm btn-icon text-danger delete-group" data-id="${id}" title="Delete"><i class="icon-base ti tabler-trash"></i></button>`;
                        return html || '<span class="text-muted">—</span>';
                    }
                },
            ],
        });
    };

    const toggleTypeUI = () => {
        if (els.type.value === 'fixed') els.minWrap?.classList.remove('d-none');
        else els.minWrap?.classList.add('d-none');
    };

    const clearErrors = () => {
        if (!form) return;
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('[data-field]').forEach(el => (el.textContent = ''));
    };

    const applyErrors = (errors) => {
        Object.keys(errors).forEach(f => {
            const input = form.querySelector(`[name="${f}"]`);
            const fb = form.querySelector(`[data-field="${f}"]`);
            if (input) input.classList.add('is-invalid');
            if (fb) fb.textContent = errors[f][0];
        });
    };

    const resetForm = () => {
        if (!form) return;
        clearErrors();
        form.reset();
        els.id.value = '';
        els.isActive.checked = true;
        $(els.type).val('').trigger('change');

        if (els.title) els.title.textContent = T.title;
        if (els.submitBtn) els.submitBtn.textContent = window.AppTranslations?.save || 'Save';
    };

    const fillForm = (data) => {
        els.id.value = data.id;
        els.name.value = data.name;
        $(els.type).val(data.type).trigger('change');
        els.value.value = data.value;
        els.minLimit.value = data.min_limit ?? '';
        els.isActive.checked = !!data.is_active;

        if (els.title) els.title.textContent = T.edit;
        if (els.submitBtn) els.submitBtn.textContent = window.AppTranslations?.save || 'Save';
    };

    const bindModalActions = () => {
        $(els.type).on('change', toggleTypeUI);

        document.getElementById('btn-add-group')?.addEventListener('click', () => {
            resetForm();
            modal?.show();
        });

        $groupTable.on('click', '.edit-group', function () {
            clearErrors();
            axios.get(`${urls.base}/${this.dataset.id}`).then(({ data }) => {
                fillForm(data);
                modal?.show();
            }).catch(() => notyf.failure(T.load_failed));
        });
    };

    const bindSaveForm = () => {
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();

            const id = els.id.value;
            const payload = Object.fromEntries(new FormData(form));
            payload.is_active = els.isActive.checked ? 1 : 0;

            axios.post(id ? `${urls.base}/save/${id}` : urls.save, payload).then(({ data }) => {
                modal?.hide();
                dataTableInstance.ajax.reload(null, false);
                notyf.success(data.message);
            }).catch(err => {
                if (err.response?.status === 422) {
                    const errors = err.response.data.errors || {};
                    const firstError = Object.values(errors)[0]?.[0];
                    if (firstError) notyf.failure(firstError);
                } else {
                    notyf.failure(err.response?.data?.message || 'Something went wrong.');
                }
            });
        });
    };

    const bindDeleteActions = () => {
        $groupTable.on('click', '.delete-group', function () {
            const id = this.dataset.id;
            Swal.fire({
                title: T.delete_confirm,
                text: T.delete_warning,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: T.yes_delete,
                confirmButtonColor: '#dc3545',
                cancelButtonText: window.AppTranslations?.cancel || 'Cancel',
            }).then(result => {
                if (!result.isConfirmed) return;
                axios.delete(`${urls.base}/${id}`).then(({ data }) => {
                    dataTableInstance.ajax.reload(null, false);
                    notyf.success(data.message);
                }).catch(() => notyf.failure(T.delete_failed));
            });
        });
    };

    // ── Initialization ──
    initSelect2();
    initDataTable();
    bindModalActions();
    bindSaveForm();
    bindDeleteActions();
    toggleTypeUI();
});
