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
})();
