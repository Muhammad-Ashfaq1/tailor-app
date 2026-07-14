@extends('layouts.app')

@section('title', __('discount_groups.title'))
@section('page-title', __('discount_groups.title'))

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('discount_groups.customer_groups') }}</h5>
            @can('discount-groups.create')
                <button class="btn btn-primary" id="btn-add-group">
                    <i class="icon-base ti tabler-plus me-1"></i> {{ __('discount_groups.add_new') }}
                </button>
            @endcan
        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table" id="discount-groups-table" style="width:100%"
                   data-listing-url="{{ route('tenant.discount-groups.listing') }}"
                   data-save-url="{{ route('tenant.discount-groups.save') }}"
                   data-base-url="{{ url('tenant/discount-groups') }}"
                   data-can-update="{{ auth()->user()->can('discount-groups.update') ? 1 : 0 }}"
                   data-can-delete="{{ auth()->user()->can('discount-groups.delete') ? 1 : 0 }}">
                <thead>
                    <tr>
                        <th>{{ __('discount_groups.name') }}</th>
                        <th>{{ __('discount_groups.slug') }}</th>
                        <th>{{ __('discount_groups.type') }}</th>
                        <th>{{ __('discount_groups.value') }}</th>
                        <th>{{ __('discount_groups.min_limit') }}</th>
                        <th>{{ __('discount_groups.status') }}</th>
                        <th class="text-end">{{ __('discount_groups.actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Create / Edit Modal --}}
    <div class="modal fade" id="discount-group-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="discount-group-modal-title">{{ __('discount_groups.title') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="discount-group-form">
                    <div class="modal-body p-4">
                        <input type="hidden" name="id" id="dg-id">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('discount_groups.title_name') }}</label>
                                <input class="form-control" id="dg-name" name="name" placeholder="Group Title" required>
                                <div class="invalid-feedback" data-field="name"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('discount_groups.discount_type') }}</label>
                                <select class="form-select" id="dg-type" name="type" required>
                                    <option value="" selected disabled>{{ __('discount_groups.select_type') }}</option>
                                    <option value="percentage">{{ __('discount_groups.percentage') }}</option>
                                    <option value="fixed">{{ __('discount_groups.fixed') }}</option>
                                </select>
                                <div class="invalid-feedback" data-field="type"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('discount_groups.discount_value') }}</label>
                                <input class="form-control" id="dg-value" name="value" type="number" min="0" step="0.01" required>
                                <div class="invalid-feedback" data-field="value"></div>
                            </div>
                            <div class="col-md-6 d-none" id="dg-min-limit-wrap">
                                <label class="form-label">{{ __('discount_groups.min_purchase_limit') }}</label>
                                <input class="form-control" id="dg-min-limit" name="min_limit" type="number" min="0" step="0.01">
                                <div class="invalid-feedback" data-field="min_limit"></div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="dg-is-active" name="is_active" value="1" checked>
                                    <label class="form-check-label">{{ __('discount_groups.is_active') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end px-4 pb-4">
                        <button class="btn btn-primary btn-lg px-5 fw-bold rounded-3" id="dg-submit-btn">{{ __('app.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('vendor-styles')
    <link class="datatables-css" rel="stylesheet" href="{{ asset('organization/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.css') }}">
@endpush

@push('vendor-scripts')
    <script src="{{ asset('organization/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('organization/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endpush

@push('scripts')
    <script>
        // Provide translations globally so discount-groups.js can read them
        window.AppTranslations = window.AppTranslations || {};
        window.AppTranslations.discount_groups = @json(__('discount_groups'));
    </script>
    <script src="{{ asset('organization/js/tenant/discount-groups.js') }}"></script>
@endpush
