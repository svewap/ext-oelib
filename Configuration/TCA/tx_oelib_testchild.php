<?php
defined('TYPO3_MODE') or die('Access denied.');

return [
    'ctrl' => [
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
        'delete' => 'deleted',
    ],
    'interface' => [
        'showRecordFieldList' => 'title',
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
    ],
    'types' => [
        '0' => ['showitem' => ''],
    ],
];
