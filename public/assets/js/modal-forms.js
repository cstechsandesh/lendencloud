/**
 * LenDen Enterprise Modal Forms Handler (in-place modal editing)
 */

$(document).ready(function() {
    // Generic edit modal population
    $(document).on('click', '.btn-edit-modal', function() {
        const targetSelector = $(this).data('bs-target') || $(this).attr('data-bs-target');
        const modal = $(targetSelector);
        if (!modal.length) return;

        const form = modal.find('form');
        const actionUrl = $(this).data('action') || $(this).attr('data-action');
        if (actionUrl) {
            form.attr('action', actionUrl);
        }

        let item = $(this).data('item');
        if (typeof item === 'string') {
            try {
                item = JSON.parse(item);
            } catch (e) {
                console.error('Failed to parse data-item JSON:', e);
            }
        }

        if (item && typeof item === 'object') {
            $.each(item, function(key, val) {
                const input = form.find(`[name="${key}"]`);
                if (input.length) {
                    if (input.is(':checkbox')) {
                        input.prop('checked', (val == 1 || val === true || val === '1' || val === 'active'));
                    } else if (input.is('select')) {
                        input.val(val).trigger('change');
                    } else if (input.is('textarea')) {
                        input.val(val);
                    } else if (input.attr('type') !== 'password') {
                        input.val(val);
                    }
                }
            });
        }
    });

    // Reset forms on modal close
    $('.modal').on('hidden.bs.modal', function () {
        const form = $(this).find('form');
        if (form.length && !form.hasClass('no-auto-reset')) {
            // Keep CSRF token intact
            const csrfInput = form.find('input[name="csrf_token"], input[name="csrf_test_name"]');
            const csrf = csrfInput.val();
            form[0].reset();
            if (csrfInput.length && csrf) {
                csrfInput.val(csrf);
            }
            form.find('select').trigger('change');
        }
    });
});
