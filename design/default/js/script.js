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
    $('.container').removeClass('loading');

    $('#data').autoHeight();

    $('[rel="tooltip"]').tooltip();

    $('#toggle-form').click(function(e) {
        e.preventDefault();

        let edit = $('#add-form');

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

    $('[data-toggle=log]').click(function(e) {
        e.preventDefault();

        let $this = $(this),
            group = $this.closest('.group'),
            log = group.find('.log');

        if (log.hasClass('d-none')) {
            // When open a log, hide the auction edit form
            if (!$('#add-form').hasClass('d-none')) {
                $('#toggle-form').trigger('click');
            }
            // Hide all logs
            $('.log').addClass('d-none');
            $('.group').removeClass('expanded');
            // Show only requested log
            group.addClass('expanded');
            log.removeClass('d-none')
                // Scroll log to the end
                .find('.pre')
                .scrollTop(9999)
                .focus();
        } else {
            // Close requested log
            group.removeClass('expanded');
            log.addClass('d-none');
        }
    });

    $('[data-api=edit]').click(function(e) {
        e.preventDefault();

        let form = $('#add-form');

        // Hide all logs
        $('.log').addClass('d-none');
        $('.group').removeClass('expanded');
        // Prepare form
        form.addClass('loading').removeClass('d-none');
        // Switch plus/minus signs
        $('#toggle-form')
            .removeClass('fa-plus-circle')
            .addClass('fa-minus-circle');

        $(window).scrollTop(0);

        // Fetch auction group data
        $.getJSON('/', { api: 'edit', token: $(this).data('token') }, function(
            data
        ) {
            $('#name, #name_old').prop('value', data.name);
            $('#data')
                .html(data.data)
                .trigger('input')
                .focus();
        }).always(function() {
            form.removeClass('loading');
        });
    });

    $('[data-api=stop]').click(function(e) {
        e.preventDefault();

        let $this = $(this),
            token = $this.data('token'),
            group = $this.closest('.group').addClass('loading');

        $.getJSON('/', { api: 'stop', token: token }, function(data) {
            if (data) {
                group.removeClass('table-success').addClass('table-secondary');
                // Switch buttons
                group.find('.btn-stop').addClass('d-none');
                group.find('.btn-delete').removeClass('d-none');
                // Set new log
                group.find('.pre').html(data);
                // Hide all logs
                $('.log').addClass('d-none');
                $('.group').removeClass('expanded');
                // Show new log
                group.find('[data-toggle=log]').trigger('click');
            }
        }).always(function() {
            group.removeClass('loading');
        });
    });

    $('[data-api=delete]').click(function(e) {
        e.preventDefault();

        let $this = $(this).blur(),
            token = $this.data('token'),
            group = $this.closest('.group').addClass('loading');

        $.getJSON('/', { api: 'delete', token: token }, function(data) {
            if (data) {
                group.fadeToggle({
                    complete: function() {
                        group.remove();
                    }
                });
                // Clear edit form as needed
                $('#add-form').addClass('d-none');
                $('#name, #name_old').prop('value', '');
                $('#data').html('');
                $('#toggle-form')
                    .removeClass('fa-minus-circle')
                    .addClass('fa-plus-circle');
            }
        }).always(function() {
            group.removeClass('loading');
        });
    });

    $('[data-api=log]').click(function(e) {
        e.preventDefault();

        let $this = $(this),
            token = $this.data('token'),
            log = $this.parent().find('.pre');

        $this
            .blur()
            .prop('disabled', true)
            .find('.fa')
            .addClass('fa-spin');

        $.getJSON('/', { api: 'log', token: token }, function(data) {
            data && log.html(data).scrollTop(9999);
        }).always(function() {
            $this
                .prop('disabled', false)
                .find('.fa')
                .removeClass('fa-spin');
        });
    });

    $('#example').click(function(e) {
        e.preventDefault();

        $.get('/app/example.txt', function(data) {
            let el = $('#data'),
                text = el.text() + '\n';
            el.text((text + data).trim())
                .trigger('input')
                .focus();
        });
    });

    // https://stackoverflow.com/a/31564270
    $(window).on('beforeunload', function() {
        $('.container').addClass('loading');
        $('*').css('cursor', 'progress');
    });

    // Remove only success alert
    setTimeout(function() {
        $('.alert-success')
            .closest('.group')
            .fadeTo(500, 0)
            .slideUp(500, function() {
                $(this).remove();
            });
    }, 5000);
});
