<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

/**
 * Interface for all types of configuration.
 */
interface Configuration
{
    public function getAsString(string $key): string;

    public function hasString(string $key): bool;

    public function getAsInteger(string $key): int;

    public function hasInteger(string $key): bool;

    public function getAsBoolean(string $key): bool;

    /**
     * Gets the value stored under the provided key, converted to an array of trimmed strings.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return array<int, string> the array value of the given key, may be empty
     */
    public function getAsTrimmedArray(string $key): array;
}
