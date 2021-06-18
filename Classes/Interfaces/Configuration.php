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
}
