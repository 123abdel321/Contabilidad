<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],
    'eco' => [
        'email_token' => env('ECO_EMAIL_TOKEN'),
    ],
    'wompi' => [
        'public_key'       => env('WOMPI_PUBLIC_KEY'),
        'private_key'      => env('WOMPI_PRIVATE_KEY'),
        'events_secret'    => env('WOMPI_EVENTS_SECRET'),
        'integrity_secret' => env('WOMPI_INTEGRITY_SECRET'),
        'sandbox'          => env('WOMPI_SANDBOX', true),
        'endpoint' => env('WOMPI_SANDBOX', true)
                ? 'https://sandbox.wompi.co/v1'
                : 'https://production.wompi.co/v1',
    ],
    'suscripcion_venta' => [
        'token_db'      => env('SUSCRIPCION_VENTA_TOKEN_DB'),
        'id_bodega'     => env('SUSCRIPCION_VENTA_ID_BODEGA'),
        'id_forma_pago' => env('SUSCRIPCION_VENTA_ID_FORMA_PAGO'),
        'id_resolucion' => env('SUSCRIPCION_VENTA_ID_RESOLUCION'),
        'id_vendedor'   => env('SUSCRIPCION_VENTA_ID_VENDEDOR'),
        'id_familia'    => env('SUSCRIPCION_VENTA_ID_FAMILIA'),
        'id_usuario'    => env('SUSCRIPCION_VENTA_ID_USUARIO'),
    ],
    'recaptcha' => [
        'site_key'    => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY')
    ]

];
