jQuery(document).ready(function () {
    "use strict";

    var $logs = jQuery('#todebug-execution-logs');

    // Show/hide logs
    jQuery('#wp-admin-bar-todebug a').on('click', function (event) {
        event.preventDefault();
        $logs.toggle();
        jQuery(this).blur();
    });

    // Close logs by click
    $logs.on('click', function (event) {
        $logs.toggle();
    });

    // Disable event propagation on .inner-wrapper to allow to highlight and
    // copy the text
    $logs.children('.inner-wrapper').on('click', function (event) {
        event.stopPropagation();
    });
});
