<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One is Enough Library',
    'description' => 'Useful stuff for TYPO3 extension development: helper functions for unit testing, templating and automatic configuration checks.',
    'version' => '3.0.3',
    'category' => 'services',
    'constraints' => [
        'depends' => [
            'php' => '7.0.0-7.4.99',
            'typo3' => '8.7.0-9.5.99',
            'extbase' => '8.7.0-9.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'static_info_tables' => '6.7.1-',
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => true,
    'createDirs' => '',
    'clearCacheOnLoad' => false,
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'classmap' => [
            'Classes',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'OliverKlee\\Oelib\\Tests\\' => 'Tests/'
        ],
    ],
];
