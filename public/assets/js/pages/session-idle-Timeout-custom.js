'use strict';
$(document).ready(function() {
    // [ session-idle-timeout ] start
    $.sessionTimeout({
        heading: 'h5',
        title: 'Idle Timeout',
        message: 'Your session is about to expire. Do you want to stay connected?',
        warnAfter: 5000,
        redirAfter: 15000,
        keepAliveUrl: 'ec-session-idle-timeout.php',
        redirUrl: 'ec-session-idle-timeout.php',
        logoutUrl: 'auth-signin.php'
    });
});
