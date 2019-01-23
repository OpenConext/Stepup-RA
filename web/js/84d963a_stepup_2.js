(function ($) {
    'use strict';

    // Enable styling application based on presence of JavaScript.
    $(document.body).addClass('js');

    // Disable forms on submission.
    $(document).on('submit', function () {
        var $form = $(this);

        // Disabling must be deferred until after the submission or the form values won't be included in the request.
        setTimeout(function () {
            $form.find('button, input, textarea, select').prop('disabled', true);
        }, 0);
    });

    $(document).on('change', 'select[name="stepup_switch_locale[locale]"]',
        function (e) {
            $(this).parents('form').first().submit();
        }
    );
}(jQuery));
