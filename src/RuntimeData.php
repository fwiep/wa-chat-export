<?php
/**
 * Runtime data
 *
 * PHP version 8
 *
 * @category RuntimeData
 * @package  WaChatExport
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
namespace FWieP;
use \IntlDateFormatter as IDF;
/**
 * Runtime data
 *
 * @category RuntimeData
 * @package  WaChatExport
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
class RuntimeData
{
    /**
     * The current instance of runtime data
     * 
     * @var RuntimeData
     */
    private static $_instance;

    /**
     * Current locale
     * 
     * @var string
     */
    public $locale;

    /**
     * The current timezone
     * 
     * @var \DateTimeZone
     */
    public $tz;

    /**
     * The UTC timezone
     * 
     * @var \DateTimeZone
     */
    public $tzUTC;

    /**
     * Date and time of the start of the request
     * 
     * @var \DateTime
     */
    public $dtNow;

    /**
     * Common date formatter with 'E dd-MM-y' pattern
     * 
     * @var \IntlDateFormatter
     */
    public $dtfmt;

    /**
     * Gets the current runtime data
     *
     * @return RuntimeData
     */
    public static function g() : self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Class constructor, private to prevent instantiation
     * 
     * @return RuntimeData
     */
    private function __construct()
    {
        $this->locale = 'nl_NL.utf8';
        $this->tz = new \DateTimeZone('Europe/Amsterdam');
        $this->tzUTC = new \DateTimeZone('UTC');
        $this->dtNow = new \DateTime('now', $this->tz);
        $this->dtfmt = new IDF(
            $this->locale, IDF::NONE, IDF::NONE,
            $this->tz, IDF::GREGORIAN, "yyyy-MM-dd HH:mm:ss xxx"
        );
    }
}