/**
 * LenDen Enterprise - Subscription Registration Interactive Script
 */

function selectPlan(planId) {
    const input = document.getElementById('selectedPlanIdInput');
    if (input) input.value = planId;
    
    document.querySelectorAll('.plan-select-card').forEach(function(c) {
        c.classList.remove('selected');
    });
    
    const card = document.getElementById('planCard_' + planId);
    if (card) card.classList.add('selected');
}

function setCycle(cycle) {
    const input = document.getElementById('selectedCycleInput');
    if (input) input.value = cycle;
    
    const btnM = document.getElementById('btnMonthly');
    const btnY = document.getElementById('btnYearly');
    
    if (btnY && btnM) {
        if (cycle === 'yearly') {
            btnY.className = 'btn cycle-toggle-btn btn-primary';
            btnM.className = 'btn cycle-toggle-btn btn-light text-muted';
        } else {
            btnM.className = 'btn cycle-toggle-btn btn-primary';
            btnY.className = 'btn cycle-toggle-btn btn-light text-muted';
        }
    }

    document.querySelectorAll('.rate-display').forEach(function(el) {
        const rate = (cycle === 'yearly') ? el.getAttribute('data-yrate') : el.getAttribute('data-mrate');
        el.innerText = '₹' + parseFloat(rate).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    });

    document.querySelectorAll('.cycle-label').forEach(function(el) {
        el.innerText = (cycle === 'yearly') ? '/yr' : '/mo';
    });
}

