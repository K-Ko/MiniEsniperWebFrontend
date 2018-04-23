/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
/**
 * Textarea with auto-resize
 * https://stackoverflow.com/a/25621277
 */
jQuery.fn.extend({
    autoHeight: function() {
        function autoHeight_(element) {
            return jQuery(element)
                .css({ height: 'auto', 'overflow-y': 'hidden' })
                .height(element.scrollHeight);
        }
        return this.each(function() {
            autoHeight_(this).on('input', function() {
                autoHeight_(this);
            });
        });
    }
});

/**
 * On load
 */
$(function() {
    $('[rel="tooltip"]').tooltip();
    $('#data').autoHeight();

    $('#toggle-form').on('click', function(e) {
        e.preventDefault();

        var edit = $('#add-form');

        // Hide all logs
        $('.log').addClass('d-none');

        // Switch visibility of auction edit form
        edit.toggleClass('d-none');

        if (!edit.hasClass('d-none')) {
            // Edit form visible
            $('#name').focus();
        }

        $(this)
            .toggleClass('fa-plus-circle')
            .toggleClass('fa-minus-circle');
    });

    $('[data-toggle=log]').on('click', function(e) {
        e.preventDefault();

        var el = $('#log-' + $(this).data('token'));

        if (el.hasClass('d-none')) {
            // When open a log, hide the auction edit form
            if (!$('#add-form').hasClass('d-none')) {
                $('#toggle-form').trigger('click');
            }
            // Hide all logs
            $('.log').addClass('d-none');
            // Show only requested log
            el.removeClass('d-none');
            // Scroll log to the end
            $('.pre', el)
                .scrollTop(9999)
                .focus();
        } else {
            // Close requested log
            el.addClass('d-none');
        }
    });

    $('[data-api=edit]').on('click', function(e) {
        e.preventDefault();

        var el_form = $('#add-form'),
            el_name = $('#name'),
            el_data = $('#data');

        // Hide all logs
        $('.log').addClass('d-none');
        // Prepare form
        el_form.addClass('loading').removeClass('d-none');
        // Switch plus/minus signs
        $('#toggle-form')
            .removeClass('fa-plus-circle')
            .addClass('fa-minus-circle');

        // Fetch auction group data
        $.getJSON('/', { api: 'edit', token: $(this).data('token') }, function(
            data
        ) {
            el_name.prop('value', data.name);
            el_data
                .text(data.data)
                .trigger('input')
                .focus();
        }).always(function() {
            el_form.removeClass('loading');
        });
    });

    $('[data-api=stop]').on('click', function(e) {
        e.preventDefault();

        var $this = $(this),
            token = $this.data('token');

        $.getJSON('/', { api: 'stop', token: token }, function(data) {
            if (data) {
                var row = $this.closest('.row');
                row.removeClass('table-success').addClass('table-secondary');
                // Switch buttons
                $('.btn-stop', row).addClass('d-none');
                $('.btn-delete', row).removeClass('d-none');
                // Set new log
                $('#log-' + token + ' div div').html(data);
                // Hide all logs
                $('.log').addClass('d-none');
                // Show new log
                $('[data-toggle=log]', row).trigger('click');
            }
        });
    });

    $('[data-api=delete]').on('click', function(e) {
        e.preventDefault();

        var $this = $(this),
            token = $this.data('token');

        $.getJSON('/', { api: 'delete', token: token }, function(data) {
            if (data) {
                $this.closest('.row').remove();
                $('#log-' + token).remove();
            }
        });
    });

    $('#example').on('click', function(e) {
        e.preventDefault();

        $.get('/app/example.txt', function(data) {
            var el = $('#data'),
                text = el.text() + '\n';
            el
                .text((text + data).trim())
                .trigger('input')
                .focus();
        });
    });

    // https://stackoverflow.com/a/31564270
    $(window).on('beforeunload', function() {
        $('*').css('cursor', 'progress');
    });

    // Remove only success alert
    setTimeout(function() {
        $('.alert-success')
            .closest('.row')
            .fadeTo(500, 0)
            .slideUp(500, function() {
                $(this).remove();
            });
    }, 5000);
});
