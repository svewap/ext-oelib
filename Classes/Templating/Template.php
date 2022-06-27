<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Templating;

use OliverKlee\Oelib\Exception\NotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an HTML template with markers (`###MARKER###`) and
 * subparts (`<!-- ###SUBPART### --><!-- ###SUBPART### -->`).
 */
class Template
{
    /**
     * @var non-empty-string the regular expression used to find subparts
     */
    private const SUBPART_PATTERN = '/<!-- *###([A-Z\\d_]+)###.*-->(.*)<!-- *###\\1###.*-->/msU';

    /**
     * @var string the complete HTML template
     */
    private $templateCode = '';

    /**
     * associative array of all HTML template subparts, using the uppercase marker names without `###` as keys,
     * for example "MY_MARKER"
     *
     * @var array<string, string>
     */
    private $subparts = [];

    /**
     * all lowercased label marker names in the current template without the hashes,
     * for example ("label_foo", "label_bar")
     *
     * @var array<int, non-empty-string>
     */
    private $labelMarkerNames = [];

    /**
     * associative array of *populated* markers and their contents
     * (with the keys being the marker names including the wrapping hash signs `###`).
     *
     * @var array<string, string>
     */
    private $markers = [];

    /**
     * Subpart names that shouldn't be displayed. Set a subpart key like "FIELD_DATE"
     * (the value does not matter) to remove that subpart.
     *
     * @var array<string, bool>
     */
    private $subpartsToHide = [];

    /**
     * Gets the HTML template in the file specified in the parameter $filename,
     * stores it and retrieves all subparts, writing them to $this->subparts.
     *
     * @param string $fileName the file name of the HTML template to process, must be an existing file, must not be
     *     empty
     */
    public function processTemplateFromFile(string $fileName): void
    {
        $fileContents = file_get_contents(GeneralUtility::getFileAbsFileName($fileName));
        if (\is_string($fileContents)) {
            $this->processTemplate($fileContents);
        }
    }

    /**
     * Stores the given HTML template and retrieves all subparts, writing them
     * to $this->subparts.
     *
     * The subpart names are automatically retrieved from $templateCode and
     * are used as array keys. For this, the ### are removed, but the names stay
     * uppercase.
     *
     * Example: The subpart `###MY_SUBPART###` will be stored with the array key
     * 'MY_SUBPART'.
     *
     * @param string $templateCode the content of the HTML template
     */
    public function processTemplate(string $templateCode): void
    {
        $this->templateCode = $templateCode;
        $this->extractSubparts($templateCode);
        $this->findMarkers();
    }

    /**
     * Recursively extracts all subparts from $templateCode and writes them to
     * $this->subparts.
     *
     * @param string $templateCode the template code to process, may be empty
     */
    private function extractSubparts(string $templateCode): void
    {
        // If there are no HTML comments in  the template code, there cannot be
        // any subparts. So there's no need to use an expensive regular
        // expression to find any subparts in that case.
        if (strpos($templateCode, '<!--') === false) {
            return;
        }

        $matches = [];
        preg_match_all(
            self::SUBPART_PATTERN,
            $templateCode,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            $subpartName = (string)$match[1];
            if (!isset($this->subparts[$subpartName])) {
                $subpartContent = (string)$match[2];
                $this->subparts[$subpartName] = $subpartContent;
                $this->extractSubparts($subpartContent);
            }
        }
    }

    /**
     * Finds all markers within the current HTML template and writes their names
     * to $this->markerNames.
     *
     * In addition, it stores the lowercased label marker names in $this->labelMarkerNames.
     */
    private function findMarkers(): void
    {
        $matches = [];

        preg_match_all(
            '/###([A-Z\\d_]+)###/',
            $this->templateCode,
            $matches
        );

        foreach (\array_unique($matches[1]) as $markerName) {
            /** @var non-empty-string $markerName */
            if (\strncmp($markerName, 'LABEL_', 6) === 0) {
                $this->labelMarkerNames[] = \strtolower($markerName);
            }
        }
    }

