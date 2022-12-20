<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\Currency;

/**
 * @extends AbstractDataMapper<Currency>
 *
 * @deprecated will be removed in oelib 6.0
 */
class CurrencyMapper extends AbstractDataMapper
{
    protected $tableName = 'static_currencies';

    protected $modelClassName = Currency::class;

    protected $additionalKeys = ['cu_iso_3'];

    /**
     * Finds a language by its ISO 4217 alpha-3 code.
     *
     * @param non-empty-string $isoAlpha3Code the ISO 4217 alpha-3 code to find
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
