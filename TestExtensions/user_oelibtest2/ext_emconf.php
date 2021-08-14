<?php

$EM_CONF['user_oelibtest2'] = [
    'title' => 'Second test extension for tx_oelib',
    'description' => 'Second test extension for tx_oelib',
    'version' => '3.0.0',
    'category' => 'example',
    'constraints' => [
        'depends' => [
            'oelib' => '',
            'user_oelibtest' => '',
        ],
    ],
    'state' => 'experimental',
    'author' => 'Niels Pardon',
    'author_email' => 'mail@niels-pardon.de',
];
