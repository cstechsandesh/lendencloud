/**
 * LenDen Enterprise Application Core JavaScript
 */

// Global jQuery setup for CSRF Token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    // Strip .table-responsive wrapping DataTables so horizontal scrollbars
    // never overlay the bottom pagination & info controls
    $('table.datatable, table.dataTable').each(function() {
        var $parent = $(this).parent();
        if ($parent.hasClass('table-responsive')) {
            $(this).unwrap();
        }
    });

    // Initialize DataTables
    if ($('.datatable').length) {
        $('.datatable').DataTable({
            responsive: false,
            autoWidth: false,
            pageLength: 25,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                lengthMenu: "Show _MENU_ entries"
            }
        });
    }

    // Initialize Select2 with Bootstrap 5 Theme
    if ($('.select2').length) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }

    // Sidebar Toggle (Mobile & Desktop)
    $('#sidebarToggle').on('click', function(e) {
        e.preventDefault();
        if ($(window).width() >= 992) {
            $('body').toggleClass('sidebar-collapsed');
            localStorage.setItem('lenden_sidebar_collapsed', $('body').hasClass('sidebar-collapsed') ? '1' : '0');
        } else {
            $('#appSidebar').toggleClass('show');
            $('#sidebarBackdrop').toggleClass('show');
        }
    });

    // Restore desktop sidebar preference
    if ($(window).width() >= 992 && localStorage.getItem('lenden_sidebar_collapsed') === '1') {
        $('body').addClass('sidebar-collapsed');
    }

    $('#sidebarBackdrop').on('click', function() {
        $('#appSidebar').removeClass('show');
        $('#sidebarBackdrop').removeClass('show');
    });

    // Auto dismiss alerts after 4 seconds
    setTimeout(function() {
        $('.alert').fadeOut(400, function() {
            $(this).remove();
        });
    }, 4000);
});

