<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class provides access to the configuration in the Flexforms of the given content object,
 * but not to TypoScript configuration. It can access data from any Flexforms sheet (without the need to
 * provide the sheet name for the access).
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FlexformsConfiguration extends AbstractObjectWithPublicAccessors implements ConfigurationInterface
{
    /**
     * @var \DOMDocument|null
     */
    private $xmlDocument = null;

    /**
     * @var \DOMXPath|null
     */
    private $xPath;

    public function __construct(ContentObjectRenderer $contentObject)
    {
        $flexFormsXml = (string)($contentObject->data['pi_flexform'] ?? '');
        if ($flexFormsXml === '') {
            return;
        }

        $this->parseXmlIntoDocument($flexFormsXml);
    }

    /**
     * @return void
     */
    private function parseXmlIntoDocument(string $flexFormsXml)
    {
        $document = new \DOMDocument();
        $libXmlState = \libxml_use_internal_errors(true);
        $document->loadXML($flexFormsXml);
        if (\libxml_get_errors() === []) {
            $this->xmlDocument = $document;
            $this->xPath = new \DOMXPath($document);
        }

        \libxml_clear_errors();
        \libxml_use_internal_errors($libXmlState);
    }

    /**
     * @return string
     */
    protected function get(string $key): string
    {
        if (!$this->xPath instanceof \DOMXPath) {
            return '';
        }

        $matchingNodes = $this->xPath->query("/T3FlexForms/data/sheet/language/field[@index='{$key}']/value");
        $firstMatchingNode = $matchingNodes instanceof \DOMNodeList ? $matchingNodes->item(0) : null;

        return $firstMatchingNode instanceof \DOMNode ? (string)$firstMatchingNode->textContent : '';
    }

    /**
     * Sets nothing as this class is a read-only accessor for Flexforms configuration.
     *
     * @param string $key
     * @param mixed $value
     *
     * @throws \BadMethodCallException
     */
    protected function set($key, $value)
    {
        throw new \BadMethodCallException('This is a read-only configuration. You cannot set any values.', 1612002594);
    }
}
