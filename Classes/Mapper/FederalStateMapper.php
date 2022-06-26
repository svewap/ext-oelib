<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Model\FederalState;

/**
 * @extends AbstractDataMapper<FederalState>
 */
class FederalStateMapper extends AbstractDataMapper
{
    protected $tableName = 'static_country_zones';

    protected $modelClassName = FederalState::class;

    protected $compoundKeyParts = ['zn_country_iso_2', 'zn_code'];

    /**
     * Finds a federal state by its ISO 3166-1 and ISO 3166-2 code.
     *
     * @param string $isoAlpha2CountryCode the ISO 3166-1 alpha-2 country code to find, must not be empty
     * @param string $isoAlpha2ZoneCode the ISO 3166-2 code to find, must not be empty
     *
     * @return FederalState the federal state with the requested code
     */
    public function findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCode(
        string $isoAlpha2CountryCode,
        string $isoAlpha2ZoneCode
    ): FederalState {
        return $this->findOneByCompoundKey(
            [
                'zn_country_iso_2' => $isoAlpha2CountryCode,
                'zn_code' => $isoAlpha2ZoneCode,
            ]
        );
    }
}
