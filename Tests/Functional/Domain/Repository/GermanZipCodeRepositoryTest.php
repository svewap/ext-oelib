<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\GermanZipCodeRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class GermanZipCodeRepositoryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var GermanZipCodeRepository
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $objectManager->get(GermanZipCodeRepository::class);
        $this->importDataSet(__DIR__ . '/Fixtures/ZipCodes.xml');
    }

    /**
     * @test
     */
    public function mapsAllModelFields()
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
    public function findOneByZipCodeWithMatchReturnsMatch()
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
    public function findOneByZipCodeWithMatchCalledTwoTimesReturnsTheSameModel()
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
    public function findOneByZipCodeWithoutMatchReturnsNull($zipCode)
    {
        $result = $this->subject->findOneByZipCode($zipCode);

        self::assertNull($result);
    }
}
