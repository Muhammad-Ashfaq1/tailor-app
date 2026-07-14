/**
 * Discount Groups — tenant-side DataTable + CRUD modal
 * Handles only Discount Groups module.
 */

$(function () {
    const $groupTable = $('#discount-groups-table');
    if (!$groupTable.length) return;

    const urls = {
        listing : $groupTable.data('listing-url'),
        save    : $groupTable.data('save-url'),
        base    : $groupTable.data('base-url')
    };

    const canUpdate = $groupTable.data('can-update') == 1;
    const canDelete = $groupTable.data('can-delete') == 1;

    const T = window.AppTranslations?.discount_groups || {
        percentage: 'Percentage',
        fixed: 'Fixed',
        title: 'Customer Discount Group',
        edit: 'Edit Discount Group',
        load_failed: 'Failed to load group details.',
        delete_confirm: 'Are you sure?',
        delete_warning: 'You want to delete this discount group',
        yes_delete: 'Yes',
        delete_failed: 'Could not delete. Please try again.'
    };

    const modalElement = document.getElementById('discount-group-modal');
    const modal        = modalElement ? new bootstrap.Modal(modalElement) : null;
    const form         = document.getElementById('discount-group-form');
    const titleEl      = document.getElementById('discount-group-modal-title');
    const typeEl       = document.getElementById('dg-type');
    const minWrap      = document.getElementById('dg-min-limit-wrap');
    const submitBtn    = document.getElementById('dg-submit-btn');

    function toggleTypeUI() {
        if (typeEl && typeEl.value === 'fixed') {
            minWrap?.classList.remove('d-none');
        } else {
            minWrap?.classList.add('d-none');
        }
    }
    if (typeEl) {
        typeEl.addEventListener('change', toggleTypeUI);
    }

    const table = $groupTable.DataTable({
        processing : true,
        serverSide : true,
        ajax       : { url: urls.listing },
        order      : [[0, 'asc']],
        language   : { search: '', searchPlaceholder: window.AppTranslations?.searchPlaceholder || 'Search…' },
        columns    : [
            { data: 'name' },
            { data: 'slug' },
            {
                data: 'type',
                render: (v) => v === 'percentage' ? T.percentage : T.fixed,
            },
            { data: 'value_label' },
            { data: 'min_limit_label', orderable: false },
            {
                data: 'is_active',
                orderable: false,
                render: (v, t, row) => {
                    const label = v
                        ? (window.AppTranslations?.active   || 'Active')
                        : (window.AppTranslations?.inactive || 'Inactive');
                    return `<span class="badge ${row.status_badge_class}">${label}</span>`;
                },
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: (id) => {
                    let html = '';
                    if (canUpdate) {
                        html += `<button class="btn btn-sm btn-icon edit-group" data-id="${id}" title="Edit">
                                    <i class="icon-base ti tabler-edit"></i>
                                 </button>`;
                    }
                    if (canDelete) {
                        html += `<button class="btn btn-sm btn-icon text-danger delete-group" data-id="${id}" title="Delete">
                                    <i class="icon-base ti tabler-trash"></i>
                                 </button>`;
                    }
                    return html || '<span class="text-muted">—</span>';
                },
            },
        ],
    });

    function clearErrors() {
        if (!form) return;
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('[data-field]').forEach(el => (el.textContent = ''));
    }

    function applyErrors(errors) {
        if (!form) return;
        Object.entries(errors).forEach(([field, msgs]) => {
            const input = form.querySelector(`[name="${field}"]`);
            const fb    = form.querySelector(`[data-field="${field}"]`);
            if (input) input.classList.add('is-invalid');
            if (fb)    fb.textContent = msgs[0];
        });
    }

    document.getElementById('btn-add-group')?.addEventListener('click', () => {
        if (!form) return;
        clearErrors();
        form.reset();
        document.getElementById('dg-id').value = '';
        document.getElementById('dg-is-active').checked = true;
        if (typeEl) {
            typeEl.value = '';
            toggleTypeUI();
        }
        if (titleEl) titleEl.textContent = T.title;
        if (submitBtn) submitBtn.textContent = window.AppTranslations?.save || 'Save';
        modal?.show();
    });

    $groupTable.on('click', '.edit-group', function () {
        const id = this.dataset.id;
        clearErrors();

        axios.get(`${urls.base}/${id}`)
            .then(({ data }) => {
                document.getElementById('dg-id').value          = data.id;
                document.getElementById('dg-name').value        = data.name;
                if (typeEl) {
                    typeEl.value = data.type;
                    toggleTypeUI();
                }
                document.getElementById('dg-value').value       = data.value;
                document.getElementById('dg-min-limit').value   = data.min_limit ?? '';
                document.getElementById('dg-is-active').checked = !!data.is_active;
                if (titleEl) titleEl.textContent = T.edit;
                if (submitBtn) submitBtn.textContent = window.AppTranslations?.save || 'Save';
                modal?.show();
            })
            .catch(() => notyf.error(T.load_failed));
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();

            const id      = document.getElementById('dg-id').value;
            const saveUrl = id ? `${urls.base}/save/${id}` : urls.save;

            const payload     = Object.fromEntries(new FormData(form));
            payload.is_active = document.getElementById('dg-is-active').checked ? 1 : 0;

            axios.post(saveUrl, payload)
                .then(({ data }) => {
                    modal?.hide();
                    table.ajax.reload(null, false);
                    notyf.success(data.message);
                })
                .catch(err => {
                    if (err.response?.status === 422) {
                        const errors = err.response.data.errors || {};
                        applyErrors(errors);
                        const firstError = Object.values(errors)[0]?.[0];
                        if (firstError) notyf.error(firstError);
                    } else {
                        notyf.error(err.response?.data?.message || 'Something went wrong.');
                    }
                });
        });
    }

    $groupTable.on('click', '.delete-group', function () {
        const id = this.dataset.id;

        Swal.fire({
            title             : T.delete_confirm,
            text              : T.delete_warning,
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonText : T.yes_delete,
            confirmButtonColor: '#dc3545',
            cancelButtonText  : window.AppTranslations?.cancel || 'Cancel',
        }).then(result => {
            if (!result.isConfirmed) return;

            axios.delete(`${urls.base}/${id}`)
                .then(({ data }) => {
                    table.ajax.reload(null, false);
                    notyf.success(data.message);
                })
                .catch(() => notyf.error(T.delete_failed));
        });
    });
});
