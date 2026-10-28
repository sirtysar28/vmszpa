<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Layanan VMS ZPA
    |--------------------------------------------------------------------------
    */

    // Prefix Kode Tamu, mis. ZPA-8F3K2A
    'guest_code' => [
        'prefix' => env('GUEST_CODE_PREFIX', 'ZPA'),
    ],

    // Captcha login management
    'captcha' => [
        'length' => env('CAPTCHA_LENGTH', 5),
    ],

    'company' => [
        'name' => 'PT. Zinkpower Austridno',
        'short' => 'ZPA',
        'app' => 'Visitor Management System',
        'domain' => 'vmszpa.com',
    ],
];
