<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Language;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;
use OliverKlee\Oelib\Language\Translator;
use OliverKlee\Oelib\Language\TranslatorRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Benjamin Schulte <benj@minschulte.de>
 */
class TranslatorRegistryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    protected function setUp()
    {
        parent::setUp();

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('config', new TypoScriptConfiguration());
        $configurationRegistry->set('page.config', new TypoScriptConfiguration());
        $configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG', new TypoScriptConfiguration());
        $configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.default', new TypoScriptConfiguration());
        $configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.de', new TypoScriptConfiguration());
        $configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.fr', new TypoScriptConfiguration());
    }

    private function setUpFrontEnd()
    {
        /** @var TypoScriptFrontendController|ProphecySubjectInterface $frontEndController */
        $frontEndController = $this->prophesize(TypoScriptFrontendController::class)->reveal();

        $GLOBALS['TSFE'] = $frontEndController;
    }

    private function setUpBackEnd()
    {
        $GLOBALS['LANG'] = new LanguageService();
        $this->setUpBackendUserFromFixture(1);
    }

    ////////////////////////////////////////////
    // Tests regarding the Singleton property.
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceReturnsTranslatorRegistryInstance()
    {
        $this->setUpFrontEnd();

        self::assertInstanceOf(
            TranslatorRegistry::class,
            TranslatorRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        $this->setUpFrontEnd();

        self::assertSame(
            TranslatorRegistry::getInstance(),
            TranslatorRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $this->setUpFrontEnd();

        $firstInstance = TranslatorRegistry::getInstance();
        TranslatorRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            TranslatorRegistry::getInstance()
        );
    }

    ///////////////////////////
    // Tests regarding get().
    ///////////////////////////

    /**
     * @test
     */
    public function getWithEmptyExtensionNameThrowsException()
    {
        $this->setUpFrontEnd();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter $extensionName must not be empty.');

        TranslatorRegistry::get('');
    }

    /**
     * @test
     */
    public function getWithNotLoadedExtensionNameThrowsException()
    {
        $this->setUpFrontEnd();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('The extension with the name "user_oelib_test_does_not_exist" is not loaded.');

        TranslatorRegistry::get('user_oelib_test_does_not_exist');
    }

    /**
     * @test
     */
    public function getWithLoadedExtensionNameReturnsTranslatorInstance()
    {
        $this->setUpFrontEnd();

        self::assertInstanceOf(Translator::class, TranslatorRegistry::get('oelib'));
    }

    /**
     * @test
     */
    public function getTwoTimesWithSameExtensionNameReturnsSameInstance()
    {
        $this->setUpFrontEnd();

        self::assertSame(TranslatorRegistry::get('oelib'), TranslatorRegistry::get('oelib'));
    }

    /////////////////////////////////////////
    // Tests regarding initializeBackEnd().
    /////////////////////////////////////////

    /**
     * @test
     */
    public function initializeBackEndWithBackEndUserLanguageEnglishSetsLanguageEnglish()
    {
        $this->setUpBackEnd();

        $backEndUser = new BackEndUser();
        $backEndUser->setDefaultLanguage('default');
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertSame(
            'default',
            TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     */
    public function initializeBackEndWithBackEndUserLanguageGermanSetsLanguageGerman()
    {
        $this->setUpBackEnd();

        $backEndUser = new BackEndUser();
        $backEndUser->setDefaultLanguage('de');
        BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertSame(
            'de',
            TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     */
    public function initializeBackEndDoesNotSetAlternativeLanguage()
    {
        $this->setUpBackEnd();

        self::assertSame(
            '',
            TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
        );
    }

    //////////////////////////////////////////
    // Tests regarding initializeFrontEnd().
    //////////////////////////////////////////

    /**
     * A data provider used for the front end configuration namespaces
     *
     * @return array[]
     */
    public function frontEndConfigurationDataProvider(): array
    {
        return [
            'config' => ['config'],
            'page.config' => ['page.config'],
        ];
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithoutFrontEndLanguageSetsLanguageDefault(string $namespace)
    {
        $this->setUpBackEnd();
        ConfigurationRegistry::get($namespace)->setData([]);

        self::assertSame(
            'default',
            TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithFrontEndLanguageEnglishSetsLanguageEnglish(string $namespace)
    {
        $this->setUpFrontEnd();
        ConfigurationRegistry::get($namespace)->setData(['language' => 'default']);

        self::assertSame(
            'default',
            TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithFrontEndLanguageGermanSetsLanguageGerman(string $namespace)
    {
        $this->setUpFrontEnd();
        ConfigurationRegistry::get($namespace)->setData(['language' => 'de']);

        self::assertSame(
            'de',
            TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithoutAlternativeFrontEndLanguageDoesNotSetAlternative(string $namespace)
    {
        $this->setUpFrontEnd();
        ConfigurationRegistry::get($namespace)->setData([]);

        self::assertSame(
            '',
            TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithAlternativeFrontEndLanguageEnglishSetsAlternativeEnglish(string $namespace)
    {
        $this->setUpFrontEnd();
        ConfigurationRegistry::get($namespace)->setData(['language' => 'de', 'language_alt' => 'default']);

        self::assertSame(
            'default',
            TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithAlternativeFrontEndLanguageGermanSetsAlternativeGerman(string $namespace)
    {
        $this->setUpFrontEnd();
        ConfigurationRegistry::get($namespace)->setData(['language' => 'default', 'language_alt' => 'de']);

        self::assertSame(
            'de',
            TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
        );
    }

    /**
     * @test
     */
    public function initializeFrontEndWithLanguageSetInConfigAndInPageConfigSetsLanguageFromPageConfig()
    {
        $this->setUpFrontEnd();
        ConfigurationRegistry::get('config')->setData(['language' => 'de']);
        ConfigurationRegistry::get('page.config')->setData(['language' => 'fr']);

        self::assertSame(
            'fr',
            TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     */
    public function initializeFrontEndWithAlternativeLanguageSetInConfigAndInPageConfigUsesFromPageConfig()
    {
        $this->setUpFrontEnd();
        ConfigurationRegistry::get('config')->setData(['language' => 'de', 'language_alt' => 'cz']);
        ConfigurationRegistry::get('page.config')->setData(['language' => 'fr', 'language_alt' => 'ja']);

        self::assertSame(
            'ja',
            TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
        );
    }

    //////////////////////////////////////////
    // Tests regarding getByExtensionName().
    //////////////////////////////////////////

    /**
     * @test
     */
    public function getByExtensionNameLoadsLabelsFromFile()
    {
        $this->setUpFrontEnd();

        self::assertSame(
            'I am from file.',
            TranslatorRegistry::get('oelib')->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function getByExtensionNameInFrontEndOverridesLabelsFromFileWithLabelsFromTypoScript()
    {
        $this->setUpFrontEnd();

        ConfigurationRegistry::get('config')->set('language', 'default');
        ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->setData(['default.' => []]);
        ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG.default')
            ->set('label_test', 'I am from TypoScript.');

        self::assertSame(
            'I am from TypoScript.',
            TranslatorRegistry::get('oelib')->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function getByExtensionNameInBackEndNotOverridesLabelsFromFileWithLabelsFromTypoScript()
    {
        $this->setUpBackEnd();

        ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->setData(['default.' => []]);
        ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG.default')
            ->set('label_test', 'I am from TypoScript.');

        self::assertSame(
            'I am from file.',
            TranslatorRegistry::get('oelib')->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function getByExtensionNameDoesNotDeleteLanguageLabelsNotAffectedByTypoScript()
    {
        $this->setUpFrontEnd();
        ConfigurationRegistry::get('config')->set('language', 'default');
        ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->setData(['default.' => []]);
        ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG.default')
            ->set('label_test_2', 'I am from TypoScript.');

        self::assertSame(
            'I am from file.',
            TranslatorRegistry::get('oelib')->translate('label_test')
        );
    }

    /////////////////////////////////////
    // Tests concerning the languageKey
    /////////////////////////////////////

    /**
     * @test
     */
    public function getLanguageKeyForSetKeyReturnsSetKey()
    {
        $this->setUpFrontEnd();
        TranslatorRegistry::getInstance()->setLanguageKey('de');

        self::assertSame(
            'de',
            TranslatorRegistry::getInstance()->getLanguageKey()
        );
    }

    /**
     * @test
     */
    public function setLanguageKeyForEmptyStringGivenThrowsException()
    {
        $this->setUpFrontEnd();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given language key must not be empty.');

        TranslatorRegistry::getInstance()->setLanguageKey('');
    }
}
