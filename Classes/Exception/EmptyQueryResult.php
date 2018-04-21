<?php

use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * This class represents an exception that should be thrown when a database
 * query has an empty result, but shouldn't have.
 *
 * The exception automatically will use an error message and the last query.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Exception_EmptyQueryResult extends Exception
{
    /**
     * The constructor.
     *
     * @param int $code error code, must be >= 0
     */
    public function __construct($code = 0)
    {
        $message = 'The database query returned an empty result, but should  have returned a non-empty result.';

        /** @var DatabaseConnection $databaseConnection */
        $databaseConnection = $GLOBALS['TYPO3_DB'];
        if ($databaseConnection->store_lastBuiltQuery || $databaseConnection->debugOutput) {
            $message .= LF . 'The last built query:' . LF . $databaseConnection->debug_lastBuiltQuery;
        }

        parent::__construct($message, $code);
    }
}
