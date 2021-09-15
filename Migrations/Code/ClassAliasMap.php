<?php

declare(strict_types=1);

return [
    // Configuration
    'Tx_Oelib_ConfigCheck' => \OliverKlee\Oelib\Configuration\ConfigurationCheck::class,
    \OliverKlee\Oelib\Configuration\Configuration::class => \OliverKlee\Oelib\Configuration\TypoScriptConfiguration::class,
];
