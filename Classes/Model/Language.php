<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Model;

/**
 * This class represents a language.
 *
 * @deprecated will be removed in oelib 6.0
 */
class Language extends AbstractModel
{
    /**
     * @var bool whether this model is read-only
     */
    protected $readOnly = true;

    /**
     * Returns the language's local name.
     *
     * @return string the language's local name, will not be empty
     */
    public function getLocalName(): string
    {
        return $this->getAsString('lg_name_local');
    }

    /**
     * Returns the ISO 639-1 alpha-2 code for this language.
     *
     * @return string the ISO 639-1 alpha-2 code of this language, will not be empty
     */
    public function getIsoAlpha2Code(): string
    {
        return $this->getAsString('lg_iso_2');
    }
}
