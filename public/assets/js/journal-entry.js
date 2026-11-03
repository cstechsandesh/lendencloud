/**
 * LenDen Enterprise Double-Entry Journal Validator & Dynamic Rows
 */

$(document).ready(function() {
    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        $('.line-row').each(function() {
            let d = parseFloat($(this).find('.line-debit').val()) || 0;
            let c = parseFloat($(this).find('.line-credit').val()) || 0;
            totalDebit += d;
            totalCredit += c;
        });

        $('#totalDebitDisplay').text(totalDebit.toFixed(2));
        $('#totalCreditDisplay').text(totalCredit.toFixed(2));

        let diff = Math.abs(totalDebit - totalCredit);
        if (diff < 0.01 && totalDebit > 0) {
            $('#balanceStatusBadge').removeClass('bg-danger').addClass('bg-success').text('Balanced (DR = CR)');
            $('#submitBtn').prop('disabled', false);
        } else {
            $('#balanceStatusBadge').removeClass('bg-success').addClass('bg-danger').text('Mismatch: Difference ₹ ' + diff.toFixed(2));
            $('#submitBtn').prop('disabled', true);
        }
    }

    $(document).on('input', '.line-debit, .line-credit', function() {
        calculateTotals();
    });

    $('#addRowBtn').on('click', function() {
        let newRow = $('.line-row:first').clone();
        newRow.find('input').val('');
        newRow.find('.line-debit, .line-credit').val('0.00');
        newRow.find('.select2-container').remove();
        newRow.find('select').removeClass('select2-hidden-accessible').val('');
        $('#linesTable tbody').append(newRow);
        newRow.find('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        calculateTotals();
    });

    $(document).on('click', '.remove-row', function() {
        if ($('.line-row').length > 2) {
            $(this).closest('tr').remove();
            calculateTotals();
        } else {
            alert('A double-entry voucher requires at least two lines.');
        }
    });

    calculateTotals();
});

