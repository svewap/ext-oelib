<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\Country;

/**
 * @extends AbstractDataMapper<Country>
 *
 * @deprecated will be removed in oelib 6.0
 */
class CountryMapper extends AbstractDataMapper
{
    protected $tableName = 'static_countries';

    protected $modelClassName = Country::class;

    protected $additionalKeys = ['cn_iso_2', 'cn_iso_3'];

    /**
     * Finds a country by its ISO 3166-1 alpha-2 code.
     *
     * @param non-empty-string $isoAlpha2Code the ISO 3166-1 alpha-2 code to find
     *
     * @return Country the country
     *
     * @throws NotFoundException if there is no record with the provided ISO 3166-1 alpha-2 code
     */
    public function findByIsoAlpha2Code(string $isoAlpha2Code): Country
    {
        /** @var Country $result */
        $result = $this->findOneByKey('cn_iso_2', $isoAlpha2Code);

        return $result;
    }

    /**
     * Finds a country by its ISO 3166-1 alpha-3 code.
     *
     * @param non-empty-string $isoAlpha3Code the ISO 3166-1 alpha-3 code to find
     *
     * @return Country the country
     *
     * @throws NotFoundException if there is no record with the provided ISO 3166-1 alpha-3 code
     */
    public function findByIsoAlpha3Code(string $isoAlpha3Code): Country
    {
        /** @var Country $result */
        $result = $this->findOneByKey('cn_iso_3', $isoAlpha3Code);

        return $result;
    }
}
