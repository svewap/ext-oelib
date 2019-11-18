<?php

declare(strict_types=1);

/**
 * This class declares the function addHeader() for its inheritants. So they
 * need to implement the concrete behavior.
 *
 * Regarding the Strategy pattern, addHeader() represents the abstract strategy.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
abstract class Tx_Oelib_AbstractHeaderProxy
{
    /**
     * This function usually should add a HTTP header.
     *
     * @param string $header
     *        HTTP header to send, e.g. 'Status: 404 Not Found', must not be empty
     *
     * @return void
     */
    abstract public function addHeader(string $header);
}
