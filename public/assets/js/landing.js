/**
 * LenDen Enterprise - Landing Page Interactive Scripts
 */

let currentCycle = 'monthly';

function togglePricing(cycle) {
    currentCycle = cycle;
    const btnM = document.getElementById('btnMonthly');
    const btnY = document.getElementById('btnYearly');

    if (btnY && btnM) {
        if (cycle === 'yearly') {
            btnY.classList.add('active');
            btnM.classList.remove('active');
        } else {
            btnM.classList.add('active');
            btnY.classList.remove('active');
        }
    }

    document.querySelectorAll('.rate-num').forEach(function(el) {
        const rate = (cycle === 'yearly') ? el.getAttribute('data-yrate') : el.getAttribute('data-mrate');
        el.innerText = '₹' + parseFloat(rate).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    });

    document.querySelectorAll('.rate-cycle').forEach(function(el) {
        el.innerText = (cycle === 'yearly') ? '/year' : '/month';
    });

    const metaBase = document.querySelector('meta[name="base-url"]');
    const baseUrl = metaBase ? metaBase.getAttribute('content').replace(/\/+$/, '') : '';

    document.querySelectorAll('.plan-subscribe-link').forEach(function(link) {
        const code = link.getAttribute('data-code');
        link.href = baseUrl + '/subscribe?plan=' + encodeURIComponent(code) + '&cycle=' + cycle;
    });
}

