<?php
return [
    'backend' => [
        'frontName' => 'admin_13mq1w'
    ],
    'crypt' => [
        'key' => '2b7828283b29f9c782e81a6a96afefe4'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'tjuk4home_mage',
                'username' => 'tjuk4home_mage@t',
                'password' => 'M+aqG8S7RmyW}FPA',
                'active' => '1'
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'developer',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => 'd0d_'
            ],
            'page_cache' => [
                'id_prefix' => 'd0d_'
            ]
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => null
        ]
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'google_product' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'downloadable_domains' => [
        'beta.theb2bstation.com'
    ],
    'install' => [
        'date' => 'Thu, 05 Dec 2019 07:09:44 +0000'
    ]
];
