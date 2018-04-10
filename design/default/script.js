/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
$(function() {

    $('[rel="tooltip"]').tooltip();

    if (!$('#add-form').hasClass('d-none')) {
        // Edit auction group mode
        $('#toggle-form').removeClass('fa-plus-circle').addClass('fa-minus-circle');
    }

    $('#toggle-form').on('click', function(e) {
        var edit = $('#add-form');

        // Switch visibility of auction edit form
        edit.toggleClass('d-none');

        if (!edit.hasClass('d-none')) {
            // Edit form visible
            $('#name').focus();
            $(this).removeClass('fa-plus-circle').addClass('fa-minus-circle');
        } else {
            // Edit form invisible
            $(this).removeClass('fa-minus-circle').addClass('fa-plus-circle');
        }

        e.preventDefault();
    });

    $('[data-toggle=log]').on('click', function(e) {
        var el = $('#log-'+$(this).data('hash'));

        if (el.hasClass('d-none')) {
            // When open a log, hide the auction edit form
            if (!$('#add-form').hasClass('d-none')) {
                $('#toggle-form').trigger('click');
            }
            // Hide all logs
            $('.log').addClass('d-none');
            // Show only requested log
            el.removeClass('d-none')
            // Scroll log to the end
            $('pre', el).scrollTop(el.prop('scrollHeight'));
        } else {
            // Close requested log
            el.addClass('d-none');
        }

        e.preventDefault();
    });

    $('#example').on('click', function(e) {
        $.get(
            '/example.txt',
            function(data) {
                var el = $('#data'), text = el.text() + '\n';
                el.text((text + data).trim())
                  // Max. height 500px
                  .css('height', Math.min(500, el.prop('scrollHeight')))
                  .focus();
            }
        );

        e.preventDefault();
    });

    // https://stackoverflow.com/a/31564270
    $(window).on('beforeunload', function(){
        $('*').css('cursor', 'progress');
    });
})
