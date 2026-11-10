<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', null),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'payroll_smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_PAYROLL_HOST', env('MAIL_HOST', 'smtp.hostinger.com')),
            'port' => env('MAIL_PAYROLL_PORT', env('MAIL_PORT', 465)),
            'encryption' => env('MAIL_PAYROLL_ENCRYPTION', env('MAIL_ENCRYPTION', 'ssl')),
            'username' => env('MAIL_PAYROLL_USERNAME', 'payroll@iconicpages.com'),
            'password' => env('MAIL_PAYROLL_PASSWORD', env('MAIL_PASSWORD')),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'billing_smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_BILLING_HOST', env('MAIL_HOST', 'smtp.hostinger.com')),
            'port' => env('MAIL_BILLING_PORT', env('MAIL_PORT', 465)),
            'encryption' => env('MAIL_BILLING_ENCRYPTION', env('MAIL_ENCRYPTION', 'ssl')),
            'username' => env('MAIL_BILLING_USERNAME', 'billing@iconicpages.com'),
            'password' => env('MAIL_BILLING_PASSWORD', env('MAIL_PASSWORD')),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'hr_smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HR_HOST', env('MAIL_HOST', 'smtp.hostinger.com')),
            'port' => env('MAIL_HR_PORT', env('MAIL_PORT', 465)),
            'encryption' => env('MAIL_HR_ENCRYPTION', env('MAIL_ENCRYPTION', 'ssl')),
            'username' => env('MAIL_HR_USERNAME', 'hr@iconicpages.com'),
            'password' => env('MAIL_HR_PASSWORD', env('MAIL_PASSWORD')),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'no-reply@iconicpages.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'ICPG Office')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payroll sender
    |--------------------------------------------------------------------------
    |
    | Payslips go out from a dedicated address so an employee replying to their
    | salary slip reaches payroll rather than the no-reply inbox. The SMTP host
    | still authenticates as MAIL_USERNAME, so this must be an address the
    | provider will accept for that account.
    |
    */

    'payroll_from' => [
        'address' => env('MAIL_PAYROLL_FROM', 'payroll@iconicpages.com'),
        'name' => env('MAIL_PAYROLL_FROM_NAME', 'ICPG Payroll'),
        'reply_to' => env('MAIL_PAYROLL_REPLY_TO', 'payroll@iconicpages.com'),
    ],

    'billing_from' => [
        'address' => env('MAIL_BILLING_FROM', 'billing@iconicpages.com'),
        'name' => env('MAIL_BILLING_FROM_NAME', env('APP_NAME', 'ICPG Technologies Pvt Ltd')),
        'reply_to' => env('MAIL_BILLING_REPLY_TO', 'billing@iconicpages.com'),
    ],

    'hr_from' => [
        'address' => env('MAIL_HR_FROM', 'hr@iconicpages.com'),
        'name' => env('MAIL_HR_FROM_NAME', 'ICPG HR Department'),
        'reply_to' => env('MAIL_HR_REPLY_TO', 'hr@iconicpages.com'),
    ],

];
