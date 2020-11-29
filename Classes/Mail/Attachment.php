<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mail;

/**
 * This class represents an e-mail attachment.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Attachment
{
    /**
     * @var string the file name of the attachment
     */
    private $fileName = '';

    /**
     * @var string the content type of the attachment
     */
    private $contentType = '';

    /**
     * @var string the content of the attachment
     */
    private $content = '';

    /**
     * Sets the file name of the attachment.
     *
     * @param string $fileName
     *        the file name of the attachment, must not be empty
     *
     * @return void
     */
    public function setFileName(string $fileName)
    {
        if ($fileName === '') {
            throw new \InvalidArgumentException('$fileName must not be empty.', 1331318400);
        }

        $this->fileName = $fileName;
    }

    /**
     * Returns the file name of the attachment.
     *
     * @return string the file name of the attachment, will be empty if not set
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Sets the content type of the attachment.
     *
     * @param string $contentType
     *        the content type of the attachment, must not be empty, e.g.,
     *        'text/plain', 'image/jpeg' or 'application/octet-stream'
     *
     * @return void
     */
    public function setContentType(string $contentType)
    {
        if ($contentType === '') {
            throw new \InvalidArgumentException('$contentType must not be empty.', 1331318411);
        }

        $this->contentType = $contentType;
    }

    /**
     * Returns the content type of the attachment.
     *
     * @return string the content type of the attachment, will be empty if not set
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Sets the content of the attachment.
     *
     * @param string $content
     *        the content of the attachment, may be empty
     *
     * @return void
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * Returns the content of the attachment.
     *
     * @return string the content of the attachment, might be empty
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
