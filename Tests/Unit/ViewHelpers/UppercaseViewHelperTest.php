<?php

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class UppercaseViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @test
     */
    public function renderConvertsToUppercase()
    {
        $subject = $this->getMock(\Tx_Oelib_ViewHelpers_UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->will(self::returnValue('foo bar'));

        /* @var \Tx_Oelib_ViewHelpers_UppercaseViewHelper $subject */
        self::assertSame(
            'FOO BAR',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanConvertUmlautsToUppercase()
    {
        $subject = $this->getMock(\Tx_Oelib_ViewHelpers_UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->will(self::returnValue('äöü'));

        /* @var \Tx_Oelib_ViewHelpers_UppercaseViewHelper $subject */
        self::assertSame(
            'ÄÖÜ',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanConvertAccentedCharactersToUppercase()
    {
        $subject = $this->getMock(\Tx_Oelib_ViewHelpers_UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->will(self::returnValue('áàéè'));

        /* @var \Tx_Oelib_ViewHelpers_UppercaseViewHelper $subject */
        self::assertSame(
            'ÁÀÉÈ',
            $subject->render()
        );
    }
}
