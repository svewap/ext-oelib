<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\Currency;

/**
 * @extends AbstractDataMapper<Currency>
 */
class CurrencyMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'static_currencies';

    /**
     * @var class-string<Currency> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Currency::class;

    /**
     * @var array<int, string> the column names of additional string keys
     */
    protected $additionalKeys = ['cu_iso_3'];

    /**
     * Finds a language by its ISO 4217 alpha-3 code.
     *
     * @param string $isoAlpha3Code the ISO 4217 alpha-3 code to find, must not be empty
     *
     * @return Currency the currency
     *
     * @throws NotFoundException if there is no record with the provided ISO 4217 alpha-3 code
     */
    public function findByIsoAlpha3Code(string $isoAlpha3Code): Currency
    {
        /** @var Currency $result */
        $result = $this->findOneByKey('cu_iso_3', $isoAlpha3Code);

        return $result;
    }
}
