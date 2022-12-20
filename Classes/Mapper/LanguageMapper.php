<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\Language;

/**
 * @extends AbstractDataMapper<Language>
 *
 * @deprecated will be removed in oelib 6.0
 */
class LanguageMapper extends AbstractDataMapper
{
    protected $tableName = 'static_languages';

    protected $modelClassName = Language::class;

    protected $additionalKeys = ['lg_iso_2'];

    /**
     * Finds a language by its ISO 639-1 alpha-2 code.
     *
     * @param non-empty-string $isoAlpha2Code the ISO 639-1 alpha-2 code to find
     *
     * @return Language the language
     *
     * @throws NotFoundException if there is no record with the provided ISO 639-1 alpha-2 code
     */
    public function findByIsoAlpha2Code(string $isoAlpha2Code): Language
    {
        /** @var Language $result */
        $result = $this->findOneByKey('lg_iso_2', $isoAlpha2Code);

        return $result;
    }
}
