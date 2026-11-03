/**
 * LenDen Enterprise Order & Invoice Dynamic Calculation
 */

$(document).ready(function() {
    const isPurchase = window.location.pathname.includes('/purchases/') || $('form').data('type') === 'purchase';
    const currency = $('meta[name="currency-symbol"]').attr('content') || '₹';

    function recalcDocument() {
        let subtotal = 0;
        let totalTax = 0;

        $('.item-row').each(function() {
            let qty = parseFloat($(this).find('.row-qty').val()) || 0;
            let price = parseFloat($(this).find('.row-price').val()) || 0;
            let disc = parseFloat($(this).find('.row-disc').val()) || 0;
            let taxRate = parseFloat($(this).find('.row-tax option:selected').data('rate')) || 0;

            let base = qty * price;
            let discAmt = base * (disc / 100);
            let taxable = base - discAmt;
            let lineTax = taxable * (taxRate / 100);
            let total = taxable + lineTax;

            $(this).find('.row-total').text(total.toFixed(2));
            subtotal += taxable;
            totalTax += lineTax;
        });

        let grand = subtotal + totalTax;
        $('#dispSubtotal').text(currency + ' ' + subtotal.toFixed(2));
        $('#dispTax').text(currency + ' ' + totalTax.toFixed(2));
        $('#dispGrandTotal').text(currency + ' ' + grand.toFixed(2));
    }

    $(document).on('change', '.select-product', function() {
        let row = $(this).closest('tr');
        let pId = $(this).val();
        if (pId) {
            let base = $('meta[name="base-url"]').attr('content') || '';
            let url = (base ? base + '/' : '/') + 'ajax/product/' + pId;
            $.getJSON(url, function(res) {
                let unitPrice = isPurchase ? res.purchase_price : res.selling_price;
                row.find('.row-price').val(parseFloat(unitPrice).toFixed(2));
                if (res.tax_rate_id) {
                    row.find('.row-tax').val(res.tax_rate_id);
                }
                recalcDocument();
            });
        }
    });

    $(document).on('input change', '.row-qty, .row-price, .row-disc, .row-tax', function() {
        recalcDocument();
    });

    $('#addInvRowBtn, #addSoRowBtn, #addBillRowBtn, #addPoRowBtn, #addOrderRowBtn, .add-item-row-btn').on('click', function() {
        let tbody = $('.item-row:first').closest('tbody');
        let newRow = $('.item-row:first').clone();
        newRow.find('input').val('');
        newRow.find('.row-qty').val('1.000');
        newRow.find('.row-price').val('0.00');
        newRow.find('.row-disc').val('0.00');
        newRow.find('.row-total').text('0.00');
        newRow.find('.select2-container').remove();
        newRow.find('select').removeClass('select2-hidden-accessible').val('');
        tbody.append(newRow);
        newRow.find('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        recalcDocument();
    });

    $(document).on('click', '.remove-item-row', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            recalcDocument();
        } else {
            alert('At least one item line is required.');
        }
    });

    recalcDocument();
});
