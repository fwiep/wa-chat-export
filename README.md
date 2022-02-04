# WA Chat Export

Chat-export tool for unencrypted WhatsApp databases

With this script, you can export all chats and group-chats from a WhatsApp
database to a readable and searchable plaintext file format. The script reads
the SQLite files `msgstore.db` and `wa.db` which provide the actual messages
and the contact's names, respectively. The exported per-chat text files are
placed in a timestamped folder.

Note: Only message text is exported. For media files, if there is a caption,
that is exported, too. If a message or media file was forwarded, the export says
so. Media file thumbnails are *not* exported, though stored in the database.

---

**Warning**: Working with unencrypted databases and plain text exports defeats
the purpose of secure messaging. Please do **not** use this script for
exporting private or privacy-critical chats.

---

## Usage

Copy the unencrypted databases from your mobile device.  
Personally, I boot my unrooted device to [TWRP Recovery][4], connect via USB and
then issue the following commands:

```sh
adb pull /data/data/com.whatsapp/databases/msgstore.db
adb pull /data/data/com.whatsapp/databases/wa.db
```

In your terminal or console session, call the export script:

```plain
$ ./wa-chat-export.php --help

WA Chat Export v0.1
Chat-export tool for unencrypted WhatsApp databases

Usage: ./wa-chat-export.php OPTIONS

[ -h | --help ]              show this help message and exit
[ -a | --addressdb= ] FILE   path to WhatsApp addressbook file
[ -m | --messagedb= ] FILE   path to WhatsApp database file
[ -n | --number= ] NUMBER    phonenumber of the exporting user, e.g. +31612345678 
[ -o | --outdir= ] DIRECTORY output folder, defaults to parent of database file
```

## Example

Using short options:

```sh
./wa-chat-export.php -a wa.db -m msgstore.db -n "+31612345678"
```

Or using corresponding long options:

```sh
./wa-chat-export.php --addressdb=wa.db --messagedb=msgstore.db --number="+31612345678"
```

After a while, the script finishes and reports the number of exported chats.

```plain
216 chat(s) exported successfully.
```

A possible single chat export might look like this:

```plain
; WA Chat Export v0.1
; Creates a readable export of WhatsApp's chat history
;
; Export started: 2022-02-03 17:19:08 +01:00
; Group chat: no
;
; Participants in this chat:
;  +31612345678: Alice
;  +31687654321: Bob

[2016-07-12 08:16:32 +02:00] Alice: Hi Bob, how are you?
[2016-07-12 10:09:36 +02:00]   Bob: Hi.
[2016-07-26 09:37:42 +02:00]   Bob: I'm fine, how's your cat doing?
[2016-07-26 09:38:27 +02:00] Alice: (media file) Photo of my cat
[2016-07-26 09:39:25 +02:00]   Bob: Waw!
...
```

## Installation

1. Install [`git`][3], [`php`][1] and [`composer`][2]
1. Clone this repository  
   `git clone https://github.com/fwiep/wa-chat-export.git`
1. Navigate into the cloned repository  
   `cd wa-chat-export`
1. Install dependencies  
   `composer install`

## Afterthoughts

Of course, the point of any end-to-end encrypted communication is to keep the
sent and receivved messages secure and private. Working with unencrypted
databases and usage of an export-tool like this is not in line with that effort.

Therefore, please look at this script as a tool for your convenience. It enables
you to easily review and search your chat's contents. After these actions, be
sure to securely delete the exported files and unencrypted databases.

## Alternatives

When preparing this project for publication on GitHub, I discovered [WhaPa][5],
a platform independent toolset for WhatsApp forensics, written in Python 3. It
provides a lot more functionality than this script does, so feel free to pay
them a visit and enjoy their work!

[1]: https://www.php.net/manual/en/install.php
[2]: https://getcomposer.org/download/
[3]: https://git-scm.com/downloads
[4]: https://twrp.me/
[5]: https://github.com/B16f00t/whapa
[6]: https://github.com/ElDavoo/WhatsApp-Crypt14-Decrypter