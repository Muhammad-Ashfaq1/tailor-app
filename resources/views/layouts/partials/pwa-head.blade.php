{{-- PWA: manifest + service-worker registration. Included in every layout <head>. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#7367f0">
<link rel="apple-touch-icon" href="{{ asset('vuexy/img/favicon/favicon.ico') }}">
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function (e) {
                console.warn('SW registration failed', e);
            });
        });
    }
</script>
