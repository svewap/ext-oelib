<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Http;

use OliverKlee\Oelib\Http\Interfaces\HeaderProxy;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * This class sends HTTP headers.
 *
 * Regarding the Strategy pattern, `addHeader()` represents one concrete behavior.
 */
class RealHeaderProxy implements HeaderProxy
{
    /**
     * Adds a header.
     *
     * @param string $header HTTP header to send, must not be empty
     *
     * @return void
     */
    public function addHeader(string $header)
    {
        HttpUtility::setResponseCode($header);
    }
}