    /**
     * Gets a list of marker names with the "LABEL" prefix.
     *
     * If there are no matches, an empty array is returned.
     *
     * @return array<int, non-empty-string> matching marker names (lowercased), might be empty
     */
    public function getLabelMarkerNames(): array
    {
        return $this->labelMarkerNames;
    }

    /**
     * Sets a marker's content.
     *
     * Example: If the prefix is "field" and the marker name is "one", the
     * marker `###FIELD_ONE###` will be written.
     *
     * If the prefix is empty and the marker name is "one", the marker
     * "###ONE###" will be written.
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content the marker's content, may be empty
     * @param string $prefix prefix to the marker name (may be empty, case-insensitive, will get uppercased)
     */
    public function setMarker(string $markerName, $content, string $prefix = ''): void
    {
        $unifiedMarkerName = $this->createMarkerName($markerName, $prefix);

        if ($this->isMarkerNameValidWithHashes($unifiedMarkerName)) {
            $this->markers[$unifiedMarkerName] = (string)$content;
        }
    }

    /**
     * Gets a marker's content.
     *
     * @param non-empty-string $markerName the marker's name without the ### signs,
     *        case-insensitive, will get uppercased
     *
     * @return string the marker's content or an empty string if the marker has not been set before
     */
    public function getMarker(string $markerName): string
    {
        $unifiedMarkerName = $this->createMarkerName($markerName);

        return $this->markers[$unifiedMarkerName] ?? '';
    }

    /**
     * Sets a subpart's content.
     *
     * Example: If the prefix is "field" and the subpart name is "one", the
     * subpart "###FIELD_ONE###" will be written.
     *
     * If the prefix is empty and the subpart name is "one", the subpart
     * "###ONE###" will be written.
     *
     * @param non-empty-string $subpartName name without the ### signs, case-insensitive, will get uppercased
     * @param mixed $content the subpart's content, may be empty
     * @param string $prefix prefix to the subpart name (may be empty, case-insensitive, will get uppercased)
     */
    public function setSubpart(string $subpartName, $content, string $prefix = ''): void
    {
        $subpartName = $this->createMarkerNameWithoutHashes($subpartName, $prefix);

        if (!$this->isMarkerNameValidWithoutHashes($subpartName)) {
            throw new \InvalidArgumentException('The value of the parameter $subpartName is not valid.', 1331489182);
        }

        $this->subparts[$subpartName] = $content;
    }

    /**
     * Sets a marker based on whether the int content is non-zero.
     *
     * If (int)$content is non-zero, this function sets the marker's content, working
     * exactly like setMarker($markerName, $content, $markerPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE otherwise
     *
     * @see setMarkerIfNotEmpty
     */
    public function setMarkerIfNotZero(string $markerName, $content, string $markerPrefix = ''): bool
    {
        $condition = ((int)$content) !== 0;
        if ($condition) {
            $this->setMarker($markerName, (string)$content, $markerPrefix);
        }
        return $condition;
    }

    /**
     * Sets a marker based on whether the (string) content is non-empty.
     * If $content is non-empty, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content, $markerPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE otherwise
     *
     * @see setMarkerIfNotZero
     */
    public function setMarkerIfNotEmpty(string $markerName, $content, string $markerPrefix = ''): bool
    {
        $condition = (string)$content !== '';
        if ($condition) {
            $this->setMarker($markerName, $content, $markerPrefix);
        }

        return $condition;
    }

    /**
     * Checks whether a subpart is visible.
     *
     * Note: If the subpart to check does not exist, this function will return FALSE.
     *
     * @param string $subpartName name of the subpart to check (without the ###)
     *
     * @return bool TRUE if the subpart is visible, FALSE otherwise
     */
    public function isSubpartVisible(string $subpartName): bool
    {
        if ($subpartName === '') {
            return false;
        }

        return isset($this->subparts[$subpartName])
            && !isset($this->subpartsToHide[$subpartName]);
    }

