<?php

return [
    'enable' => true,
    'allowed_methods' => [
        // 'GET',
        'HEAD',
        'POST',
        'PUT',
        'DELETE',
        'CONNECT',
        'OPTIONS', 
        'TRACE',
        'PATCH'
    ],

    'secret_fields' => [
        'password',
        'password_confirmation',
    ],

    // Routes that will not log to database.
    // All method to path like: auth/logs/*/edit
    // or specific method to path like: get:auth/logs.
    'except' => [
        'auth/logs*',
    ],
];
