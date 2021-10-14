<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Templating;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a registry for templates.
 */
class TemplateRegistry
{
    /**
     * @var TemplateRegistry|null the Singleton instance
     */
    private static $instance = null;

    /**
     * @var array<string, Template> already created templates (by file name)
     */
    private $templates = [];

    /**
     * The constructor. Use getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of this class.
     *
     * @return TemplateRegistry the current Singleton instance
     */
    public static function getInstance(): TemplateRegistry
    {
        if (!self::$instance) {
            self::$instance = new TemplateRegistry();
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
     * Creates a new template for a provided template file name with an already
     * parsed the template file.
     *
     * If the template file name is empty, no template file will be used for
     * that template.
     *
     * @param string $templateFileName the file name of the template to retrieve, may not be empty to get a template
     *        that is not related to a template file.
     *
     * @return Template the template for the given template file name
     *
     * @see getByFileName
     */
    public static function get(string $templateFileName): Template
    {
        return self::getInstance()->getByFileName($templateFileName);
    }

    /**
     * Creates a new template for a provided template file name with an already
     * parsed the template file.
     *
     * If the template file name is empty, no template file will be used for
     * that template.
     *
     * @param string $fileName the file name of the template to retrieve, may not be empty to get a template that
     *        is not related to a template file
     *
     * @return Template the template for the given template file name
     */
    public function getByFileName(string $fileName): Template
    {
        if (!isset($this->templates[$fileName])) {
            $template = GeneralUtility::makeInstance(Template::class);

            if ($fileName !== '') {
                $template->processTemplateFromFile($fileName);
            }
            $this->templates[$fileName] = $template;
        }

        return clone $this->templates[$fileName];
    }
}
