<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;

/**
 * This class represents a mapper for languages.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class LanguageMapper extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'static_languages';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Oelib_Model_Language::class;

    /**
     * @var string[] the column names of additional string keys
     */
    protected $additionalKeys = ['lg_iso_2'];

    /**
     * Finds a language by its ISO 639-1 alpha-2 code.
     *
     * @throws NotFoundException if there is no record with the
     *                                     provided ISO 639-1 alpha-2 code
     *
     * @param string $isoAlpha2Code
     *        the ISO 639-1 alpha-2 code to find, must not be empty
     *
     * @return \Tx_Oelib_Model_Language the language
     */
    public function findByIsoAlpha2Code(string $isoAlpha2Code): \Tx_Oelib_Model_Language
    {
        /** @var \Tx_Oelib_Model_Language $result */
        $result = $this->findOneByKey('lg_iso_2', $isoAlpha2Code);

        return $result;
    }
}
