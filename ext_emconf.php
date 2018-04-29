<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One is Enough Library',
    'description' => 'Useful stuff for TYPO3 extension development: helper functions for unit testing, templating and automatic configuration checks.',
    'category' => 'services',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'shy' => 0,
    'dependencies' => 'static_info_tables',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 1,
    'createDirs' => '',
    'modify_tables' => 'be_users,be_groups,fe_groups,fe_users,pages,sys_template,tt_content',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'author_company' => 'oliverklee.de',
    'version' => '1.4.0',
    '_md5_values_when_last_written' => '',
    'constraints' => [
        'depends' => [
            'php' => '5.6.0-7.2.99',
            'typo3' => '7.6.0-8.7.99',
            'emogrifier' => '2.0.0-2.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'static_info_tables' => '6.4.0-',
        ],
    ],
    'autoload' => [
        'classmap' => [
            'Classes',
            'Tests',
        ],
    ],
];
