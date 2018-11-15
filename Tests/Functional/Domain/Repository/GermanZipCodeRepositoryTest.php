<?php

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

        static::assertInstanceOf(GermanZipCode::class, $result);
        static::assertSame('01067', $result->getZipCode());
        static::assertSame('Dresden', $result->getCityName());
        static::assertEquals(13.721068, $result->getLongitude());
        static::assertEquals(51.060036, $result->getLatitude());
    }

    /**
     * @test
     */
    public function findOneByZipCodeWithMatchReturnsMatch()
    {
        $zipCode = '01067';
        /** @var GermanZipCode $result */
        $result = $this->subject->findOneByZipCode($zipCode);

        static::assertInstanceOf(GermanZipCode::class, $result);
        static::assertSame($zipCode, $result->getZipCode());
        static::assertSame('Dresden', $result->getCityName());
    }

    /**
     * @test
     */
    public function findOneByZipCodeWithoutMatchReturnsNull()
    {
        $zipCode = '00000';
        $result = $this->subject->findOneByZipCode($zipCode);

        static::assertNull($result);
    }
}
