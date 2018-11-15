<?php
defined('TYPO3_MODE') or die('Access denied.');

return [
    'ctrl' => [
        'default_sortby' => 'zip_code',
        'delete' => 'deleted',
        'iconfile' => 'EXT:oelib/Resources/Public/Icons/ZipCode.svg',
        'is_static' => true,
        'label' => 'zip_code',
        'label_alt' => 'city_name',
        'label_alt_force' => true,
        'readOnly' => true,
        'rootLevel' => 1,
        'searchFields' => 'zip_code, city_name',
        'title' => 'LLL:EXT:oelib/Resources/Private/Language/locallang_db.xlf:tx_oelib_domain_model_germanzipcode',
    ],
    'interface' => [
        'showRecordFieldList' => 'zip_code, city_name, longitude, latitude',
    ],
    'columns' => [
        'zip_code' => [
            'label' => 'LLL:EXT:oelib/Resources/Private/Language/locallang_db.xlf:tx_oelib_domain_model_germanzipcode.zip_code',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'size' => 10,
            ],
        ],
        'city_name' => [
            'label' => 'LLL:EXT:oelib/Resources/Private/Language/locallang_db.xlf:tx_oelib_domain_model_germanzipcode.city_name',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'size' => 50,
            ],
        ],
        'longitude' => [
            'label' => 'LLL:EXT:oelib/Resources/Private/Language/locallang_db.xlf:tx_oelib_domain_model_germanzipcode.longitude',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'size' => 10,
            ],
        ],
        'latitude' => [
            'label' => 'LLL:EXT:oelib/Resources/Private/Language/locallang_db.xlf:tx_oelib_domain_model_germanzipcode.latitude',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'size' => 10,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'zip_code, city_name, longitude, latitude'],
    ],
];
