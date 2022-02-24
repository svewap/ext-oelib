<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class provides an abstraction for selecting a page in the FE or BE.
 */
class PageFinder
{
    /**
     * @var int
     */
    private const SOURCE_AUTO = 0;

    /**
     * @var int
     */
    public const SOURCE_FRONT_END = 1;

    /**
     * @var int
     */
    public const SOURCE_BACK_END = 2;

    /**
     * @var int
     */
    public const SOURCE_MANUAL = 3;

    /**
     * @var int
     */
    public const NO_SOURCE_FOUND = 4;

    /**
     * @var PageFinder|null the Singleton instance
     */
    private static $instance = null;

    /**
     * @var int the manually set page UID
     */
    private $storedPageUid = 0;

    /**
     * @var int the source the page is retrieved from
     */
    private $manualPageUidSource = self::SOURCE_AUTO;

    /**
     * Don't call this constructor; use getInstance instead.
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of this class.
     *
     * @return PageFinder the current Singleton instance
     */
    public static function getInstance(): PageFinder
    {
        if (!self::$instance) {
            self::$instance = new PageFinder();
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new instance.
     */
    public static function purgeInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Returns the UID of the current page.
     *
     * If manualPageUidSource is set to SOURCE_FRONT_END or SOURCE_BACK_END, this
     * function returns the UID set in this part. Otherwise starts with looking
     * into the manually set page UID, then if a FE page UID is present
     * and finally if a BE page UID is present.
     *
     * @return int the ID of the current page, will be zero if no page is
     *                 present or no page source could be found
     */
    public function getPageUid(): int
    {
        switch ($this->getCurrentSource()) {
            case self::SOURCE_MANUAL:
                $result = $this->storedPageUid;
                break;
            case self::SOURCE_FRONT_END:
                $controller = $this->getFrontEndController();
                $result = $controller instanceof TypoScriptFrontendController ? (int)$controller->id : 0;
                break;
            case self::SOURCE_BACK_END:
                $result = (int)GeneralUtility::_GP('id');
                break;
            default:
                $result = 0;
        }

        return $result;
    }

    /**
     * Manually sets a page UID which always will be returned by getPageUid.
     *
     * @param int $uidToStore the page UID to store manually, must be > 0
     */
    public function setPageUid(int $uidToStore): void
    {
        if ($uidToStore <= 0) {
            throw new \InvalidArgumentException(
                'The given page UID was "' . $uidToStore . '". Only integer values greater than zero are allowed.',
                1331489010
            );
        }
        $this->storedPageUid = $uidToStore;
    }

    /**
     * Forces the getPageUid function to get the page UID from a specific
     * source, ignoring an empty value or the original precedence.
     *
     * @param int $modeToForce SOURCE_BACK_END or SOURCE_FRONT_END
     */
    public function forceSource(int $modeToForce): void
    {
        $this->manualPageUidSource = $modeToForce;
    }

    /**
     * Returns the current source for the page UID.
     *
     * @return int either SOURCE_BACK_END, SOURCE_FRONT_END or SOURCE_MANUAL,
     *                 will be NO_SOURCE_FOUND if no source could be detected
     */
    public function getCurrentSource(): int
    {
        if ($this->manualPageUidSource !== self::SOURCE_AUTO) {
            $result = $this->manualPageUidSource;
        } elseif ($this->hasManualPageUid()) {
            $result = self::SOURCE_MANUAL;
        } elseif ($this->hasFrontEnd()) {
            $result = self::SOURCE_FRONT_END;
        } elseif ($this->hasBackEnd()) {
            $result = self::SOURCE_BACK_END;
        } else {
            $result = self::NO_SOURCE_FOUND;
        }

        return $result;
    }

    /**
     * Checks whether a front end (with a non-zero page UID) is present.
     *
     * @return bool TRUE if there is a front end with a non-zero page UID,
     *                 FALSE otherwise
     */
    private function hasFrontEnd(): bool
    {
        $frontEndController = $this->getFrontEndController();

        return $frontEndController instanceof TypoScriptFrontendController && $frontEndController->id > 0;
    }

    /**
     * Checks whether a back-end page UID has been set.
     *
     * @return bool TRUE if a back-end page UID has been set, FALSE otherwise
     */
    private function hasBackEnd(): bool
    {
        return (int)GeneralUtility::_GP('id') > 0;
    }

    /**
     * Checks whether a manual page UID has been set.
     *
     * @return bool TRUE if a page UID has been set manually, FALSE otherwise
     */
    private function hasManualPageUid(): bool
    {
        return $this->storedPageUid > 0;
    }

    /**
     * Returns the current front-end instance.
     */
    protected function getFrontEndController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
