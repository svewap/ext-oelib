<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Http;

use OliverKlee\Oelib\Http\Interfaces\HeaderProxy;

/**
 * This class sends HTTP headers.
 *
 * Regarding the Strategy pattern, addHeader() represents one concrete behavior.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
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
        header($header);
    }
}
