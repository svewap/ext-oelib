<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One is Enough Library',
    'description' => 'Useful stuff for TYPO3 extension development: helper functions for unit testing, templating and automatic configuration checks.',
    'version' => '2.3.2',
    'category' => 'services',
    'constraints' => [
        'depends' => [
            'php' => '5.6.0-7.2.99',
            'typo3' => '7.6.0-8.7.99',
            'extbase' => '7.6.0-8.7.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'static_info_tables' => '6.5.0-',
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
        'classmap' => [
            'Tests',
        ],
    ],
];
