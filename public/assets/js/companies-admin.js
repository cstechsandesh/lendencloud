/**
 * LenDen Enterprise - Companies Administration Interactive Scripts
 */

function getBaseUrl() {
    const metaBase = document.querySelector('meta[name="base-url"]');
    return metaBase ? metaBase.getAttribute('content').replace(/\/+$/, '') : '';
}

function openAddCompanyModal() {
    const modalEl = document.getElementById('addCompanyModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function editCompany(companyId) {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/companies/view/' + companyId)
        .then(response => response.json())
        .then(data => {
            if (data.company) {
                const c = data.company;
                const form = document.getElementById('editCompanyForm');
                if (form) form.action = baseUrl + '/companies/update/' + c.company_id;

                const setVal = function(id, val) {
                    const el = document.getElementById(id);
                    if (el) el.value = val;
                };

                setVal('edit_company_name', c.company_name || '');
                setVal('edit_company_code', c.company_code || '');
                setVal('edit_company_gstin', c.company_gstin || '');
                setVal('edit_company_pan', c.company_pan || '');
                setVal('edit_company_status', c.company_status || 'active');
                setVal('edit_company_email', c.company_email || '');
                setVal('edit_company_phone', c.company_phone || '');
                setVal('edit_company_address', c.company_address || '');
                setVal('edit_company_city', c.company_city || '');
                setVal('edit_company_state', c.company_state || '');
                setVal('edit_company_postal_code', c.company_postal_code || '');
                setVal('edit_company_bank_name', c.company_bank_name || '');
                setVal('edit_company_account_no', c.company_account_no || '');
                setVal('edit_company_ifsc_code', c.company_ifsc_code || '');
                setVal('edit_company_upi_id', c.company_upi_id || '');
                setVal('edit_company_currency_symbol', c.company_currency_symbol || '₹');

                const modalEl = document.getElementById('editCompanyModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.show();
                }
            }
        })
        .catch(() => {
            alert('Failed to load company details.');
        });
}

function viewCompanyDetails(companyId) {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/companies/view/' + companyId)
        .then(response => response.json())
        .then(data => {
            if (data.company) {
                const c = data.company;
                const s = data.stats || {};
                
                const setText = function(id, val) {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val;
                };

                setText('view_company_name', c.company_name || '');
                setText('view_company_code', c.company_code || 'ID: ' + c.company_id);
                setText('view_company_status', (c.company_status || 'active').toUpperCase());
                setText('view_company_gstin', c.company_gstin || 'None');
                setText('view_company_pan', c.company_pan || 'None');
                setText('view_company_city_state', (c.company_city || '') + (c.company_state ? ', ' + c.company_state : ''));
                setText('view_company_email', c.company_email || 'None');
                setText('view_company_phone', c.company_phone || 'None');
                setText('view_company_bank', (c.company_bank_name || 'None') + (c.company_account_no ? ' (' + c.company_account_no + ')' : ''));
                setText('view_company_ifsc_upi', (c.company_ifsc_code || '') + (c.company_upi_id ? ' / ' + c.company_upi_id : ''));

                setText('view_stat_accounts', s.accounts || 0);
                setText('view_stat_products', s.products || 0);
                setText('view_stat_invoices', s.invoices || 0);

                const switchBtn = document.getElementById('modalSwitchBtn');
                if (switchBtn) {
                    switchBtn.href = baseUrl + '/companies/switch/' + c.company_id;
                }

                const modalEl = document.getElementById('viewCompanyModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.show();
                }
            }
        })
        .catch(() => {
            alert('Failed to load company details.');
        });
}

