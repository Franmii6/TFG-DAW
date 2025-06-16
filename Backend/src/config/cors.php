<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'register',
        'login',
        'logout',
        'getUser',
        'customers',
        'customers/*',
        'customer/*',
        'profile',
        'contracts',
        'employees',
        'appointments',
        'services',
        'specialties',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*','http://localhost:8080', 'http://localhost:8001'], //'http://localhost:4200' esta ruta era la que permitíamos usar porque era donde se desplegaba el frontend con angular. Me daba error en el registro y he conseguido solucionarlo añadiendo aquí '*', tras consultarlo con la inteligencia artificial.

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'allowed_credentials' => true,

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
