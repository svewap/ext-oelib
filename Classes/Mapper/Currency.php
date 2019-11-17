<?php
declare(strict_types = 1);

/**
 * This class represents a mapper for currencies.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Mapper_Currency extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'static_currencies';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Oelib_Model_Currency::class;

    /**
     * @var string[] the column names of additional string keys
     */
    protected $additionalKeys = ['cu_iso_3'];

    /**
     * Finds a language by its ISO 4217 alpha-3 code.
     *
     * @throws \Tx_Oelib_Exception_NotFound if there is no record with the
     *                                     provided ISO 4217 alpha-3 code
     *
     * @param string $isoAlpha3Code
     *        the ISO 4217 alpha-3 code to find, must not be empty
     *
     * @return \Tx_Oelib_Model_Currency the currency
     */
    public function findByIsoAlpha3Code(string $isoAlpha3Code): \Tx_Oelib_Model_Currency
    {
        /** @var \Tx_Oelib_Model_Currency $result */
        $result = $this->findOneByKey('cu_iso_3', $isoAlpha3Code);

        return $result;
    }
}
