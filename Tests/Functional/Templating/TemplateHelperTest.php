<?php

namespace OliverKlee\Oelib\Tests\Functional\Templating;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Tests\Functional\Templating\Fixtures\TestingTemplateHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        parent::setUp();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);
        \Tx_Oelib_ConfigurationProxy::getInstance('oelib')->setAsBoolean('enableConfigCheck', true);

        $this->subject = new TestingTemplateHelper([]);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    ///////////////////////////////////////////////////////////////////////
    // Tests for the behavior of the template helper without a front end.
    ///////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function initMarksObjectAsInitialized()
    {
        $this->subject->init();

        self::assertTrue(
            $this->subject->isInitialized()
        );
    }

    /**
     * @test
     */
    public function initInitializesContentObjectRenderer()
    {
        $this->subject->init();

        self::assertInstanceOf(ContentObjectRenderer::class, $this->subject->cObj);
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

    /**
     * @test
     */
    public function getSubpartWithLabelsReturnsVerbatimSubpartWithoutLabels()
    {
        $subpartContent = 'Subpart content';
        $templateCode = 'Text before the subpart'
            . '<!-- ###MY_SUBPART### -->'
            . $subpartContent
            . '<!-- ###MY_SUBPART### -->'
            . 'Text after the subpart.';

        $this->subject->processTemplate($templateCode);

        self::assertSame(
            $subpartContent,
            $this->subject->getSubpartWithLabels('MY_SUBPART')
        );
    }

    /**
     * @test
     */
    public function getSubpartWithLabelsReplacesLabelMarkersWithLabels()
    {
        $templateCode = 'Text before the subpart'
            . '<!-- ###MY_SUBPART### -->before ###LABEL_FOO### after<!-- ###MY_SUBPART### -->'
            . 'Text after the subpart.';

        $this->subject->processTemplate($templateCode);

        self::assertSame(
            'before foo after',
            $this->subject->getSubpartWithLabels('MY_SUBPART')
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

    ////////////////////////////////////////////
    // Tests for automatically setting labels.
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function setLabels()
    {
        $this->subject->processTemplate(
            'a ###LABEL_FOO### b'
        );
        $this->subject->setLabels();
        self::assertSame(
            'a foo b',
            $this->subject->getSubpart()
        );
    }

    /**
     * @test
     */
    public function setLabelsNoSalutation()
    {
        $this->subject->processTemplate(
            'a ###LABEL_BAR### b'
        );
        $this->subject->setLabels();
        self::assertSame(
            'a bar (no salutation) b',
            $this->subject->getSubpart()
        );
    }

    /**
     * @test
     */
    public function setLabelsFormal()
    {
        $this->subject->setSalutationMode('formal');
        $this->subject->processTemplate(
            'a ###LABEL_BAR### b'
        );
        $this->subject->setLabels();
        self::assertSame(
            'a bar (formal) b',
            $this->subject->getSubpart()
        );
    }

    /**
     * @test
     */
    public function setLabelsInformal()
    {
        $this->subject->setSalutationMode('informal');
        $this->subject->processTemplate(
            'a ###LABEL_BAR### b'
        );
        $this->subject->setLabels();
        self::assertSame(
            'a bar (informal) b',
            $this->subject->getSubpart()
        );
    }

    /**
     * @test
     */
    public function setLabelsWithOneBeingThePrefixOfAnother()
    {
        $this->subject->processTemplate(
            '###LABEL_FOO###, ###LABEL_FOO2###'
        );
        $this->subject->setLabels();
        self::assertSame(
            'foo, foo two',
            $this->subject->getSubpart()
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
        $pageId = $this->testingFramework->createFrontEndPage();
        self::assertSame(
            [],
            $this->subject->retrievePageConfig($pageId)
        );
    }
}
