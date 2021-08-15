<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use OliverKlee\Oelib\ViewHelpers\UppercaseViewHelper;
use PHPUnit\Framework\MockObject\MockObject;

class UppercaseViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @test
     */
    public function renderConvertsToUppercase()
    {
        /** @var UppercaseViewHelper&MockObject $subject */
        $subject = $this->createPartialMock(UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->willReturn('foo bar');

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
        /** @var UppercaseViewHelper&MockObject $subject */
        $subject = $this->createPartialMock(UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->willReturn('äöü');

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
        /** @var UppercaseViewHelper&MockObject $subject */
        $subject = $this->createPartialMock(UppercaseViewHelper::class, ['renderChildren']);
        $subject->expects(self::once())->method('renderChildren')->willReturn('áàéè');

        self::assertSame(
            'ÁÀÉÈ',
            $subject->render()
        );
    }
}
