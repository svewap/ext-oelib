<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\GermanZipCodeRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @covers \OliverKlee\Oelib\Domain\Model\GermanZipCode
 * @covers \OliverKlee\Oelib\Domain\Repository\GermanZipCodeRepository
 * @covers \OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic
 */
final class GermanZipCodeRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var GermanZipCodeRepository
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $this->subject = GeneralUtility::makeInstance(GermanZipCodeRepository::class);
        } else {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->subject = $objectManager->get(GermanZipCodeRepository::class);
        }

        $this->importDataSet(__DIR__ . '/Fixtures/ZipCodes.xml');
    }

    /**
     * @test
     */
    public function mapsAllModelFields(): void
    {
        /** @var GermanZipCode $result */
        $result = $this->subject->findByUid(9000);

        self::assertInstanceOf(GermanZipCode::class, $result);
        self::assertSame('01067', $result->getZipCode());
        self::assertSame('Dresden', $result->getCityName());
        self::assertEquals(13.721068, $result->getLongitude());
        self::assertEquals(51.060036, $result->getLatitude());
    }

    /**
     * @test
     */
    public function findOneByZipCodeWithMatchReturnsMatch(): void
    {
        $zipCode = '01067';
        /** @var GermanZipCode $result */
        $result = $this->subject->findOneByZipCode($zipCode);

        self::assertInstanceOf(GermanZipCode::class, $result);
        self::assertSame($zipCode, $result->getZipCode());
        self::assertSame('Dresden', $result->getCityName());
    }

    /**
     * @test
     */
    public function findOneByZipCodeWithMatchCalledTwoTimesReturnsTheSameModel(): void
    {
        $zipCode = '01067';
        $firstResult = $this->subject->findOneByZipCode($zipCode);
        $secondResult = $this->subject->findOneByZipCode($zipCode);

        self::assertSame($firstResult, $secondResult);
    }

    /**
     * @return string[][]
     */
    public function nonMatchedZipCodesDataProvider(): array
    {
        return [
            '5 digits without match' => ['00000'],
            '5 letters' => ['av3sd'],
            '4 digits' => ['1233'],
            '6 digits' => ['463726'],
            'empty string' => [''],
        ];
    }

    /**
     * @test
     *
     * @param string $zipCode
     *
     * @dataProvider nonMatchedZipCodesDataProvider
     */
    public function findOneByZipCodeWithoutMatchReturnsNull(string $zipCode): void
    {
        $result = $this->subject->findOneByZipCode($zipCode);

        self::assertNull($result);
    }
}
