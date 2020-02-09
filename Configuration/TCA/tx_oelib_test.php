<?php

defined('TYPO3_MODE') or die('Access denied.');

return [
    'ctrl' => [
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'hideTable' => true,
        'adminOnly' => true,
    ],
    'interface' => [
        'showRecordFieldList' => '',
    ],
    'columns' => [
        'hidden' => [
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'starttime' => [
            'config' => [
                'type' => 'none',
                'renderType' => 'inputDateTime',
                'eval' => 'date',
            ],
        ],
        'endtime' => [
            'config' => [
                'type' => 'none',
                'eval' => 'date',
                'renderType' => 'inputDateTime',
            ],
        ],
        'title' => [
            'config' => [
                'type' => 'none',
            ],
        ],
        'friend' => [
            'l10n_mode' => 'exclude',
            'label' => 'Friend (n:1 relation within the same table):',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_oelib_test',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'owner' => [
            'l10n_mode' => 'exclude',
            'label' => 'Owner (n:1 relation to another table):',
            'config' => [
                'type' => 'group',
                'foreign_table' => 'fe_users',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'children' => [
            'l10n_mode' => 'exclude',
            'label' => 'Children (m:n relation using a comma-separated list)',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'tx_oelib_test',
                'size' => 4,
                'minitems' => 0,
            ],
        ],
        'related_records' => [
            'l10n_mode' => 'exclude',
            'label' => 'Related records (m:n relation using an m:n table)',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'tx_oelib_test',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
                'MM' => 'tx_oelib_test_article_mm',
            ],
        ],
        'composition' => [
            'l10n_mode' => 'exclude',
            'label' => 'Related records (1:n relation using a foreign field)',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_oelib_testchild',
                'foreign_field' => 'parent',
                'foreign_sortby' => 'title',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
            ],
        ],
        'composition2' => [
            'l10n_mode' => 'exclude',
            'label' => 'Related records (1:n relation using a foreign field with prefix)',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_oelib_testchild',
                'foreign_field' => 'tx_oelib_parent2',
                'foreign_default_sortby' => 'title',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
            ],
        ],
        'composition_without_sorting' => [
            'l10n_mode' => 'exclude',
            'label' => 'Related records (1:n relation using a foreign field without sorting)',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_oelib_testchild',
                'foreign_field' => 'tx_oelib_parent3',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
            ],
        ],
        'bidirectional' => [
            'l10n_mode' => 'exclude',
            'label' => 'Related records (m:n relation using an m:n table)',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'tx_oelib_test',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
                'MM' => 'tx_oelib_test_article_mm',
                'MM_opposite_field' => 'related_records',
            ],
        ],
        'header' => [
            'config' => [
                'type' => 'none',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => ''],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];
