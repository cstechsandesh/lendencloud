/**
 * LenDen Enterprise Role Permissions Toggle
 */

$(document).ready(function() {
    $('.select-all-mod').on('click', function() {
        let card = $(this).closest('.card');
        let checks = card.find('.perm-check');
        let allChecked = checks.filter(':checked').length === checks.length;
        checks.prop('checked', !allChecked);
    });
});

