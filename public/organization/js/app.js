/*
 | App JS bootstrap (plain static file, NO build step).
 | Loaded after libs/axios/axios.min.js, so `axios` is a global.
 | Owns: CSRF-aware axios defaults + per-org currency formatting.
 */
(function () {
    'use strict';

    if (window.axios) {
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        var token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
    }

    // Mirror of App\Support\Currency::format() for client-side rendering.
    window.formatMoney = function (amount) {
        var c = window.appCurrency || { symbol: '$', position: 'before', decimals: 2 };
        var value = Number(amount || 0).toFixed(c.decimals == null ? 2 : c.decimals);
        return c.position === 'after' ? value + c.symbol : c.symbol + value;
    };

    /*
     | Global toast notifications: window.notyf.success/failure/warning/info.
     | Backed by the Notyf library (window.Notyf, loaded near the bottom of the
     | page) with a plain alert() fallback. The Notyf instance is created lazily
     | on first use — so script order never matters (toasts only fire on user
     | actions, long after the library has loaded).
     */
    var notyfInstance = null;

    function getNotyf() {
        if (notyfInstance) {
            return notyfInstance;
        }
        if (typeof window.Notyf === 'function') {
            notyfInstance = new window.Notyf({
                duration: 3000,
                ripple: true,
                dismissible: true,
                position: { x: 'right', y: 'top' },
                types: [
                    { type: 'warning', background: '#ff9f43', icon: false },
                    { type: 'info', background: '#03c3ec', icon: false },
                ],
            });
        }
        return notyfInstance; // null until the library has loaded
    }

    function notify(kind, message) {
        var n = getNotyf();
        if (!n) {
            window.alert(message);
            return;
        }
        if (kind === 'success') {
            n.success(message);
        } else if (kind === 'failure') {
            n.error(message);
        } else {
            n.open({ type: kind, message: message }); // warning / info
        }
    }

    window.notyf = {
        success: function (m) { notify('success', m); },
        failure: function (m) { notify('failure', m); },
        warning: function (m) { notify('warning', m); },
        info: function (m) { notify('info', m); },
    };

    /*
     | Password show/hide toggle. Any eye icon marked [data-password-toggle]
     | inside an .input-group toggles the sibling password input. Delegated on
     | document so it also works for inputs rendered later (e.g. in modals).
     */
    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-password-toggle]');
        if (!toggle) {
            return;
        }
        var group = toggle.closest('.input-group');
        var input = group ? group.querySelector('input') : null;
        if (!input) {
            return;
        }
        var showing = input.type === 'password';
        input.type = showing ? 'text' : 'password';
        var icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('tabler-eye', showing);
            icon.classList.toggle('tabler-eye-off', !showing);
        }
    });
})();
