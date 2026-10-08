<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Invoice Company / Brand
    |--------------------------------------------------------------------------
    */
    'company_name' => env('INVOICE_COMPANY_NAME', 'ICPG Technologies Pvt. Ltd.'),
    'brand_name' => env('INVOICE_BRAND_NAME', 'ICONICPAGES'),
    'tagline' => env('INVOICE_TAGLINE', 'Make Your Brand Iconic'),

    /*
    |--------------------------------------------------------------------------
    | Invoice Contact
    |--------------------------------------------------------------------------
    */
    'website' => env('INVOICE_WEBSITE', 'www.iconicpages.com'),
    'support_email' => env('INVOICE_SUPPORT_EMAIL', 'support@iconicpages.com'),
    'support_phone' => env('INVOICE_SUPPORT_PHONE', '+91 8887760009'),

    /*
    |--------------------------------------------------------------------------
    | Payment / Bank Details
    |--------------------------------------------------------------------------
    */
    'bank_name' => env('INVOICE_BANK_NAME', 'Kotak Mahindra Bank'),
    'account_type' => env('INVOICE_ACCOUNT_TYPE', 'Current Account'),
    'account_name' => env('INVOICE_ACCOUNT_NAME', 'ICPG TECHNOLOGIES PRIVATE LIMITED'),
    'account_number' => env('INVOICE_ACCOUNT_NUMBER', '50111162407'),
    'ifsc_code' => env('INVOICE_IFSC_CODE', 'KKBK0000121'),
    'upi_id' => env('INVOICE_UPI_ID', '7866210351@kotak'),

    /*
    |--------------------------------------------------------------------------
    | QR Code for Payment
    | Path relative to public/ or full URL (e.g. images/upi-qr.png)
    |--------------------------------------------------------------------------
    */
    'qr_image' => env('INVOICE_QR_IMAGE', ''),

];
