<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Email;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Email\Mail;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class MailTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var Mail
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new Mail();
    }

    // Tests regarding setting and getting the CSS file.

    /**
     * @test
     */
    public function setCssFileForNoCssFileGivenDoesNotSetCssFile()
    {
        $this->subject->setCssFile('');

        self::assertFalse(
            $this->subject->hasCssFile()
        );
    }

    /**
     * @test
     */
    public function setCssFileForStringGivenWhichIsNoFileDoesNotSetCssFile()
    {
        $this->subject->setCssFile('foo');

        self::assertFalse(
            $this->subject->hasCssFile()
        );
    }

    /**
     * @test
     */
    public function setCssFileForGivenCssFileWithAbsolutePathSetsCssFile()
    {
        $this->subject
            ->setCssFile(ExtensionManagementUtility::extPath('oelib') . 'Tests/Functional/Email/Fixtures/test.css');

        self::assertTrue(
            $this->subject->hasCssFile()
        );
    }

    /**
     * @test
     */
    public function setCssFileForGivenCssFileWithAbsoluteExtPathSetsCssFile()
    {
        $this->subject->setCssFile('EXT:oelib/Tests/Functional/Email/Fixtures/test.css');

        self::assertTrue(
            $this->subject->hasCssFile()
        );
    }

    /**
     * @test
     */
    public function setCssFileForGivenCssFileStoresContentsOfCssFile()
    {
        $this->subject->setCssFile('EXT:oelib/Tests/Functional/Email/Fixtures/test.css');

        self::assertContains(
            'h3',
            $this->subject->getCssFile()
        );
    }

    /**
     * @test
     */
    public function setCssFileForSetCssFileAndThenGivenEmptyStringClearsStoredCssFileData()
    {
        $this->subject->setCssFile('EXT:oelib/Tests/Functional/Email/Fixtures/test.css');
        $this->subject->setCssFile('');

        self::assertFalse(
            $this->subject->hasCssFile()
        );
    }

    /**
     * @test
     */
    public function setCssFileForSetCssFileAndThenGivenNewCssFileRemovesOldCssDataFromStorage()
    {
        $this->subject->setCssFile('EXT:oelib/Tests/Functional/Email/Fixtures/test.css');
        $this->subject->setCssFile('EXT:oelib/Tests/Functional/Email/Fixtures/test_2.css');

        self::assertNotContains(
            'h3',
            $this->subject->getCssFile()
        );
    }

    /**
     * @test
     */
    public function setCssFileForSetCssFileAndThenGivenNewCssFileStoresNewCssData()
    {
        $this->subject->setCssFile('EXT:oelib/Tests/Functional/Email/Fixtures/test.css');
        $this->subject->setCssFile('EXT:oelib/Tests/Functional/Email/Fixtures/test_2.css');

        self::assertContains(
            'h4',
            $this->subject->getCssFile()
        );
    }

    // Tests concerning the emogrification of the HTML Messages and the CSS file

    /**
     * @test
     */
    public function setHtmlMessageWithNoCssFileStoredOnlyStoresTheHtmlMessage()
    {
        $htmlMessage =
            '<html>' .
            '<head><title>foo</title></head>' .
            '<body><h3>Bar</h3></body>' .
            '</html>';
        $this->subject->setHTMLMessage($htmlMessage);

        self::assertSame(
            $htmlMessage,
            $this->subject->getHTMLMessage()
        );
    }

    /**
     * @test
     */
    public function setHtmlMessageWithCssFileStoredStoresAttributesFromCssInHtmlMessage()
    {
        $this->subject->setCssFile('EXT:oelib/Tests/Functional/Email/Fixtures/test.css');
        $this->subject->setHTMLMessage(
            '<!DOCTYPE html>' .
            '<html>' .
            '<head><title>foo</title></head>' .
            '<body><h3>Bar</h3></body>' .
            '</html>'
        );

        self::assertContains(
            '<h3 style="font-weight: bold;">Bar</h3>',
            $this->subject->getHTMLMessage()
        );
    }
}
