<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use OliverKlee\Oelib\Mapper\LanguageMapper;
use OliverKlee\Oelib\Model\Language;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\LanguageMapper
 * @covers \OliverKlee\Oelib\Model\Language
 */
final class LanguageMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/static_info_tables'];

    /**
     * @var LanguageMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importStaticData();
        $this->subject = new LanguageMapper();
    }

    /**
     * Imports static records - but only if they aren't already available as static data.
     */
    private function importStaticData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('static_languages');
        if ($connection->count('*', 'static_languages', []) === 0) {
            $this->importDataSet(__DIR__ . '/../Fixtures/Languages.xml');
        }
    }

    ///////////////////////////
    // Tests concerning find.
    ///////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        /** @var Language $model */
        $model = $this->subject->find(43);
        self::assertSame(
            'DE',
            $model->getIsoAlpha2Code()
        );
    }

    /////////////////////////////////////////
    // Tests regarding findByIsoAlpha2Code.
    /////////////////////////////////////////

    /**
     * @test
     */
    public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsLanguageInstance(): void
    {
        self::assertInstanceOf(
            Language::class,
            $this->subject->findByIsoAlpha2Code('DE')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsRecordAsModel(): void
    {
        self::assertSame(
            'DE',
            $this->subject->findByIsoAlpha2Code('DE')->getIsoAlpha2Code()
        );
    }
}
