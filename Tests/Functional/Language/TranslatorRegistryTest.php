<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Functional\Language;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
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

        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('config', new \Tx_Oelib_Configuration());
        $configurationRegistry->set('page.config', new \Tx_Oelib_Configuration());
        $configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG', new \Tx_Oelib_Configuration());
        $configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.default', new \Tx_Oelib_Configuration());
        $configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.de', new \Tx_Oelib_Configuration());
        $configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.fr', new \Tx_Oelib_Configuration());
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

    /**
     * Returns the current front-end instance.
     *
     * @return TypoScriptFrontendController
     */
    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
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
            \Tx_Oelib_TranslatorRegistry::class,
            \Tx_Oelib_TranslatorRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        $this->setUpFrontEnd();

        self::assertSame(
            \Tx_Oelib_TranslatorRegistry::getInstance(),
            \Tx_Oelib_TranslatorRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $this->setUpFrontEnd();

        $firstInstance = \Tx_Oelib_TranslatorRegistry::getInstance();
        \Tx_Oelib_TranslatorRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            \Tx_Oelib_TranslatorRegistry::getInstance()
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

        \Tx_Oelib_TranslatorRegistry::get('');
    }

    /**
     * @test
     */
    public function getWithNotLoadedExtensionNameThrowsException()
    {
        $this->setUpFrontEnd();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('The extension with the name "user_oelib_test_does_not_exist" is not loaded.');

        \Tx_Oelib_TranslatorRegistry::get('user_oelib_test_does_not_exist');
    }

    /**
     * @test
     */
    public function getWithLoadedExtensionNameReturnsTranslatorInstance()
    {
        $this->setUpFrontEnd();

        self::assertInstanceOf(\Tx_Oelib_Translator::class, \Tx_Oelib_TranslatorRegistry::get('oelib'));
    }

    /**
     * @test
     */
    public function getTwoTimesWithSameExtensionNameReturnsSameInstance()
    {
        $this->setUpFrontEnd();

        self::assertSame(\Tx_Oelib_TranslatorRegistry::get('oelib'), \Tx_Oelib_TranslatorRegistry::get('oelib'));
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

        $backEndUser = new \Tx_Oelib_Model_BackEndUser();
        $backEndUser->setDefaultLanguage('default');
        \Tx_Oelib_BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertSame(
            'default',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     */
    public function initializeBackEndWithBackEndUserLanguageGermanSetsLanguageGerman()
    {
        $this->setUpBackEnd();

        $backEndUser = new \Tx_Oelib_Model_BackEndUser();
        $backEndUser->setDefaultLanguage('de');
        \Tx_Oelib_BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

        self::assertSame(
            'de',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
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
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
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
    public function initializeFrontEndWithoutFrontEndLanguageSetsLanguageDefault($namespace)
    {
        $this->setUpBackEnd();
        \Tx_Oelib_ConfigurationRegistry::get($namespace)->setData([]);

        self::assertSame(
            'default',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithFrontEndLanguageEnglishSetsLanguageEnglish($namespace)
    {
        $this->setUpFrontEnd();
        \Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(['language' => 'default']);

        self::assertSame(
            'default',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithFrontEndLanguageGermanSetsLanguageGerman($namespace)
    {
        $this->setUpFrontEnd();
        \Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(['language' => 'de']);

        self::assertSame(
            'de',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithoutAlternativeFrontEndLanguageDoesNotSetAlternativeLanguage($namespace)
    {
        $this->setUpFrontEnd();
        \Tx_Oelib_ConfigurationRegistry::get($namespace)->setData([]);

        self::assertSame(
            '',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithAlternativeFrontEndLanguageEnglishSetsAlternativeLanguageEnglish($namespace)
    {
        $this->setUpFrontEnd();
        \Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(['language' => 'de', 'language_alt' => 'default']);

        self::assertSame(
            'default',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
        );
    }

    /**
     * @test
     *
     * @param string $namespace the configuration namespace
     *
     * @dataProvider frontEndConfigurationDataProvider
     */
    public function initializeFrontEndWithAlternativeFrontEndLanguageGermanSetsAlternativeLanguageGerman($namespace)
    {
        $this->setUpFrontEnd();
        \Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(['language' => 'default', 'language_alt' => 'de']);

        self::assertSame(
            'de',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
        );
    }

    /**
     * @test
     */
    public function initializeFrontEndWithLanguageSetInConfigAndInPageConfigSetsLanguageFromPageConfig()
    {
        $this->setUpFrontEnd();
        \Tx_Oelib_ConfigurationRegistry::get('config')->setData(['language' => 'de']);
        \Tx_Oelib_ConfigurationRegistry::get('page.config')->setData(['language' => 'fr']);

        self::assertSame(
            'fr',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
        );
    }

    /**
     * @test
     */
    public function initializeFrontEndWithAlternativeLanguageSetInConfigAndInPageConfigSetsAlternativeLanguageFromPageConfig(
    ) {
        $this->setUpFrontEnd();
        \Tx_Oelib_ConfigurationRegistry::get('config')->setData(['language' => 'de', 'language_alt' => 'cz']);
        \Tx_Oelib_ConfigurationRegistry::get('page.config')->setData(['language' => 'fr', 'language_alt' => 'ja']);

        self::assertSame(
            'ja',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
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
            \Tx_Oelib_TranslatorRegistry::get('oelib')->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function getByExtensionNameInFrontEndOverridesLabelsFromFileWithLabelsFromTypoScript()
    {
        $this->setUpFrontEnd();
        $this->getFrontEndController()->initLLvars();

        \Tx_Oelib_ConfigurationRegistry::get('config')->set('language', 'default');
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->setData(['default.' => []]);
        \Tx_Oelib_ConfigurationRegistry::
        get('plugin.tx_oelib._LOCAL_LANG.default')->set('label_test', 'I am from TypoScript.');

        self::assertSame(
            'I am from TypoScript.',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function getByExtensionNameInBackEndNotOverridesLabelsFromFileWithLabelsFromTypoScript()
    {
        $this->setUpBackEnd();

        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->setData(['default.' => []]);
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG.default')
            ->set('label_test', 'I am from TypoScript.');

        self::assertSame(
            'I am from file.',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function getByExtensionNameDoesNotDeleteLanguageLabelsNotAffectedByTypoScript()
    {
        $this->setUpFrontEnd();
        $this->getFrontEndController()->initLLvars();
        \Tx_Oelib_ConfigurationRegistry::get('config')->set('language', 'default');
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->setData(['default.' => []]);
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG.default')
            ->set('label_test_2', 'I am from TypoScript.');

        self::assertSame(
            'I am from file.',
            \Tx_Oelib_TranslatorRegistry::get('oelib')->translate('label_test')
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
        \Tx_Oelib_TranslatorRegistry::getInstance()->setLanguageKey('de');

        self::assertSame(
            'de',
            \Tx_Oelib_TranslatorRegistry::getInstance()->getLanguageKey()
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

        \Tx_Oelib_TranslatorRegistry::getInstance()->setLanguageKey('');
    }
}
