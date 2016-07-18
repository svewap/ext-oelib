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
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:oelib/Resources/Public/Icons/Test.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,starttime,endtime,title,friend,owner,children,related_records,composition',
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
            'config' => [
                'type' => 'none',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'default' => '0',
                'checkbox' => '0',
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
            'config' => [
                'type' => 'none',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0',
                'range' => [
                    'upper' => mktime(0, 0, 0, 12, 31, 2020),
                    'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')),
                ],
            ],
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'Title',
            'config' => [
                'type' => 'none',
                'size' => '30',
            ],
        ],
        'friend' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
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
            'exclude' => 1,
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
            'exclude' => 1,
            'label' => 'Children (m:n relation using a comma-separated list)',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'tx_oelib_test',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
            ],
        ],
        'related_records' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
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
            'exclude' => 1,
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
            'exclude' => 1,
            'label' => 'Related records (1:n relation using a foreign field with prefix)',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_oelib_testchild',
                'foreign_field' => 'tx_oelib_parent2',
                'foreign_sortby' => 'title',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
            ],
        ],
        'bidirectional' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
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
            'exclude' => 0,
            'config' => [
                'type' => 'none',
                'size' => '30',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title, friend, owner, children, related_records, composition'],
    ],
    'palettes' => [
        '1' => ['showitem' => 'starttime, endtime'],
    ],
];
