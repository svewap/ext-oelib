<?php
defined('TYPO3') or die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'][]
    = \OliverKlee\Oelib\Testing\TestingFrameworkCleanup::class;
