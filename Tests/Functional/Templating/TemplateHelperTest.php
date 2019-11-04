<?php

namespace OliverKlee\Oelib\Tests\Functional\Templating;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Tests\Functional\Templating\Fixtures\TestingTemplateHelper;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
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

        \Tx_Oelib_ConfigurationProxy::getInstance('oelib')->setAsBoolean('enableConfigCheck', true);

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
        $templateCode = 'This is a test including' . LF . 'a linefeed.' . LF;
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

    /**
     * @test
     */
    public function setNewSubpartWithNameWithSpaceCreatesWarning()
    {
        $this->subject->processTemplate(
            'Some text.'
        );
        $this->subject->setSubpart('MY SUBPART', 'foo');
        self::assertSame(
            '',
            $this->subject->getSubpart('MY SUBPART')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function setNewSubpartWithNameWithUtf8UmlautCreatesWarning()
    {
        $this->subject->processTemplate(
            'Some text.'
        );
        $this->subject->setSubpart('MY_SÜBPART', 'foo');
        self::assertSame(
            '',
            $this->subject->getSubpart('MY_SÜBPART')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function setNewSubpartWithNameWithUnderscoreSuffixCreatesWarning()
    {
        $this->subject->processTemplate(
            'Some text.'
        );
        $this->subject->setSubpart('MY_SUBPART_', 'foo');
        self::assertSame(
            '',
            $this->subject->getSubpart('MY_SUBPART_')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function setNewSubpartWithNameStartingWithUnderscoreCreatesWarning()
    {
        $this->subject->processTemplate(
            'Some text.'
        );
        $this->subject->setSubpart('_MY_SUBPART', 'foo');
        self::assertSame(
            '',
            $this->subject->getSubpart('_MY_SUBPART')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function setNewSubpartWithNameStartingWithNumberCreatesWarning()
    {
        $this->subject->processTemplate(
            'Some text.'
        );
        $this->subject->setSubpart('1_MY_SUBPART', 'foo');
        self::assertSame(
            '',
            $this->subject->getSubpart('1_MY_SUBPART')
        );
        self::assertNotSame(
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
    public function subpartWithNameWithSpaceIsIgnored()
    {
        $this->subject->processTemplate(
            '<!-- ###MY SUBPART### -->'
            . 'Some text.'
            . '<!-- ###MY SUBPART### -->'
        );
        self::assertSame(
            '',
            $this->subject->getSubpart('MY SUBPART')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function subpartWithNameWithUtf8UmlautIsIgnored()
    {
        $this->subject->processTemplate(
            '<!-- ###MY_SÜBPART### -->'
            . 'Some text.'
            . '<!-- ###MY_SÜBPART### -->'
        );
        self::assertSame(
            '',
            $this->subject->getSubpart('MY_SÜBPART')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function subpartWithNameWithUnderscoreSuffixIsIgnored()
    {
        $this->subject->processTemplate(
            '<!-- ###MY_SUBPART_### -->'
            . 'Some text.'
            . '<!-- ###MY_SUBPART_### -->'
        );
        self::assertSame(
            '',
            $this->subject->getSubpart('MY_SUBPART_')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function subpartWithNameStartingWithUnderscoreIsIgnored()
    {
        $this->subject->processTemplate(
            '<!-- ###_MY_SUBPART### -->'
            . 'Some text.'
            . '<!-- ###_MY_SUBPART### -->'
        );
        self::assertSame(
            '',
            $this->subject->getSubpart('_MY_SUBPART')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

    /**
     * @test
     */
    public function subpartWithNameStartingWithNumberIsIgnored()
    {
        $this->subject->processTemplate(
            '<!-- ###1_MY_SUBPART### -->'
            . 'Some text.'
            . '<!-- ###1_MY_SUBPART### -->'
        );
        self::assertSame(
            '',
            $this->subject->getSubpart('1_MY_SUBPART')
        );
        self::assertNotSame(
            '',
            $this->subject->getWrappedConfigCheckMessage()
        );
    }

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

    ///////////////////////////////////
    // Tests concerning TS templates.
    ///////////////////////////////////

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
