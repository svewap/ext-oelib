<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Http;

use OliverKlee\Oelib\Http\Interfaces\HeaderProxy;

/**
 * This class stores HTTP header which were meant to be sent instead of really
 * sending them and provides various functions to get them for testing purposes.
 *
 * Regarding the Strategy pattern, `addHeader()` represents one concrete behavior.
 */
class HeaderCollector implements HeaderProxy
{
    /**
     * headers which were meant to be sent
     *
     * @var array<int, string>
     */
    private $headers = [];

    /**
     * Stores a HTTP header which was meant to be sent.
     *
     * @param string $header HTTP header to send, must not be empty
     */
    public function addHeader(string $header): void
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
        if ($this->headers === []) {
            return '';
        }

        return end($this->headers);
    }

    /**
     * Returns all headers added with this instance or an empty array if there is none.
     *
     * @return array<int, string> all added headers, will be empty if there is none
     */
    public function getAllAddedHeaders(): array
    {
        return $this->headers;
    }
}
