<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Email\Fixtures;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithAccessors;
use OliverKlee\Oelib\Interfaces\MailRole;

/**
 * This class represents an e-mail role, e.g., a sender or a recipient.
 */
class TestingMailRole extends AbstractObjectWithAccessors implements MailRole
{
    /**
     * @var string[] the data of this object
     */
    private $data = [];

    /**
     * The constructor. Sets the name and the e-mail address of the e-mail role.
     *
     * @param string $name the name of the e-mail role, may be empty
     * @param string $email the e-mail address of the e-mail role, may be empty
     */
    public function __construct(string $name, string $email)
    {
        $this->setName($name);
        $this->setEmailAddress($email);
    }

    /**
     * Sets the value of the data item for the key $key.
     *
     * @param string $key
     *        the key of the data item to set, must not be empty
     * @param mixed $value
     *        the data for the key $key
     *
     * @return void
     */
    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Gets the value of the data item for the key $key.
     *
     * @param string $key
     *        the key of the data item to get, must not be empty
     *
     * @return mixed the data for the key $key, will be an empty string
     *               if the key has not been set yet
     */
    protected function get(string $key): string
    {
        return $this->data[$key] ?? '';
    }

    /**
     * Returns the real name of the e-mail role.
     *
     * @return string the real name of the e-mail role, might be empty
     */
    public function getName(): string
    {
        return $this->getAsString('name');
    }

    /**
     * Sets the real name of the e-mail role.
     *
     * @param string $name
     *        the real name of the e-mail role, may be empty
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->setAsString('name', $name);
    }

    /**
     * Returns the e-mail address of the e-mail role.
     *
     * @return string the e-mail address of the e-mail role, might be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * Sets the e-mail address of the e-mail role.
     *
     * @param string $email
     *        the e-mail address of the e-mail role, may be empty
     *
     * @return void
     */
    public function setEmailAddress(string $email)
    {
        $this->setAsString('email', $email);
    }
}
