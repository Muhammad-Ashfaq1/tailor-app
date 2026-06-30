{{-- Vuexy core JS + page libs (static files). jQuery powers DataTables/Select2. --}}
<script src="{{ asset('organization/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('organization/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('organization/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('organization/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('organization/vendor/js/menu.js') }}"></script>

@stack('vendor-scripts')

<script src="{{ asset('organization/js/main.js') }}"></script>

@stack('scripts')
