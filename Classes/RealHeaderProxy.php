<?php

/**
 * This class sends HTTP headers.
 *
 * Regarding the Strategy pattern, addHeader() represents one concrete behavior.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_RealHeaderProxy extends Tx_Oelib_AbstractHeaderProxy
{
    /**
     * Adds a header.
     *
     * @param string $header HTTP header to send, must not be empty
     *
     * @return void
     */
    public function addHeader($header)
    {
        header($header);
    }
}
