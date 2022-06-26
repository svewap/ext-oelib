<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Mapper\LanguageMapper;
use OliverKlee\Oelib\Model\Language;

class LanguageMapperTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
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
     *
     * @throws NimutException
     */
    private function importStaticData(): void
    {
        if ($this->getDatabaseConnection()->selectCount('*', 'static_languages') === 0) {
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
