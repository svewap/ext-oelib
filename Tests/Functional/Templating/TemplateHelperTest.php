<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Templating;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Tests\Unit\Templating\Fixtures\TestingTemplateHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Templating\TemplateHelper
 */
final class TemplateHelperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    protected $initializeDatabase = false;

    /**
     * @var TestingTemplateHelper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $frontEndControllerMock = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()->getMock();
        $frontEndControllerMock->cObj = $this->createMock(ContentObjectRenderer::class);
        $GLOBALS['TSFE'] = $frontEndControllerMock;

        $configuration = new DummyConfiguration(['enableConfigCheck' => true]);
        ConfigurationProxy::setInstance('oelib', $configuration);

        $this->subject = new TestingTemplateHelper([]);
    }

    protected function tearDown(): void
    {
        ConfigurationProxy::purgeInstances();
        parent::tearDown();
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

    // Tests for automatically setting labels.

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setLabelsAfterGetTemplateCodeWithoutTemplatePathDoesNotCrash(): void
    {
        $this->subject->getTemplateCode();
        $this->subject->setLabels();
    }

    /**
     * @test
     */
    public function setLabels(): void
    {
        $this->subject->processTemplate('a ###LABEL_FOO### b');

        $this->subject->setLabels();

        self::assertSame('a foo b', $this->subject->getSubpart());
    }

    /**
     * @test
     */
    public function setLabelsNoSalutation(): void
    {
        $this->subject->processTemplate('a ###LABEL_BAR### b');

        $this->subject->setLabels();

        self::assertSame('a bar (no salutation) b', $this->subject->getSubpart());
    }

    /**
     * @test
     */
    public function setLabelsFormal(): void
    {
        $this->subject->setSalutationMode('formal');
        $this->subject->processTemplate('a ###LABEL_BAR### b');

        $this->subject->setLabels();

        self::assertSame('a bar (formal) b', $this->subject->getSubpart());
    }

    /**
     * @test
     */
    public function setLabelsInformal(): void
    {
        $this->subject->setSalutationMode('informal');
        $this->subject->processTemplate('a ###LABEL_BAR### b');

        $this->subject->setLabels();

        self::assertSame('a bar (informal) b', $this->subject->getSubpart());
    }

    /**
     * @test
     */
    public function setLabelsWithOneBeingThePrefixOfAnother(): void
    {
        $this->subject->processTemplate('###LABEL_FOO###, ###LABEL_FOO2###');

        $this->subject->setLabels();

        self::assertSame('foo, foo two', $this->subject->getSubpart());
    }

    // Tests for getting subparts.

    /**
     * @test
     */
    public function getSubpartWithLabelsReturnsVerbatimSubpartWithoutLabels(): void
    {
        $subpartContent = 'Subpart content';
        $templateCode = 'Text before the subpart
            <!-- ###MY_SUBPART### -->'
            . $subpartContent
            . '<!-- ###MY_SUBPART### -->'
            . 'Text after the subpart.';

        $this->subject->processTemplate($templateCode);

        self::assertSame($subpartContent, $this->subject->getSubpartWithLabels('MY_SUBPART'));
    }

    /**
     * @test
     */
    public function getSubpartWithLabelsReplacesLabelMarkersWithLabels(): void
    {
        $templateCode = 'Text before the subpart
            <!-- ###MY_SUBPART### -->before ###LABEL_FOO### after<!-- ###MY_SUBPART### -->
            Text after the subpart.';

        $this->subject->processTemplate($templateCode);

        self::assertSame('before foo after', $this->subject->getSubpartWithLabels('MY_SUBPART'));
    }
}
