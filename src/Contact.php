<?php
/**
 * Single contact
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
 * Single contact
 *
 * @category ChatExporter
 * @package  WaChatExport
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
class Contact
{
    /**
     * The Contact's jID
     * 
     * @var string
     */
    private $_jID;

    /**
     * Get the Contact's jID
     *
     * @return string
     */
    public function getJID()
    {
        return $this->_jID;
    }

    /**
     * Set the Contact's jID
     *
     * @param string $_jID the Contact's jID
     *
     * @return self
     */
    public function setJID(string $_jID)
    {
        $this->_jID = $_jID;
        return $this;
    }

    /**
     * The Contact's display name
     * 
     * @var string
     */
    private $_displayName;

    /**
     * Get the Contact's display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->_displayName;
    }

    /**
     * Set the Contact's display name
     *
     * @param string $_displayName the Contact's display name
     *
     * @return self
     */
    public function setDisplayName(string $_displayName)
    {
        $this->_displayName = $_displayName;
        return $this;
    }
}