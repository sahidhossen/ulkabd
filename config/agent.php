<?php

return [
    'facebook_protocols' => [
        'permissions' => [
            'email',
            'manage_pages',
            'publish_pages',
            'pages_messaging',
            'pages_messaging_subscriptions'
        ],
        'image_size' => [
            'width'     => 574,
            'height'    => 300
        ],
        'title_min_length'  => 1,
        'title_max_length'  => 80,
        'desc_min_length'   => 1,
        'desc_max_length'   => 80,
        'max_cards'         => 9,
        'max_list'          => 4,
        'text_limit'        => 640,
        'get_started_payload' => 'Get Started',
        'app_id'                => "254319315907703",
        'app_secret'            => "ec5401b4c71714b89444e5c78e460063",
        'v_api'                 => "v11.0",
        'payload_product_detail'=> "PLOAD_ED-"
    ],

    'flag' => [
        'ok'            => 0, // check
        'created'       => 1, // check
        'read'          => 2,
        'updated'       => 3, // check
        'uncategorized' => 4, // check in products
        'deleted'       => 5, // check
        'uneditable'    => 6,
        'default'       => 7
    ],

    'training' => [
        'needed'    => 0,
        'running'   => 1,
        'done'      => 2
    ],

    'delivery_state' => [
        'new'       => 0,
        'confirmed' => 4,
        'sent'      => 2,
        'delivered' => 1,
        'cancelled' => 3
    ],

    'payment'  => [
        'due'       => 0,
        'paid'      => 1,
        'pending'   => 2
    ],

    'ideal_redis_data_expiration_time' => 300,

    // 'base_url' => 'https://usha.ulkabd.com'
    'base_url' => 'http://x-project.loc/'
];
