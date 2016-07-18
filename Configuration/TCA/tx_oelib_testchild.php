<?php
defined('TYPO3_MODE') or die('Access denied.');

return [
    'ctrl' => [
        'title' => 'oelib test record',
        'readOnly' => 1,
        'adminOnly' => 1,
        'rootLevel' => 1,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
        'default_sortby' => 'ORDER BY uid',
        'delete' => 'deleted',
        'iconfile' => 'EXT:oelib/Resources/Public/Icons/Test.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'title',
    ],
    'columns' => [
        'title' => [
            'exclude' => 0,
            'label' => 'Title',
            'config' => [
                'type' => 'none',
                'size' => '30',
            ],
        ],
        'parent' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => '',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tx_oelib_parent2' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => '',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title'],
    ],
];
