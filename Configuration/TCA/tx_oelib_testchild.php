<?php

defined('TYPO3') or die('Access denied.');

return [
    'ctrl' => [
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
        'delete' => 'deleted',
        'hideTable' => true,
        'adminOnly' => true,
    ],
    'columns' => [
        'title' => [
            'config' => [
                'type' => 'none',
            ],
        ],
        'parent' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tx_oelib_parent2' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tx_oelib_parent3' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => ''],
    ],
];
