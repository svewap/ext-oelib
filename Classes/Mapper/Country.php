<?php
declare(strict_types = 1);

/**
 * This class represents a mapper for countries.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Mapper_Country extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'static_countries';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Oelib_Model_Country::class;

    /**
     * @var string[] the column names of additional string keys
     */
    protected $additionalKeys = ['cn_iso_2', 'cn_iso_3'];

    /**
     * Finds a country by its ISO 3166-1 alpha-2 code.
     *
     * @throws \Tx_Oelib_Exception_NotFound if there is no record with the
     *                                     provided ISO 3166-1 alpha-2 code
     *
     * @param string $isoAlpha2Code
     *        the ISO 3166-1 alpha-2 code to find, must not be empty
     *
     * @return \Tx_Oelib_Model_Country the country
     */
    public function findByIsoAlpha2Code($isoAlpha2Code): \Tx_Oelib_Model_Country
    {
        return $this->findOneByKey('cn_iso_2', $isoAlpha2Code);
    }

    /**
     * Finds a country by its ISO 3166-1 alpha-3 code.
     *
     * @throws \Tx_Oelib_Exception_NotFound if there is no record with the
     *                                     provided ISO 3166-1 alpha-3 code
     *
     * @param string $isoAlpha3Code
     *        the ISO 3166-1 alpha-3 code to find, must not be empty
     *
     * @return \Tx_Oelib_Model_Country the country
     */
    public function findByIsoAlpha3Code(string $isoAlpha3Code): \Tx_Oelib_Model_Country
    {
        return $this->findOneByKey('cn_iso_3', $isoAlpha3Code);
    }
}
