<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\LanguageMapper;
use OliverKlee\Oelib\Model\Language;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\LanguageMapper
 */
final class LanguageMapperTest extends UnitTestCase
{
    /**
     * @var LanguageMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new LanguageMapper();
    }

    /**
     * @test
     */
    public function isMapper(): void
    {
        self::assertInstanceOf(AbstractDataMapper::class, $this->subject);
    }

    /**
     * @test
     */
    public function createsLanguageModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(Language::class, $model);
    }
}
