<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Model;

/**
 * This model represents a federal state, e.g., Nordrhein-Westfalen (in Germany).
 */
class FederalState extends AbstractModel
{
    /**
     * @var bool
     */
    protected $readOnly = true;

    /**
     * Returns the local name, e.g., "Nordrhein-Westfalen".
     *
     * @return string the local name, will not be empty
     */
    public function getLocalName(): string
    {
        return $this->getAsString('zn_name_local');
    }

    /**
     * Returns the English name, e.g., "North Rhine-Westphalia".
     *
     * @return string the English name, will not be empty
     */
    public function getEnglishName(): string
    {
        return $this->getAsString('zn_name_en');
    }

    /**
     * Returns the ISO 3166-1 alpha-2 code, e.g., "DE".
     *
     * @return string the ISO 3166-1 alpha-2 code, will not be empty
     */
    public function getIsoAlpha2Code(): string
    {
        return $this->getAsString('zn_country_iso_2');
    }

    /**
     * Returns the ISO 3166-2 alpha-2 code, e.g., "NW".
     *
     * @return string the ISO 3166-2 alpha-2 code, will not be empty
     */
    public function getIsoAlpha2ZoneCode(): string
    {
        return $this->getAsString('zn_code');
    }
}
