<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class LanguageTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_Model_Language
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Model_Language();
    }

    ////////////////////////////////////////////
    // Tests regarding getting the local name.
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getLocalNameReturnsLocalNameOfGerman()
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
    public function getLocalNameReturnsLocalNameOfEnglish()
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
    public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfGerman()
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
    public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfEnglish()
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
    public function isReadOnlyIsTrue()
    {
        self::assertTrue(
            $this->subject->isReadOnly()
        );
    }
}
