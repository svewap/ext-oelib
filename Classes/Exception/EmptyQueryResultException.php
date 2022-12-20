<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Exception;

/**
 * This class represents an exception that should be thrown when a database
 * query has an empty result, but shouldn't have.
 *
 * @deprecated will be removed in oelib 6.0
 */
class EmptyQueryResultException extends \RuntimeException
{
}
