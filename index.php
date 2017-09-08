<?php

return [

    'name' => 'bixie/emailreader',

    'type' => 'extension',

    'main' => 'Bixie\\Emailreader\\EmailreaderModule',

    'autoload' => [
        'Bixie\\Emailreader\\' => 'src'
    ],

    'nodes' => [],

    'routes' => [
        '/emailreader' => [
            'name' => '@emailreader',
            'controller' => [
                'Bixie\\Emailreader\\Controller\\EmailreaderController',
            ]
        ],
        '/api/emailreader' => [
            'name' => '@emailreader/api',
            'controller' => [
                'Bixie\\Emailreader\\Controller\\EmailreaderApiController',
            ]
        ]
    ],

    'resources' => [
        'bixie/emailreader:' => ''
    ],

    'config' => [
        'attachment_path' => '../email_attachments',
        'log_path' => '../logs/emailreader',
        'server' => [
            'host' => '',
            'email' => '',
            'password' => ''
        ],
        'mailboxes' => [
            'processed' => '',
            'unprocessed' => '',
        ],
    ],

    'menu' => [
        'emailreader' => [
            'label' => 'Email Reader',
            'icon' => 'packages/bixie/emailreader/icon.svg',
            'url' => '@emailreader/index',
            'access' => 'emailreader: use emailreader',
            'active' => '@emailreader(/*)'
        ],
        'emailreader: index' => [
            'label' => 'Email Reader',
            'parent' => 'emailreader',
            'url' => '@emailreader/index',
            'access' => 'emailreader: use emailreader',
            'active' => '@emailreader/index'
        ],
        'emailreader: settings' => [
            'label' => 'Settings',
            'parent' => 'emailreader',
            'url' => '@emailreader/settings',
            'access' => 'system: access settings',
            'active' => '@emailreader/settings'
        ]
    ],

    'permissions' => [
        'emailreader: use emailreader' => [
            'title' => 'Use emailreader'
        ],
        'emailreader: access mailbox' => [
            'title' => 'Access mailbox'
        ]
    ],

    'settings' => '@emailreader/settings',

    'events' => [
        'console.init' => function ($event, $console) {

            $console->add(new \Bixie\Emailreader\Console\Commands\ProcessCommand());

        }
    ]
];
