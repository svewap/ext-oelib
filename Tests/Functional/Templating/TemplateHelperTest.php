<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Templating;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Tests\Unit\Templating\Fixtures\TestingTemplateHelper;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Oelib\Templating\TemplateHelper
 */
class TemplateHelperTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingTemplateHelper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TypoScriptFrontendController&ProphecySubjectInterface $frontEndController */
        $frontEndController = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        $frontEndController->cObj = $this->prophesize(ContentObjectRenderer::class)->reveal();
        $GLOBALS['TSFE'] = $frontEndController;

        $configuration = new DummyConfiguration(['enableConfigCheck' => true]);
        ConfigurationProxy::setInstance('oelib', $configuration);

        $this->subject = new TestingTemplateHelper([]);
    }

    protected function tearDown(): void
    {
        ConfigurationProxy::purgeInstances();
    }

    ///////////////////////////////
    // Tests for getting subparts.
    ///////////////////////////////

    /**
     * @test
     */
    public function noSubpartsAndEmptySubpartName(): void
    {
        self::assertSame(
            '',
            $this->subject->getSubpart()
        );
    }

    /**
     * @test
     */
    public function getSubpartWithNotExistingSubpartNameThrowsException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('$key contained the subpart name "FOOBAR"');
        $this->expectExceptionCode(1632760625);

        $this->subject->getSubpart('FOOBAR');
    }

    /**
     * @test
     */
    public function getCompleteTemplateReturnsCompleteTemplateContent(): void
    {
        $templateCode = "This is a test including\na linefeed.\n";
        $this->subject->processTemplate(
            $templateCode
        );
        self::assertSame(
            $templateCode,
            $this->subject->getSubpart()
        );
    }

    ////////////////////////////////
    // Tests for setting subparts.
    ////////////////////////////////

    /**
     * @test
     */
    public function setNewSubpartNotEmptyGetSubpart(): void
    {
        $this->subject->processTemplate(
            'Some text.'
        );
        $this->subject->setSubpart('MY_SUBPART', 'foo');
        self::assertSame(
            'foo',
            $this->subject->getSubpart('MY_SUBPART')
        );
    }

    ///////////////////////////////////////////////////
    // Tests for getting subparts with invalid names.
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function getSubpartWithLowercaseNameIsIgnoredWithUsingLowercase(): void
    {
        $this->subject->processTemplate(
            '<!-- ###my_subpart### -->'
            . 'Some text.'
            . '<!-- ###my_subpart### -->'
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('$key contained the subpart name "my_subpart"');
        $this->expectExceptionCode(1632760625);

        $this->subject->getSubpart('my_subpart');
    }

    /**
     * @test
     */
    public function subpartWithLowercaseNameIsIgnoredWithUsingUppercase(): void
    {
        $this->subject->processTemplate(
            '<!-- ###my_subpart### -->'
            . 'Some text.'
            . '<!-- ###my_subpart### -->'
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('$key contained the subpart name "MY_SUBPART"');
        $this->expectExceptionCode(1632760625);

        $this->subject->getSubpart('MY_SUBPART');
    }
}
