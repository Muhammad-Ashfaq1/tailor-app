{{--
    Server-side flash + validation messages, surfaced as global notyf toasts
    (Notyf, with an alert() fallback — see public/organization/js/app.js).
    Included by every layout (app / member-portal / auth), so `status`,
    `error` and validation errors (e.g. invalid login credentials) all render
    consistently across the whole application.
--}}
@php
    $flashSuccess = session('status');
    $flashError = session('error');
    $flashErrors = $errors->all();
@endphp

@if ($flashSuccess || $flashError || count($flashErrors) > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.notyf) { return; }
            @if ($flashSuccess)
                window.notyf.success(@json($flashSuccess));
            @endif
            @if ($flashError)
                window.notyf.failure(@json($flashError));
            @endif
            @foreach ($flashErrors as $error)
                window.notyf.failure(@json($error));
            @endforeach
        });
    </script>
@endif
