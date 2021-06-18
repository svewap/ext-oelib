<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use OliverKlee\Oelib\ViewHelpers\UppercaseViewHelper;

/**
 * Test case.
 */
class UppercaseViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @test
     */
    public function renderConvertsToUppercase()
    {
        $subject = $this->createPartialMock(UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->willReturn('foo bar');

        /* @var UppercaseViewHelper $subject */
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
        $subject = $this->createPartialMock(UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->willReturn('äöü');

        /* @var UppercaseViewHelper $subject */
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
        $subject = $this->createPartialMock(UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->willReturn('áàéè');

        /* @var UppercaseViewHelper $subject */
        self::assertSame(
            'ÁÀÉÈ',
            $subject->render()
        );
    }
}
