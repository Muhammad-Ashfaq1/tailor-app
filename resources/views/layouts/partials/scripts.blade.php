{{-- Vuexy core JS + page libs. jQuery is required by DataTables/Select2. --}}
<script src="{{ asset('vuexy/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('vuexy/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('vuexy/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('vuexy/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('vuexy/vendor/js/menu.js') }}"></script>

@stack('vendor-scripts')

<script src="{{ asset('vuexy/js/main.js') }}"></script>

@stack('scripts')
