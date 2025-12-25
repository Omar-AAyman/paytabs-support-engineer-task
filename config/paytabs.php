<?php

return [
    'profile_id' => env('PAYTABS_PROFILE_ID', '132344'),
    'server_key' => env('PAYTABS_SERVER_KEY', 'SWJ992BZTN-JHGTJBWDLM-BZJKMR2ZHT'),
    'base_url' => env('pA', 'https://secure-egypt.paytabs.com/payment/request'),
    'currency' => env('PAYTABS_REGION', 'EGP'),
    'callback_base_url' => env('PAYTABS_CALLBACK_URL'),
];
