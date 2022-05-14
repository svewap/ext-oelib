<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Testing\TestingFramework;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a registry for mappers. The mappers must be located in
 * the directory Mapper/ in each extension. Extension can use mappers from
 * other extensions as well.
 */
class MapperRegistry
{
    /**
     * @var MapperRegistry|null the Singleton instance
     */
    private static $instance = null;

    /**
     * @var array<class-string, AbstractDataMapper<AbstractModel>> already created mappers (by class name)
     */
    private $mappers = [];

    /**
     * @var bool whether database access should be denied for mappers
     */
    private $denyDatabaseAccess = false;

    /**
     * @var bool whether this MapperRegistry is used in testing mode
     */
    private $testingMode = false;

    /**
     * @var TestingFramework the testingFramework to use in testing mode
     */
    private $testingFramework = null;

    /**
     * The constructor. Use getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of this class.
     *
     * @return MapperRegistry the current Singleton instance
     */
    public static function getInstance(): MapperRegistry
    {
        if (!self::$instance instanceof MapperRegistry) {
            self::$instance = new MapperRegistry();
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new
     * instance.
     */
    public static function purgeInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Retrieves a dataMapper by class name.
     *
     * @template M of AbstractDataMapper
     *
     * @param class-string<M> $className the name of an existing mapper class, must not be empty
     *
     * @return M the mapper instance of the provided class
     *
     * @throws \InvalidArgumentException if there is no such mapper
     *
     * @see getByClassName
     */
    public static function get(string $className): AbstractDataMapper
    {
        return self::getInstance()->getByClassName($className);
    }

    /**
     * Retrieves a dataMapper by class name.
     *
     * @template M of AbstractDataMapper
     *
     * @param class-string<M> $className the name of an existing mapper class, must not be empty
     *
     * @return M the mapper instance of the provided class
     *
     * @throws \InvalidArgumentException if there is no such mapper
     */
    private function getByClassName(string $className): AbstractDataMapper
    {
        // @phpstan-ignore-next-line We explicitly check for contract violations here.
        if ($className === '') {
            throw new \InvalidArgumentException('$className must not be empty.', 1331488868);
        }

        if (isset($this->mappers[$className])) {
            /** @var M $mapper */
            $mapper = $this->mappers[$className];
        } else {
            if (!\class_exists($className)) {
                throw new \InvalidArgumentException('No mapper class "' . $className . '" could be found.', 1632844178);
            }

            $mapper = GeneralUtility::makeInstance($className);
            $this->mappers[$className] = $mapper;
        }

        if ($this->testingMode) {
            $mapper->setTestingFramework($this->testingFramework);
        }
        if ($this->denyDatabaseAccess) {
            $mapper->disableDatabaseAccess();
        }

        return $mapper;
    }

    /**
     * Disables database access for all mappers received with `get()`.
     */
    public static function denyDatabaseAccess(): void
    {
        self::getInstance()->denyDatabaseAccess = true;
    }

    /**
     * Activates the testing mode. This automatically will activate the testing mode for all future mappers.
     */
    public function activateTestingMode(TestingFramework $testingFramework): void
    {
        $this->testingMode = true;
        $this->testingFramework = $testingFramework;
    }

    /**
     * Sets a mapper that can be returned via get.
     *
     * This function is a static public convenience wrapper for setByClassName.
     *
     * This function is to be used for testing purposes only.
     *
     * @template M of AbstractDataMapper
     *
     * @param class-string<M> $className the class name of the mapper to set
     * @param M $mapper the mapper to set, must be an instance of `$className`
     *
     * @see setByClassName
     */
    public static function set(string $className, AbstractDataMapper $mapper): void
    {
        self::getInstance()->setByClassName($className, $mapper);
    }

    /**
     * Sets a mapper that can be returned via get.
     *
     * This function is to be used for testing purposes only.
     *
     * @template M of AbstractDataMapper
     *
     * @param class-string<M> $className the class name of the mapper to set
     * @param M $mapper the mapper to set, must be an instance of `$className`
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    private function setByClassName(string $className, AbstractDataMapper $mapper): void
    {
        if (!$mapper instanceof $className) {
            throw new \InvalidArgumentException(
                'The provided mapper is not an instance of ' . $className . '.',
                1331488915
            );
        }
        if (isset($this->mappers[$className])) {
            throw new \BadMethodCallException(
                'There already is a ' . $className . ' mapper registered. ' .
                ' Overwriting existing wrappers is not allowed.',
                1331488928
            );
        }

        $this->mappers[$className] = $mapper;
    }
}
