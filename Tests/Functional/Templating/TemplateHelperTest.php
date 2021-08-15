<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Templating;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Tests\Unit\Templating\Fixtures\TestingTemplateHelper;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TemplateHelperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingTemplateHelper
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        /** @var TypoScriptFrontendController|ProphecySubjectInterface $frontEndController */
        $frontEndController = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        $frontEndController->cObj = $this->prophesize(ContentObjectRenderer::class)->reveal();
        $GLOBALS['TSFE'] = $frontEndController;

        /** @var ConfigurationProxy $configuration */
        $configuration = ConfigurationProxy::getInstance('oelib');
        $configuration->setAsBoolean('enableConfigCheck', true);

        $this->subject = new TestingTemplateHelper([]);
    }

    ///////////////////////////////
    // Tests for getting subparts.
    ///////////////////////////////

    /**
     * @test
     */
    public function noSubpartsAndEmptySubpartName()
    {
        self::assertSame(
            '',
            $this->subject->getSubpart()
        );
        self::assertSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function notExistingSubpartName()
    {
        self::assertSame(
            '',
            $this->subject->getSubpart('FOOBAR')
        );
        self::assertContains(
            'The subpart',
            $this->subject->getWrappedConfigCheckMessage()
        );
        self::assertContains(
            'is missing',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function getCompleteTemplateReturnsCompleteTemplateContent()
    {
        $templateCode = "This is a test including\na linefeed.\n";
        $this->subject->processTemplate(
            $templateCode
        );
        self::assertSame(
            $templateCode,
            $this->subject->getSubpart()
        );
        self::assertSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    ////////////////////////////////
    // Tests for setting subparts.
    ////////////////////////////////

    /**
     * @test
     */
    public function setNewSubpartNotEmptyGetSubpart()
    {
        $this->subject->processTemplate(
            'Some text.'
        );
        $this->subject->setSubpart('MY_SUBPART', 'foo');
        self::assertSame(
            'foo',
            $this->subject->getSubpart('MY_SUBPART')
        );
        self::assertSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    ///////////////////////////////////////////////////
    // Tests for getting subparts with invalid names.
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function subpartWithLowercaseNameIsIgnoredWithUsingLowercase()
    {
        $this->subject->processTemplate(
            '<!-- ###my_subpart### -->'
            . 'Some text.'
            . '<!-- ###my_subpart### -->'
        );
        self::assertSame(
            '',
            $this->subject->getSubpart('my_subpart')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function subpartWithLowercaseNameIsIgnoredWithUsingUppercase()
    {
        $this->subject->processTemplate(
            '<!-- ###my_subpart### -->'
            . 'Some text.'
            . '<!-- ###my_subpart### -->'
        );
        self::assertSame(
            '',
            $this->subject->getSubpart('MY_SUBPART')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    // Tests concerning TypoScript templates.

    /**
     * @test
     */
    public function pageSetupInitiallyIsEmpty()
    {
        self::assertSame(
            [],
            $this->subject->retrievePageConfig(1)
        );
    }
}
