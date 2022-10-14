<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use OliverKlee\Oelib\Model\Language;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Model\Language
 */
final class LanguageTest extends UnitTestCase
{
    /**
     * @var Language
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Language();
    }

    ////////////////////////////////////////////
    // Tests regarding getting the local name.
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getLocalNameReturnsLocalNameOfGerman(): void
    {
        $this->subject->setData(['lg_name_local' => 'Deutsch']);

        self::assertSame(
            'Deutsch',
            $this->subject->getLocalName()
        );
    }

    /**
     * @test
     */
    public function getLocalNameReturnsLocalNameOfEnglish(): void
    {
        $this->subject->setData(['lg_name_local' => 'English']);

        self::assertSame(
            'English',
            $this->subject->getLocalName()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding getting the ISO alpha-2 code.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfGerman(): void
    {
        $this->subject->setData(['lg_iso_2' => 'DE']);

        self::assertSame(
            'DE',
            $this->subject->getIsoAlpha2Code()
        );
    }

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfEnglish(): void
    {
        $this->subject->setData(['lg_iso_2' => 'EN']);

        self::assertSame(
            'EN',
            $this->subject->getIsoAlpha2Code()
        );
    }

    ////////////////////////////////
    // Tests concerning isReadOnly
    ////////////////////////////////

    /**
     * @test
     */
    public function isReadOnlyIsTrue(): void
    {
        self::assertTrue(
            $this->subject->isReadOnly()
        );
    }
}
