jQuery(document).ready(function () {
    "use strict";

    var $logsWrapper = jQuery('#todebug-logs');

    // Show/hide logs
    jQuery('#wp-admin-bar-todebug a').on('click', function (event) {
        event.preventDefault();
        $logsWrapper.toggle();
        jQuery(this).blur();
    });

    // Close logs by clicking on the background
    $logsWrapper.on('click', function (event) {
        $logsWrapper.toggle();
    });

    // Disable event propagation on .inner-wrapper to allow highlighting and
    // copying the text
    $logsWrapper.children('.inner-wrapper').on('click', function (event) {
        event.stopPropagation();
    });
});
