import axios from 'axios';

/*
 | App JS bootstrap. The Vuexy admin layouts additionally load jQuery,
 | DataTables, Select2, ApexCharts and SweetAlert2 from the bundled template
 | libs (see layouts/partials/scripts.blade.php). This file owns the things we
 | want everywhere: a CSRF-aware axios instance and currency formatting.
 */

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

/**
 * Format a numeric amount using the per-organization currency settings
 * injected as window.appCurrency by the layout head. Mirrors App\Support\Currency.
 */
window.formatMoney = function (amount) {
    const c = window.appCurrency || { symbol: '$', position: 'before', decimals: 2 };
    const value = Number(amount || 0).toFixed(c.decimals ?? 2);
    return c.position === 'after' ? `${value}${c.symbol}` : `${c.symbol}${value}`;
};

export { axios };
