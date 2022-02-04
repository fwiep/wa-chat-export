<?php
/**
 * Single chat message
 *
 * PHP version 8
 *
 * @category ChatExporter
 * @package  WaChatExport
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
namespace FWieP;
/**
 * Single chat message
 *
 * @category ChatExporter
 * @package  WaChatExport
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
class Message
{
    /**
     * Whether this message was sent by me
     * 
     * @var bool
     */
    private $_fromMe = false;

    /**
     * Get whether this message was sent by me
     *
     * @return bool
     */
    public function isFromMe()
    {
        return $this->_fromMe;
    }

    /**
     * Set whether this message was sent by me
     *
     * @param bool $_fromMe Whether this message was sent by me
     *
     * @return self
     */
    public function setFromMe(bool $_fromMe)
    {
        $this->_fromMe = $_fromMe;
        return $this;
    }

    /**
     * Whether this message was forwarded to me
     * 
     * @var bool
     */
    private $_forwarded = false;

    /**
     * Get whether this message was forwarded to me
     *
     * @return bool
     */
    public function isForwarded()
    {
        return $this->_forwarded;
    }

    /**
     * Set whether this message was forwarded to me
     *
     * @param bool $_forwarded Whether this message was forwarded to me
     *
     * @return self
     */
    public function setForwarded(bool $_forwarded)
    {
        $this->_forwarded = $_forwarded;
        return $this;
    }

    /**
     * Whether this is a message in a group chat
     * 
     * @var bool
     */
    private $_inGroup = false;

    /**
     * Get whether this is a message in a group chat
     *
     * @return bool
     */
    public function isInGroup()
    {
        return $this->_inGroup;
    }

    /**
     * Set whether this is a message in a group chat
     *
     * @param bool $_inGroup whether this is a message in a group chat
     *
     * @return self
     */
    public function setInGroup(bool $_inGroup)
    {
        $this->_inGroup = $_inGroup;
        return $this;
    }

    /**
     * The Message's remote contact
     * 
     * @var Contact
     */
    private $_remote;

    /**
     * Get the Message's remote contact
     *
     * @return Contact|NULL
     */
    public function getRemote()
    {
        return $this->_remote;
    }

    /**
     * Set the Message's remote contact
     *
     * @param Contact|NULL $_remote the Message's remote contact
     *
     * @return self
     */
    public function setRemote(?Contact $_remote)
    {
        $this->_remote = $_remote;
        return $this;
    }

    /**
     * The date and time the message was received
     * 
     * @var \DateTime
     */
    private $_receivedAt;

    /**
     * Get the date and time the message was received
     *
     * @return \DateTime
     */
    public function getReceivedAt()
    {
        return $this->_receivedAt;
    }

    /**
     * Set the date and time the message was received
     *
     * @param \DateTime $_receivedAt the date and time the message was received
     *
     * @return self
     */
    public function setReceivedAt(\DateTime $_receivedAt)
    {
        $this->_receivedAt = $_receivedAt;
        return $this;
    }

    /**
     * The message body text
     * 
     * @var string
     */
    private $_text;

    /**
     * Get the message body text
     *
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * Set the message body text
     *
     * @param string $_text the message body text
     *
     * @return self
     */
    public function setText(string $_text)
    {
        $this->_text = $_text;
        return $this;
    }

    /**
     * The Message's group contact, if any
     * 
     * @var Contact
     */
    private $_group;

    /**
     * Get the Message's group contact, if any
     *
     * @return Contact
     */
    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * Set the Message's group contact, if any
     *
     * @param Contact|NULL $_group the Message's group contact, if any
     *
     * @return self
     */
    public function setGroup(?Contact $_group)
    {
        $this->_group = $_group;
        return $this;
    }
}