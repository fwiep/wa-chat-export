<?php
/**
 * Chat export functionality
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
use FWieP\RuntimeData as RD;
use FWieP\Utils as U;
/**
 * Chat export functionality
 *
 * @category ChatExporter
 * @package  WaChatExport
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
class ChatExporter
{
    private static $_exportFileHeader = <<<b8a38c2a67185f5d7f25
; WA Chat Export v%s
; Creates a readable export of WhatsApp's chat history
;
; Export started: %s
; Group chat: %s
;
; Participants in this chat:
%s

b8a38c2a67185f5d7f25;

    /**
     * Creates a new ChatExporter
     * 
     * @param string $abDbFilename    the addressbook DB filename
     * @param string $msgDbFilename   the message DB filename
     * @param string $mePhoneNumber   the international phone number of 'me'
     * @param string $outputDirectory the output directory
     */
    public function __construct(
        string $abDbFilename, string $msgDbFilename, string $mePhoneNumber,
        string $outputDirectory
    ) {
        $meJID = trim($mePhoneNumber, "+")."@s.whatsapp.net";
        $this->_outputDirecory = $outputDirectory;

        $wa = new \SQLite3($abDbFilename, SQLITE3_OPEN_READONLY);
        $res = $wa->query(
            "SELECT `jid`, `display_name`, `wa_name` FROM `wa_contacts`"
        );
        // Add contacts to local address book
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            if (!empty($row['display_name']) || !empty($row['wa_name'])) {
                $n = $row['wa_name'] ? '('.$row['wa_name'].')' : null;
                
                $c = (new Contact())
                    ->setJID($row['jid'])
                    ->setDisplayName($row['display_name'] ?? $n);
                $this->addContactToAddressBook($c);

                if ($row['jid'] == $meJID) {
                    $this->setMe($c);
                }
            }
        }
        $wa->close();

        // The 'me' Contact could not be found; exit.
        if (!$this->getMe()) {
            return;
        }
        $db = new \SQLite3($msgDbFilename, SQLITE3_OPEN_READONLY);
        $res = $db->query(
            "SELECT
                `J`.`raw_string`,
                `C`.`subject`
            FROM
                `chat` AS `C`
                JOIN `jid` AS `J` ON `C`.`jid_row_id` = `J`.`_id`"
        );
        // Add group chats as separate contacts to the address book
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            if (!empty($row['raw_string']) && !empty($row['subject'])) {
                $this->addContactToAddressBook(
                    (new Contact())
                        ->setJID($row['raw_string'])
                        ->setDisplayName($row['subject'])
                );
            }
        }
        // Loop through address book...
        foreach ($this->_addressBook as $jID => $contact) {
            $stmt = $db->prepare(
                "SELECT
                    `key_remote_jid`,
                    `key_from_me`,
                    `data`,
                    `remote_resource`,
                    `media_caption`,
                    `forwarded`,
                    `timestamp`
                FROM
                    `messages`
                WHERE
                    `key_remote_jid` == :jid
                    AND `status` != 6
                ORDER BY
                    `key_remote_jid` ASC,
                    `timestamp` ASC"
            );
            $stmt->bindValue(':jid', $jID, SQLITE3_TEXT);
            if ($res = $stmt->execute()) {
                // ... and fetch this contact's messages
                while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                    $msg = (new Message())
                        ->setFromMe($row['key_from_me'] == 1)
                        ->setForwarded($row['forwarded'] == 1)
                        ->setInGroup(
                            (strpos($row['key_remote_jid'], '-') !== false)
                        );
                    $remoteJid = $row['key_remote_jid'];
                    $groupJid = null;
                    
                    if ($msg->isInGroup()) {
                        $remoteJid = $row['remote_resource'];
                        $groupJid = $row['key_remote_jid'];
                    }
                    $remoteContact = null;
                    if (!$msg->isFromMe()) {
                        $remoteContact = $this->_findContact($remoteJid)
                            ?? (new Contact())
                            ->setJID($remoteJid)
                            ->setDisplayName(self::_jidToNumber($remoteJid));
                    }
                    $groupContact = $this->_findContact($groupJid);
                    $dt = (new \DateTime())
                        ->setTimestamp(intdiv($row['timestamp'], 1000));
                    $fw = ($msg->isForwarded() ? '(forwarded) ' : '');
                    $mediaCaption = ' '.($row['media_caption'] ?? '');
                    $text = $fw.($row['data'] ?? '(media file)'.$mediaCaption);
                    $msg
                        ->setRemote($remoteContact)
                        ->setGroup($groupContact)
                        ->setReceivedAt($dt)
                        ->setText($text);
                    // Add the message to this contact's chats
                    $this->addMessage($contact, $msg);
                }
            }
        }
        $db->close();
    }

    /**
     * Exports all (group-)chats to separate text files on disk
     * 
     * @return int the amount of chats exported
     */
    public function exportAllChats() : int
    {
        $x = 0;
        foreach ($this->_addressBook as $jID => $contact) {
            if ($contact !== $this->_me) {
                $o = $this->exportSingleChat($jID);
                if (!empty($o)) {
                    $ok =file_put_contents(
                        $this->_outputDirecory.DIRECTORY_SEPARATOR.$jID.'.txt',
                        $o
                    );
                    if ($ok !== false) {
                        $x++;
                    }
                }
            }
        }
        return $x;
    }

    /**
     * Exports a single (group-)chat conversation
     * 
     * @param string $jID the Contact's jID
     * 
     * @return string
     */
    public function exportSingleChat(string $jID) : string
    {
        if (!array_key_exists($jID, $this->_messages)) {
            return '';
        }
        $o = '';
        $msgs = $this->_messages[$jID];
        $top1msg = reset($msgs);
        
        $participants = [];
        $participants[$this->_me->getJID()] = $this->_me;

        if ($top1msg->isInGroup()) {
            foreach ($msgs as $m) {
                if ($m->getRemote()) {
                    $participants[$m->getRemote()->getJID()] = $m->getRemote();    
                }
            }
        } else {
            $participants[$jID] = $this->_findContact($jID);
        }
        uasort(
            $participants, function (Contact $c1, Contact $c2) {
                return strcmp($c1->getDisplayName(), $c2->getDisplayName());
            }
        );
        $participantsString = '';
        $maxDisplayNameLength = 0;
        
        array_walk(
            $participants,
            function (Contact $c, string $jid) use (
                &$participantsString, &$maxDisplayNameLength
            ) {
                $participantsString .= sprintf(
                    ';  %s: %s%s',
                    self::_jidToNumber($jid),
                    $c->getDisplayName(),
                    PHP_EOL
                );
                $senderStringLength = mb_strlen($c->getDisplayName());
                if ($maxDisplayNameLength < $senderStringLength) {
                    $maxDisplayNameLength = $senderStringLength;
                }
            }
        );
        $o .= U::sprintf(
            self::$_exportFileHeader,
            PROG_VERSION,
            RD::g()->dtfmt->format(new \DateTime('now', RD::g()->tz)),
            ($top1msg->isInGroup() ? $top1msg->getGroup()->getDisplayName() : 'no'),
            $participantsString
        );
        foreach ($msgs as $m) {
            $indent = 29 + $maxDisplayNameLength + 2;
            $indentSpaces = str_repeat(' ', $indent);
            $o .= U::sprintf(
                '[%s] %'.$maxDisplayNameLength.'s: %s%s',
                RD::g()->dtfmt->format($m->getReceivedAt()),
                $m->isFromMe()
                    ? $this->_me->getDisplayName()
                    : $m->getRemote()->getDisplayName(),
                str_replace("\n", "\n".$indentSpaces, $m->getText()),
                PHP_EOL
            );
        }
        $o .= PHP_EOL;
        return $o;
    }

    /**
     * Converts a WhatsApp-jID to an international telephone number
     * 
     * @param string $jid the JID
     * 
     * @return string
     */
    private static function _jidToNumber(string $jid) : string
    {
        return '+'.explode('@', $jid)[0];
    }

    /**
     * Finds a contact in the collection by it's jID
     * 
     * @param string|NULL $jID the jID
     * 
     * @return Contact|NULL
     */
    private function _findContact(?string $jID) : ?Contact
    {
        if (!empty($jID)) {
            foreach ($this->_addressBook as $ixJID => $c) {
                if ($jID == $ixJID) {
                    return $c;
                }
            }
        }
        return null;
    }

    /**
     * The address book
     * 
     * @var Contact[]
     */
    private $_addressBook = [];

    /**
     * Get the address book
     *
     * @return Contact[]
     */
    public function getAddressBook() : array
    {
        return $this->_addressBook;
    }

    /**
     * Adds a contact to the address book
     * 
     * @param Contact $c the Contact
     * 
     * @return self
     */
    protected function addContactToAddressBook(Contact $c) : self
    {
        $this->_addressBook[$c->getJID()] = $c;
        return $this;
    }

    /**
     * The exporting person's Contact
     * 
     * @var Contact
     */
    private $_me;

    /**
     * Get the exporting person's Contact
     *
     * @return Contact|NULL
     */
    public function getMe() : ?Contact
    {
        return $this->_me;
    }

    /**
     * Set the exporting person's Contact
     *
     * @param Contact $_me the exporting person's Contact
     *
     * @return self
     */
    protected function setMe(Contact $_me) : self
    {
        $this->_me = $_me;
        return $this;
    }

    /**
     * The message collection, keyed by Message's remote-jID
     * 
     * @var array
     */
    private $_messages = [];

    /**
     * Get the message collection, keyed by Message's remote-jID
     *
     * @return array
     */
    public function getMessages() : array
    {
        return $this->_messages;
    }

    /**
     * Adds a Message to the Contact's collection
     * 
     * @param Contact $c contact
     * @param Message $m message
     * 
     * @return self
     */
    protected function addMessage(Contact $c, Message $m) : self
    {
        $this->_messages[$c->getJID()][] = $m;
        return $this;
    }

    /**
     * The output directory
     * 
     * @var string
     */
    private $_outputDirecory;
}