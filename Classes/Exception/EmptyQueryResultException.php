<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Exception;

/**
 * This class represents an exception that should be thrown when a database
 * query has an empty result, but shouldn't have.
 */
class EmptyQueryResultException extends \RuntimeException
{
}
