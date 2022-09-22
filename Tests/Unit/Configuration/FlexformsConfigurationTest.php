<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\FlexformsConfiguration;
use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Configuration\FlexformsConfiguration
 */
final class FlexformsConfigurationTest extends UnitTestCase
{
    private function buildContentObjectWithXmlFlexformsData(string $key, string $value): ContentObjectRenderer
    {
        $flexformsXml = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
                         <T3FlexForms>
                             <data>
                                 <sheet index="sDEF">
                                     <language index="lDEF">
                                         <field index="' . $key . '">
                                             <value index="vDEF">' . $value . '</value>
                                         </field>
                                     </language>
                                 </sheet>
                             </data>
                         </T3FlexForms>';

        $contentObject = new ContentObjectRenderer();
        $contentObject->data = ['pi_flexform' => $flexformsXml];

        return $contentObject;
    }

    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function buildContentObjectWithArrayFlexformsData(array $data): ContentObjectRenderer
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = ['pi_flexform' => $data];

        return $contentObject;
    }

    /**
     * @test
     */
    public function implementsConfigurationInterface(): void
    {
        $subject = new FlexformsConfiguration(new ContentObjectRenderer());

        self::assertInstanceOf(ConfigurationInterface::class, $subject);
    }

    /**
     * @test
     */
    public function isReadOnlyObjectWithPublicAccessors(): void
    {
        $subject = new FlexformsConfiguration(new ContentObjectRenderer());

        self::assertInstanceOf(AbstractReadOnlyObjectWithPublicAccessors::class, $subject);
    }

    /**
     * @test
     */
    public function hasFlexformsName(): void
    {
        $subject = new FlexformsConfiguration(new ContentObjectRenderer());

        self::assertSame('in the plugin Flexforms', $subject->getSourceName());
    }

    /**
     * @return array<string, array<array<string, string|array<string, mixed>|null>>>
     */
    public function noFlexformsDataDataProvider(): array
    {
        return [
            'no flexforms data field' => [[]],
            'null' => [['pi_flexform' => null]],
            'empty string' => [['pi_flexform' => '']],
            'non-XML string' => [['pi_flexform' => 'The cake is a lie.']],
            'non-flexforms XML string' => [
                ['pi_flexform' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?><html></html>'],
            ],
            'empty array' => [['pi_flexform' => []]],
            'empty data array (without any sheets)' => [['pi_flexform' => ['data' => []]]],
            'data array, empty sDEF sheet' => [['pi_flexform' => ['data' => ['sDEF' => []]]]],
            'data array, sDEF sheet, empty language' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => []]]]],
            ],
            'data array, sDEF sheet, language, empty key contents' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['flavor' => []]]]]],
            ],
            'data array, sDEF sheet, language, key, empty vDEF' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['flavor' => ['vDEF' => '']]]]]],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider noFlexformsDataDataProvider
     *
     * @param array<array<string, string|array<string, mixed>|null>> $contentObjectData
     */
    public function getAsStringForNoFlexformsDataReturnsEmptyString(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertSame('', $subject->getAsString('flavor'));
    }

    /**
     * @test
     * @dataProvider noFlexformsDataDataProvider
     *
     * @param array<array<string, string|null>> $contentObjectData
     */
    public function getAsIntegerForNoFlexformsDataReturnsZero(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertSame(0, $subject->getAsInteger('flavor'));
    }

    /**
     * @test
     * @dataProvider noFlexformsDataDataProvider
     *
     * @param array<array<string, string|null>> $contentObjectData
     */
    public function getAsBooleanForNoFlexformsDataReturnsFalse(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertFalse($subject->getAsBoolean('flavor'));
    }

    /**
     * @return array<string, array<array<string, string>>>
     */
    public function noFieldsDataDataProvider(): array
    {
        return [
            'flexforms without any fields' => [
                [
                    'pi_flexform' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
                                      <T3FlexForms>
                                          <data>
                                              <sheet index="sDEF">
                                                  <language index="lDEF"></language>
                                              </sheet>
                                          </data>
                                      </T3FlexForms>',
                ],
            ],
        ];
    }

    /**
     * @test
     *
     * @param array<string, string> $contentObjectData
     * @dataProvider noFieldsDataDataProvider
     */
    public function getAsStringForMissingFieldReturnsEmptyString(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertSame('', $subject->getAsString('flavor'));
    }

    /**
     * @test
     *
     * @param array<string, string> $contentObjectData
     * @dataProvider noFieldsDataDataProvider
     */
    public function getAsIntegerForMissingFieldReturnsZero(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertSame(0, $subject->getAsInteger('flavor'));
    }

    /**
     * @test
     *
     * @param array<string, string> $contentObjectData
     * @dataProvider noFieldsDataDataProvider
     */
    public function getAsBooleanForMissingFieldReturnsFalse(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertFalse($subject->getAsBoolean('flavor'));
    }

    /**
     * @return array<string, array<array<string, string>>>
     */
    public function emptyFieldDataDataProvider(): array
    {
        return [
            'flexforms with with empty "flavor" field' => [
                [
                    'pi_flexform' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
                                      <T3FlexForms>
                                          <data>
                                              <sheet index="sDEF">
                                                  <language index="lDEF">
                                                      <field index="flavor">
                                                          <value index="vDEF"></value>
                                                      </field>
                                                  </language>
                                              </sheet>
                                          </data>
                                      </T3FlexForms>',
                ],
            ],
        ];
    }

    /**
     * @test
     *
     * @param array<string, string> $contentObjectData
     * @dataProvider emptyFieldDataDataProvider
     */
    public function getAsStringForEmptyFieldReturnsEmptyString(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertSame('', $subject->getAsString('flavor'));
    }

    /**
     * @test
     *
     * @param array<string, string> $contentObjectData
     * @dataProvider emptyFieldDataDataProvider
     */
    public function getAsIntegerForEmptyFieldReturnsZero(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertSame(0, $subject->getAsInteger('flavor'));
    }

    /**
     * @test
     *
     * @param array<string, string> $contentObjectData
     * @dataProvider emptyFieldDataDataProvider
     */
    public function getAsBooleanForEmptyFieldReturnsFalse(array $contentObjectData): void
    {
        $contentObject = new ContentObjectRenderer();
        $contentObject->data = $contentObjectData;
        $subject = new FlexformsConfiguration($contentObject);

        self::assertFalse($subject->getAsBoolean('flavor'));
    }

    /**
     * @test
     */
    public function getAsStringForExistingNonEmptyFieldInXmlReturnsValueFromField(): void
    {
        $key = 'flavor';
        $value = 'hazelnut';
        $subject = new FlexformsConfiguration($this->buildContentObjectWithXmlFlexformsData($key, $value));

        self::assertSame($value, $subject->getAsString($key));
    }

    /**
     * @return array<string, array<int, array<string, array<string, array<string, array<string, mixed>>>>>>
     */
    public function stringValueInArrayDataProvider(): array
    {
        return [
            'default array key names' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['catName' => ['vDEF' => 'Clyde']]]]]],
            ],
            'missing sheet level' => [
                ['pi_flexform' => ['data' => ['de' => ['catName' => ['vDEF' => 'Clyde']]]]],
            ],
            'different sheet name' => [
                ['pi_flexform' => ['data' => ['general' => ['de' => ['catName' => ['vDEF' => 'Clyde']]]]]],
            ],
            'different language' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['en' => ['catName' => ['vDEF' => 'Clyde']]]]]],
            ],
            'different field key' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['catName' => ['contents' => 'Clyde']]]]]],
            ],
        ];
    }

    /**
     * @test
     *
     * @param array<string, array<string, array<string, array<string, array<string, array<string, mixed>>>>>> $data
     * @dataProvider stringValueInArrayDataProvider
     */
    public function getAsStringForExistingNonEmptyFieldInArrayReturnsValueFromField(array $data): void
    {
        $subject = new FlexformsConfiguration($this->buildContentObjectWithArrayFlexformsData($data));

        self::assertSame('Clyde', $subject->getAsString('catName'));
    }

    /**
     * @test
     */
    public function getAsIntegerForExistingNonEmptyFieldInXmlReturnsValueFromField(): void
    {
        $key = 'size';
        $value = 4;
        $subject = new FlexformsConfiguration($this->buildContentObjectWithXmlFlexformsData($key, (string)$value));

        self::assertSame($value, $subject->getAsInteger($key));
    }

    /**
     * @return array<string, array<int, array<string, array<string, array<string, array<string, mixed>>>>>>
     */
    public function integerValueInArrayDataProvider(): array
    {
        return [
            'default array key names with string' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['numberOfCats' => ['vDEF' => '17']]]]]],
            ],
            'default array key names with int' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['numberOfCats' => ['vDEF' => 17]]]]]],
            ],
            'missing sheet level' => [
                ['pi_flexform' => ['data' => ['de' => ['numberOfCats' => ['vDEF' => '17']]]]],
            ],
            'different sheet name' => [
                ['pi_flexform' => ['data' => ['general' => ['de' => ['numberOfCats' => ['vDEF' => '17']]]]]],
            ],
            'different language' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['en' => ['numberOfCats' => ['vDEF' => '17']]]]]],
            ],
            'different field key' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['numberOfCats' => ['contents' => '17']]]]]],
            ],
        ];
    }

    /**
     * @test
     *
     * @param array<string, array<string, array<string, array<string, array<string, array<string, mixed>>>>>> $data
     * @dataProvider integerValueInArrayDataProvider
     */
    public function getAsIntegerForExistingNonEmptyFieldInArrayReturnsValueFromField(array $data): void
    {
        $subject = new FlexformsConfiguration($this->buildContentObjectWithArrayFlexformsData($data));

        self::assertSame(17, $subject->getAsInteger('numberOfCats'));
    }

    /**
     * @test
     */
    public function getAsBooleanForExistingNonEmptyFieldInXmlReturnsValueFromField(): void
    {
        $key = 'hasCats';
        $subject = new FlexformsConfiguration($this->buildContentObjectWithXmlFlexformsData($key, '1'));

        self::assertTrue($subject->getAsBoolean($key));
    }

    /**
     * @return array<string, array<int, array<string, array<string, array<string, array<string, mixed>>>>>>
     */
    public function booleanValueInArrayDataProvider(): array
    {
        return [
            'default array key names with string' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['hasCats' => ['vDEF' => '1']]]]]],
            ],
            'default array key names with int' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['hasCats' => ['vDEF' => 1]]]]]],
            ],
            'missing sheet level' => [
                ['pi_flexform' => ['data' => ['de' => ['hasCats' => ['vDEF' => '1']]]]],
            ],
            'different sheet name' => [
                ['pi_flexform' => ['data' => ['general' => ['de' => ['hasCats' => ['vDEF' => '1']]]]]],
            ],
            'different language' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['en' => ['hasCats' => ['vDEF' => '1']]]]]],
            ],
            'different field key' => [
                ['pi_flexform' => ['data' => ['sDEF' => ['de' => ['hasCats' => ['contents' => '1']]]]]],
            ],
        ];
    }

    /**
     * @test
     *
     * @param array<string, array<string, array<string, array<string, array<string, array<string, string>>>>>> $data
     * @dataProvider booleanValueInArrayDataProvider
     */
    public function getAsBooleanForExistingNonEmptyFieldInArrayReturnsValueFromField(array $data): void
    {
        $subject = new FlexformsConfiguration($this->buildContentObjectWithArrayFlexformsData($data));

        self::assertTrue($subject->getAsBoolean('hasCats'));
    }
}
