<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Http\Interfaces;

/**
 * This interface declares the function `addHeader()` for concrete classes, so they
 * need to implement the concrete behavior.
 *
 * Regarding the Strategy pattern, `addHeader()` represents the abstract strategy.
 */
interface HeaderProxy
{
    /**
     * This function usually should add a HTTP header.
     *
     * @param string $header
     *        HTTP header to send, e.g. 'Status: 404 Not Found', must not be empty
     *
     * @return void
     */
    public function addHeader(string $header);
}
