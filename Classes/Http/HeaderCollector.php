<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Http;

use OliverKlee\Oelib\Http\Interfaces\HeaderProxy;

/**
 * This class stores HTTP header which were meant to be sent instead of really
 * sending them and provides various functions to get them for testing purposes.
 *
 * Regarding the Strategy pattern, addHeader() represents one concrete behavior.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class HeaderCollector implements HeaderProxy
{
    /**
     * headers which were meant to be sent
     *
     * @var string[]
     */
    private $headers = [];

    /**
     * Stores a HTTP header which was meant to be sent.
     *
     * @param string $header HTTP header to send, must not be empty
     *
     * @return void
     */
    public function addHeader(string $header)
    {
        $this->headers[] = $header;
    }

    /**
     * Returns the last header or an empty string if there are none.
     *
     * @return string last header, will be empty if there are none
     */
    public function getLastAddedHeader(): string
    {
        if (empty($this->headers)) {
            return '';
        }

        return end($this->headers);
    }

    /**
     * Returns all headers added with this instance or an empty array if there
     * is none.
     *
     * @return string[] all added headers, will be empty if there is none
     */
    public function getAllAddedHeaders(): array
    {
        return $this->headers;
    }
}
