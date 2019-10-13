<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Second test extension for tx_oelib',
    'description' => 'Second test extension for tx_oelib',
    'version' => '2.3.3',
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
