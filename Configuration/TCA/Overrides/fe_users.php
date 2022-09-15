<?php

defined('TYPO3') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    ['tx_oelib_is_dummy_record' => ['config' => ['type' => 'none']]]
);