    /**
     * Takes a comma-separated list of subpart names and sets them to hidden. In
     * the process, the names are changed from 'aname' to '###BLA_ANAME###' and
     * used as keys.
     *
     * Example: If the prefix is "field" and the list is "one,two", the subparts
     * "###FIELD_ONE###" and "###FIELD_TWO###" will be hidden.
     *
     * If the prefix is empty and the list is "one,two", the subparts
     * "###ONE###" and "###TWO###" will be hidden.
     *
     * @param string $subparts comma-separated list of the subparts to hide
     *        (case-insensitive, will get uppercased)
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function hideSubparts(string $subparts, string $prefix = ''): void
    {
        /** @var array<int, non-empty-string> $subpartNames */
        $subpartNames = GeneralUtility::trimExplode(',', $subparts, true);

        $this->hideSubpartsArray($subpartNames, $prefix);
    }

    /**
     * Takes an array of subpart names and sets them to hidden. In the process,
     * the names are changed from 'aname' to '###BLA_ANAME###' and used as keys.
     *
     * Example: If the prefix is "field" and the array has two elements "one"
     * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
     * hidden.
     *
     * If the prefix is empty and the array has two elements "one" and "two",
     * the subparts "###ONE###" and "###TWO###" will be hidden.
     *
     * @param array<string|int, non-empty-string> $subparts subpart names to hide
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function hideSubpartsArray(array $subparts, string $prefix = ''): void
    {
        foreach ($subparts as $currentSubpartName) {
            $fullSubpartName = $this->createMarkerNameWithoutHashes($currentSubpartName, $prefix);

            $this->subpartsToHide[$fullSubpartName] = true;
        }
    }

    /**
     * Takes a comma-separated list of subpart names and unhides them if they
     * have been hidden beforehand.
     *
     * Note: All subpartNames that are provided with the second parameter will
     * not be unhidden. This is to avoid unhiding subparts that are hidden by
     * the configuration.
     *
     * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
     *
     * Example: If the prefix is "field" and the list is "one,two", the subparts
     * "###FIELD_ONE###" and "###FIELD_TWO###" will be unhidden.
     *
     * If the prefix is empty and the list is "one,two", the subparts
     * "###ONE###" and "###TWO###" will be unhidden.
     *
     * @param string $subparts comma-separated list of subpart names to unhide (case-insensitive, will get uppercased),
     *        must not be empty
     * @param string $permanentlyHiddenSubparts comma-separated list of subpart names that shouldn't get unhidden
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function unhideSubparts(
        string $subparts,
        string $permanentlyHiddenSubparts = '',
        string $prefix = ''
    ): void {
        /** @var array<int, non-empty-string> $subpartNames */
        $subpartNames = GeneralUtility::trimExplode(',', $subparts, true);

        /** @var array<int, non-empty-string> $hiddenSubpartNames */
        $hiddenSubpartNames = GeneralUtility::trimExplode(',', $permanentlyHiddenSubparts, true);

        $this->unhideSubpartsArray($subpartNames, $hiddenSubpartNames, $prefix);
    }

    /**
     * Takes an array of subpart names and unhides them if they have been hidden
     * beforehand.
     *
     * Note: All subpartNames that are provided with the second parameter will
     * not be unhidden. This is to avoid unhiding subparts that are hidden by
     * the configuration.
     *
     * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
     *
     * Example: If the prefix is "field" and the array has two elements "one"
     * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
     * unhidden.
     *
     * If the prefix is empty and the array has two elements "one" and "two",
     * the subparts "###ONE###" and "###TWO###" will be unhidden.
     *
     * @param array<string|int, non-empty-string> $subparts subpart names to unhide
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string[] $permanentlyHiddenSubparts subpart names that shouldn't get unhidden
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function unhideSubpartsArray(
        array $subparts,
        array $permanentlyHiddenSubparts = [],
        string $prefix = ''
    ): void {
        foreach ($subparts as $currentSubpartName) {
            // Only unhide the current subpart if it is not on the list of
            // permanently hidden subparts (e.g. by configuration).
            if (!in_array($currentSubpartName, $permanentlyHiddenSubparts, true)) {
                $currentMarkerName = $this->createMarkerNameWithoutHashes($currentSubpartName, $prefix);
                unset($this->subpartsToHide[$currentMarkerName]);
            }
        }
    }

    /**
     * Sets or hides a marker based on $condition.
     * If $condition is TRUE, this function sets the marker's content, working
     * exactly like setMarker($markerName, $content, $markerPrefix).
     * If $condition is FALSE, this function removes the wrapping subpart,
     * working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive
     * @param bool $condition if this is TRUE, the marker will be filled, otherwise the wrapped marker will be hidden
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive)
     * @param string $wrapperPrefix prefix to the subpart name for hiding (may be empty, case-insensitive)
     *
     * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
     *
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarker(
        string $markerName,
        bool $condition,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        if ($condition) {
            $this->setMarker($markerName, $content, $markerPrefix);
        } else {
            $this->hideSubparts($markerName, $wrapperPrefix);
        }

        return $condition;
    }

    /**
     * Sets or hides a marker based on whether the int content is non-zero.
     *
     * If (int)$content is non-zero, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content,
     * $markerPrefix).
     * If (int)$condition is zero, this function removes the wrapping
     * subpart, working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive)
     * @param string $wrapperPrefix prefix to the subpart name for hiding (may be empty, case-insensitive)
     *
     * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
     *
     * @see setOrDeleteMarker
     * @see setOrDeleteMarkerIfNotEmpty
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarkerIfNotZero(
        string $markerName,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        return $this->setOrDeleteMarker(
            $markerName,
            ((int)$content) !== 0,
            (string)$content,
            $markerPrefix,
            $wrapperPrefix
        );
    }

    /**
     * Sets or hides a marker based on whether the (string) content is
     * non-empty.
     * If $content is non-empty, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content,
     * $markerPrefix).
     * If $condition is empty, this function removes the wrapping subpart,
     * working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix prefix to the subpart name for hiding
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if
     *                 the subpart has been hidden
     *
     * @see setOrDeleteMarker
     * @see setOrDeleteMarkerIfNotZero
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarkerIfNotEmpty(
        string $markerName,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        $contentIsNonEmpty = (string)$content !== '';

        return $this->setOrDeleteMarker(
            $markerName,
            $contentIsNonEmpty,
            $content,
            $markerPrefix,
            $wrapperPrefix
        );
    }

    /**
     * Creates an uppercase marker (or subpart) name from a given name and an
     * optional prefix, wrapping the result in three hash signs (###).
     *
     * Example: If the prefix is "field" and the marker name is "one", the
     * result will be "###FIELD_ONE###".
     *
     * If the prefix is empty and the marker name is "one", the result will be
     * "###ONE###".
     *
     * @param non-empty-string $markerName the name of the marker
     * @param string $prefix an optional prefix, may be empty
     *
     * @return non-empty-string the created marker name (including the hashes), will not be empty
     */
    private function createMarkerName(string $markerName, string $prefix = ''): string
    {
        return '###' . $this->createMarkerNameWithoutHashes($markerName, $prefix) . '###';
    }

    /**
     * Creates an uppercase marker (or subpart) name from a given name and an
     * optional prefix, but without wrapping it in hash signs.
     *
     * Example: If the prefix is "field" and the marker name is "one", the
     * result will be "FIELD_ONE".
     *
     * If the prefix is empty and the marker name is "one", the result will be
     * "ONE".
     *
     * @param non-empty-string $markerName the name of the marker
     * @param string $prefix an optional prefix, may be empty
     *
     * @return non-empty-string the created marker name (without the hashes)
     */
    private function createMarkerNameWithoutHashes(string $markerName, string $prefix = ''): string
    {
        // If a prefix is provided, uppercases it and separates it with an underscore.
        if ($prefix !== '') {
            $prefix .= '_';
        }

        /** @var non-empty-string $result */
        $result = \strtoupper($prefix . trim($markerName));

        return $result;
    }

    /**
     * Renders the complete template.
     *
     * @return string the rendered template, might be empty
     */
    public function render(): string
    {
        return $this->replaceMarkersAndSubparts($this->templateCode);
    }

    /**
     * Retrieves a named subpart, recursively filling in its inner subparts
     * and markers. Inner subparts that are marked to be hidden will be
     * substituted with empty strings.
     *
     * This function either works on the subpart with the name $key or the
     * complete HTML template if $key is an empty string.
     *
     * @param string $subpartKey key of an existing subpart, for example 'LIST_ITEM' (without the ###),
     *        or an empty string to use the complete HTML template
     *
     * @return string the subpart content or an empty string if the subpart is hidden or the subpart name is missing
     *
     * @throws \InvalidArgumentException if $subpartKey is not valid
     * @throws NotFoundException if there is no subpart with the provided name
     */
    public function getSubpart(string $subpartKey = ''): string
    {
        if ($subpartKey === '') {
            return $this->render();
        }
        if (!$this->isMarkerNameValidWithoutHashes($subpartKey)) {
            throw new \InvalidArgumentException('The value of the parameter $key is not valid.', 1331489215);
        }
        if (!isset($this->subparts[$subpartKey])) {
            throw new NotFoundException(
                '$key contained the subpart name "' . $subpartKey
                . '", but only the following subparts are available: (' .
                implode(', ', array_keys($this->subparts)) . ')',
                1632760625
            );
        }
        if (!$this->isSubpartVisible($subpartKey)) {
            return '';
        }

        return $this->replaceMarkersAndSubparts($this->subparts[$subpartKey]);
    }

    /**
     * Recursively replaces all subparts and markers in $templateCode.
     *
     * @param string $templateCode the template, may be empty
     *
     * @return string the template with all subparts and markers replaced
     */
    protected function replaceMarkersAndSubparts(string $templateCode): string
    {
        return $this->replaceMarkers($this->replaceSubparts($templateCode));
    }

    /**
     * Recursively replaces subparts with their contents.
     *
     * @param string $templateCode the template, may be empty
     *
     * @return string the template with the subparts replaced
     */
    protected function replaceSubparts(string $templateCode): string
    {
        $template = $this;
        return (string)\preg_replace_callback(
            self::SUBPART_PATTERN,
            static function (array $matches) use ($template): string {
                return $template->getSubpart($matches[1]);
            },
            $templateCode
        );
    }

    /**
     * Replaces all markers with their contents.
     *
     * @param string $templateCode the template, may be empty
     *
     * @return string the template with the markers replaced
     */
    protected function replaceMarkers(string $templateCode): string
    {
        return str_replace(array_keys($this->markers), $this->markers, $templateCode);
    }

    /**
     * Checks whether a marker name (or subpart name) is valid (including the
     * leading and trailing hashes ###).
     *
     * A valid marker name must be a non-empty string, consisting of uppercase
     * and lowercase letters ranging A to Z, digits and underscores. It must
     * start with a lowercase or uppercase letter ranging from A to Z. It must
     * not end with an underscore. In addition, it must be prefixed and suffixed
     * with ###.
     *
     * @param string $markerName marker name to check (with the hashes), may be empty
     *
     * @return bool TRUE if the marker name is valid, FALSE otherwise
     */
    private function isMarkerNameValidWithHashes(string $markerName): bool
    {
        return isset($this->markers[$markerName])
            || (bool)\preg_match('/^###[a-zA-Z](?:\\w*[a-zA-Z\\d])?###$/', $markerName);
    }

    /**
     * Checks whether a marker name (or subpart name) is valid (excluding the
     * leading and trailing hashes ###).
     *
     * A valid marker name must be a non-empty string, consisting of uppercase
     * and lowercase letters ranging A to Z, digits and underscores. It must
     * start with a lowercase or uppercase letter ranging from A to Z. It must
     * not end with an underscore.
     *
     * @param string $markerName marker name to check (without the hashes), may be empty
     *
     * @return bool TRUE if the marker name is valid, FALSE otherwise
     */
    private function isMarkerNameValidWithoutHashes(string $markerName): bool
    {
        return $this->isMarkerNameValidWithHashes('###' . $markerName . '###');
    }

    /**
     * Resets the list of subparts to hide.
     */
    public function resetSubpartsHiding(): void
    {
        $this->subpartsToHide = [];
    }
}
