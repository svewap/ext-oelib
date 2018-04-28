<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "oelib".
 *
 * Auto generated 06-01-2015 19:56
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

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
            'php' => '5.6.0-7.0.99',
            'typo3' => '6.2.0-7.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'static_info_tables' => '6.3.7-',
        ],
    ],
    'autoload' => [
        'classmap' => [
            'Classes',
            'Tests',
        ],
    ],
];
