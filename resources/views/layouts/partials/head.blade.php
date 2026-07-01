{{-- Shared <head> for the Vuexy admin layouts. Static assets only — no build step. --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name')) &middot; {{ config('app.name') }}</title>

@include('layouts.partials.pwa-head')

{{-- Vuexy core theme (copied from the template into public/organization). --}}
<link rel="stylesheet" href="{{ asset('organization/vendor/fonts/iconify-icons.css') }}">
<link rel="stylesheet" href="{{ asset('organization/vendor/css/core.css') }}">
<link rel="stylesheet" href="{{ asset('organization/css/demo.css') }}">
<link rel="stylesheet" href="{{ asset('organization/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
{{-- Notyf toast notifications (global — used via window.notyf). --}}
<link rel="stylesheet" href="{{ asset('organization/vendor/libs/notyf/notyf.css') }}">
{{-- Pickr — colour picker used by the theme customizer. --}}
<link rel="stylesheet" href="{{ asset('organization/vendor/libs/pickr/pickr-themes.css') }}">

@if (app()->getLocale() === 'ar')
    {{-- Custom RTL overrides — loaded only for Arabic (no Vuexy RTL build present). --}}
    <link rel="stylesheet" href="{{ asset('organization/css/custom-rtl.css') }}">
@endif

@stack('vendor-styles')
@stack('styles')

{{-- Per-organization currency config for JS (window.appCurrency). --}}
<script>
    window.appCurrency = @json(\App\Support\Currency::jsConfig());

    // Locale + direction + translatable UI strings for JS (DataTables, SweetAlert2,
    // Select2, notyf). Keep server-side keys English; only labels are translated.
    window.AppLocale = @json(app()->getLocale());
    window.AppDirection = @json(app()->getLocale() === 'ar' ? 'rtl' : 'ltr');
    window.AppTranslations = {
        datatable: {
            processing: @json(__('app.datatable.processing')),
            search: @json(__('app.datatable.search')),
            lengthMenu: @json(__('app.datatable.length_menu')),
            info: @json(__('app.datatable.info')),
            infoEmpty: @json(__('app.datatable.info_empty')),
            infoFiltered: @json(__('app.datatable.info_filtered')),
            zeroRecords: @json(__('app.datatable.zero_records')),
            emptyTable: @json(__('app.datatable.empty_table')),
            paginate: {
                first: @json(__('app.datatable.first')),
                last: @json(__('app.datatable.last')),
                next: @json(__('app.datatable.next')),
                previous: @json(__('app.datatable.previous')),
            },
        },
        confirmDelete: @json(__('app.confirm_delete')),
        confirmDeleteText: @json(__('app.confirm_delete_text')),
        yesDelete: @json(__('app.yes_delete')),
        cancel: @json(__('app.cancel')),
        delete: @json(__('app.delete')),
        savedSuccessfully: @json(__('app.saved_successfully')),
        deletedSuccessfully: @json(__('app.deleted_successfully')),
        operationFailed: @json(__('app.operation_failed')),
        active: @json(__('app.active')),
        inactive: @json(__('app.inactive')),
    };
</script>

{{-- axios + our app bootstrap (plain static files, no bundler). --}}
<script src="{{ asset('organization/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('organization/js/app.js') }}"></script>

<script src="{{ asset('organization/vendor/js/helpers.js') }}"></script>
{{-- Theme customizer (skin / theme / colour). Must load after helpers.js, before config.js. --}}
<script src="{{ asset('organization/vendor/js/template-customizer.js') }}"></script>
<script src="{{ asset('organization/js/config.js') }}"></script>
