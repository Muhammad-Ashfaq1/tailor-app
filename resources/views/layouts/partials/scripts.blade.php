{{-- Vuexy core JS + page libs (static files). jQuery powers DataTables/Select2. --}}
<script src="{{ asset('organization/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('organization/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('organization/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('organization/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('organization/vendor/js/menu.js') }}"></script>
{{-- Pickr — powers the theme customizer's colour picker (needed before window.onload). --}}
<script src="{{ asset('organization/vendor/libs/pickr/pickr.js') }}"></script>
{{-- Notyf — global toast lib behind window.notyf (see js/app.js). --}}
<script src="{{ asset('organization/vendor/libs/notyf/notyf.js') }}"></script>

@stack('vendor-scripts')

<script src="{{ asset('organization/js/main.js') }}"></script>

@stack('scripts')
