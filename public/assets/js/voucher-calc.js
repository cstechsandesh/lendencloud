/**
 * LenDen Enterprise Voucher Allocation Dynamic Loading
 */

$(document).ready(function() {
    const base = $('meta[name="base-url"]').attr('content') || '';
    const currency = $('meta[name="currency-symbol"]').attr('content') || '₹';

    // Receipt: Load Customer Invoices
    $('#receiptCustomerSelect').on('change', function() {
        let custId = $(this).val();
        let tbody = $('#invoicesAllocTable tbody');
        tbody.html('<tr><td colspan="5" class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div> Loading pending invoices...</td></tr>');

        if (!custId) {
            tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">Select a customer above.</td></tr>');
            return;
        }

        let url = (base ? base + '/' : '/') + 'ajax/party-invoices/' + custId;
        $.getJSON(url, function(data) {
            tbody.empty();
            if (data.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">No pending invoices found for this customer.</td></tr>');
                return;
            }

            $.each(data, function(idx, inv) {
                let row = `<tr>
                    <td><strong>${inv.invoice_number}</strong><input type="hidden" name="allocated_invoice_id[]" value="${inv.invoice_id}"></td>
                    <td>${inv.invoice_date}</td>
                    <td class="text-end">${currency} ${parseFloat(inv.invoice_total).toFixed(2)}</td>
                    <td class="text-end text-danger fw-bold">${currency} ${parseFloat(inv.invoice_balance).toFixed(2)}</td>
                    <td><input type="number" step="0.01" name="allocated_amount[]" class="form-control form-control-sm text-end alloc-input" placeholder="0.00" max="${inv.invoice_balance}"></td>
                </tr>`;
                tbody.append(row);
            });
        });
    });

    // Payment: Load Vendor Bills
    $('#paymentVendorSelect').on('change', function() {
        let vendId = $(this).val();
        let tbody = $('#billsAllocTable tbody');
        tbody.html('<tr><td colspan="5" class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div> Loading pending bills...</td></tr>');

        if (!vendId) {
            tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">Select a vendor above.</td></tr>');
            return;
        }

        let url = (base ? base + '/' : '/') + 'ajax/party-bills/' + vendId;
        $.getJSON(url, function(data) {
            tbody.empty();
            if (data.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">No pending bills found for this vendor.</td></tr>');
                return;
            }

            $.each(data, function(idx, b) {
                let row = `<tr>
                    <td><strong>${b.bill_number}</strong><input type="hidden" name="allocated_bill_id[]" value="${b.bill_id}"></td>
                    <td>${b.bill_date}</td>
                    <td class="text-end">${currency} ${parseFloat(b.bill_total).toFixed(2)}</td>
                    <td class="text-end text-danger fw-bold">${currency} ${parseFloat(b.bill_balance).toFixed(2)}</td>
                    <td><input type="number" step="0.01" name="allocated_amount[]" class="form-control form-control-sm text-end" placeholder="0.00" max="${b.bill_balance}"></td>
                </tr>`;
                tbody.append(row);
            });
        });
    });
});

