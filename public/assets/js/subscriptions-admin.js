/**
 * LenDen Enterprise - Subscription Administration Interactive Scripts
 */

function getBaseUrl() {
    const metaBase = document.querySelector('meta[name="base-url"]');
    return metaBase ? metaBase.getAttribute('content').replace(/\/+$/, '') : '';
}

function showEditSubModal(sub) {
    const form = document.getElementById('editSubForm');
    if (!form) return;

    form.action = getBaseUrl() + '/subscriptions/update/' + sub.subscription_id;

    const nameEl = document.getElementById('editModalCompanyName');
    if (nameEl) nameEl.innerText = sub.company_name || 'Company #' + sub.subscription_company_id;

    const metaEl = document.getElementById('editModalCompanyMeta');
    if (metaEl) metaEl.innerText = 'Code: ' + (sub.company_code || 'N/A') + ' • ' + (sub.company_city || 'Mumbai');

    const planEl = document.getElementById('editSubPlan');
    if (planEl) planEl.value = sub.subscription_plan_id;

    const cycleEl = document.getElementById('editSubCycle');
    if (cycleEl) cycleEl.value = sub.subscription_billing_cycle || 'monthly';

    const amtEl = document.getElementById('editSubAmount');
    if (amtEl) amtEl.value = parseFloat(sub.subscription_amount || 0).toFixed(2);

    const statusEl = document.getElementById('editSubStatus');
    if (statusEl) statusEl.value = sub.subscription_status || 'trial';

    const startEl = document.getElementById('editSubStartDate');
    if (startEl) startEl.value = sub.subscription_start_date || '';

    const endEl = document.getElementById('editSubEndDate');
    if (endEl) endEl.value = sub.subscription_end_date || '';

    const payEl = document.getElementById('editSubPayMethod');
    if (payEl) payEl.value = sub.subscription_payment_method || '';

    const modalEl = document.getElementById('editSubModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function onEditPlanChange() {
    const planSel = document.getElementById('editSubPlan');
    const cycleSel = document.getElementById('editSubCycle');
    if (!planSel || !cycleSel) return;

    const opt = planSel.options[planSel.selectedIndex];
    if (!opt) return;

    const rate = (cycleSel.value === 'yearly') ? opt.getAttribute('data-yrate') : opt.getAttribute('data-mrate');
    const amtEl = document.getElementById('editSubAmount');
    if (rate && amtEl) {
        amtEl.value = parseFloat(rate).toFixed(2);
    }
}

function onEditCycleChange() {
    onEditPlanChange();
}

function addDaysToEditEnd(days) {
    const endInput = document.getElementById('editSubEndDate');
    if (!endInput) return;

    let current = endInput.value ? new Date(endInput.value) : new Date();
    if (isNaN(current.getTime())) current = new Date();

    current.setDate(current.getDate() + days);
    const yyyy = current.getFullYear();
    const mm = String(current.getMonth() + 1).padStart(2, '0');
    const dd = String(current.getDate()).padStart(2, '0');
    endInput.value = `${yyyy}-${mm}-${dd}`;
}

function onAssignPlanChange() {
    const planSel = document.getElementById('assignPlanId');
    const cycleSel = document.getElementById('assignCycle');
    if (!planSel || !cycleSel) return;

    const opt = planSel.options[planSel.selectedIndex];
    if (!opt) return;

    const rate = (cycleSel.value === 'yearly') ? opt.getAttribute('data-yrate') : opt.getAttribute('data-mrate');
    const amtEl = document.getElementById('assignAmount');
    if (rate && amtEl) {
        amtEl.value = parseFloat(rate).toFixed(2);
    }
}

function onAssignCycleChange() {
    onAssignPlanChange();
    const cycleSel = document.getElementById('assignCycle');
    const endInput = document.getElementById('assignEndDate');
    if (!cycleSel || !endInput) return;

    const days = (cycleSel.value === 'yearly') ? 365 : 30;

    let d = new Date();
    d.setDate(d.getDate() + days);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    endInput.value = `${yyyy}-${mm}-${dd}`;
}

function showEditPlanModal(plan) {
    const form = document.getElementById('editPlanForm');
    if (!form) return;

    form.action = getBaseUrl() + '/subscriptions/plans/update/' + plan.plan_id;

    const setVal = function(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val;
    };

    setVal('planNameInput', plan.plan_name || '');
    setVal('planTaglineInput', plan.plan_tagline || '');
    setVal('planPriceMonthInput', parseFloat(plan.plan_price_per_month || 0).toFixed(2));
    setVal('planPriceYearInput', parseFloat(plan.plan_price_per_year || 0).toFixed(2));
    setVal('planMaxUsersInput', plan.plan_max_users || 0);
    setVal('planMaxCompaniesInput', (plan.plan_max_companies !== undefined && plan.plan_max_companies !== null) ? plan.plan_max_companies : 1);
    setVal('planMaxInvoicesInput', (plan.plan_max_invoices !== undefined && plan.plan_max_invoices !== null) ? plan.plan_max_invoices : 50);
    setVal('planMaxBillsInput', (plan.plan_max_bills !== undefined && plan.plan_max_bills !== null) ? plan.plan_max_bills : 20);
    setVal('planMaxProductsInput', (plan.plan_max_products !== undefined && plan.plan_max_products !== null) ? plan.plan_max_products : 30);
    setVal('planMaxWarehousesInput', (plan.plan_max_warehouses !== undefined && plan.plan_max_warehouses !== null) ? plan.plan_max_warehouses : 1);
    
    const advRep = document.getElementById('planAllowsAdvReportsInput');
    if (advRep) advRep.checked = parseInt(plan.plan_allows_advanced_reports || 0) === 1;

    setVal('planBadgeInput', plan.plan_badge || '');

    const isPop = document.getElementById('planIsPopularInput');
    if (isPop) isPop.checked = parseInt(plan.plan_is_popular || 0) === 1;

    const isAct = document.getElementById('planIsActiveInput');
    if (isAct) isAct.checked = parseInt(plan.plan_is_active || 0) === 1;

    let feats = '';
    if (Array.isArray(plan.feature_list)) {
        feats = plan.feature_list.join("\n");
    }
    setVal('planFeaturesInput', feats);

    const modalEl = document.getElementById('editPlanModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
}

