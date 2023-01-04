#!/usr/bin/env php
<?php
/**
 * Main entry point for commandline (CLI) execution
 *
 * PHP version 8
 *
 * @category MainEntry
 * @package  WhatsappView
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
use FWieP\ChatExporter;
use FWieP\RuntimeData as RD;
require __DIR__.'/vendor/autoload.php';

define('PROG_VERSION', '0.2');
$validSQLite3mimes = ['application/x-sqlite3', 'application/vnd.sqlite3'];
$wacDBfile = null;
$msgDBfile = null;
$mePhone = null;
$outDir = null;

$outHeader = sprintf(
    <<<b8a38c2a67185f5d7f25
WA Chat Export v%s
Chat-export tool for unencrypted WhatsApp databases


b8a38c2a67185f5d7f25,
    PROG_VERSION,
);

$outUsage = sprintf(
    <<<b8a38c2a67185f5d7f25
Usage: %s OPTIONS

[ -h | --help ]              show this help message and exit
[ -a | --addressdb= ] FILE   path to WhatsApp addressbook file
[ -m | --messagedb= ] FILE   path to WhatsApp database file
[ -n | --number= ] NUMBER    phonenumber of the exporting user, e.g. +31612345678 
[ -o | --outdir= ] DIRECTORY output folder, defaults to parent of database file


b8a38c2a67185f5d7f25,
    basename($argv[0])
);
$opts = getopt(
    'ha:m:n:o:',
    ['help', 'addressdb:', 'messagedb:', 'number:', 'outdir:']
);
if (array_intersect_key(['h' => 0, 'help' => 0], $opts)) {
    print $outHeader;
    print $outUsage;
    exit(0);
}
if (array_intersect_key(['a' => 0, 'addressdb' => 0], $opts)) {
    $wacDBfile = array_key_exists('a', $opts) ? $opts['a'] : $opts['addressdb'];

    if (!file_exists($wacDBfile) || !is_readable($wacDBfile)) {
        print $outHeader;
        fwrite(
            STDERR,
            "The addressbook file does not exist, or could not be read.".
            PHP_EOL."Exiting.".PHP_EOL
        );
        exit(1);
    }
    if (!in_array(mime_content_type($wacDBfile), $validSQLite3mimes)) {
        print $outHeader;
        fwrite(
            STDERR,
            "The addressbook file is not an SQLite3 database.".
            PHP_EOL."Exiting.".PHP_EOL
        );
        exit(2);
    }
}
if (array_intersect_key(['m' => 0, 'messagedb' => 0], $opts)) {
    $msgDBfile = array_key_exists('m', $opts) ? $opts['m'] : $opts['messagedb'];

    if (!file_exists($msgDBfile) || !is_readable($msgDBfile)) {
        print $outHeader;
        fwrite(
            STDERR,
            "The database file does not exist, or could not be read.".
            PHP_EOL."Exiting.".PHP_EOL
        );
        exit(3);
    }
    if (!in_array(mime_content_type($msgDBfile), $validSQLite3mimes)) {
        print $outHeader;
        fwrite(
            STDERR,
            "The database file is not an SQLite3 database.".
            PHP_EOL."Exiting.".PHP_EOL
        );
        exit(4);
    }
}
if (array_intersect_key(['n' => 0, 'number' => 0], $opts)) {
    $mePhone = array_key_exists('n', $opts) ? $opts['n'] : $opts['number'];

    if (preg_match('!^\+?[0-9]+$!', $mePhone) != 1) {
        print $outHeader;
        fwrite(
            STDERR,
            "The phonenumber was not in the correct format, e.g. '+31612345678'.".
            PHP_EOL."Exiting.".PHP_EOL
        );
        exit(5);
    }
}
if (empty($wacDBfile) || empty($msgDBfile) || empty($mePhone)) {
    print $outHeader;
    print $outUsage;

    fwrite(
        STDERR,
        "Not all required arguments were provided.".
        PHP_EOL."Exiting.".PHP_EOL
    );
    exit(6);
}
if (array_intersect_key(['o' => 0, 'outdir' => 0], $opts)) {
    $outDir = array_key_exists('o', $opts) ? $opts['o'] : $opts['outdir'];
} else {
    $outDir = dirname($msgDBfile);
}
if (!is_dir($outDir) || !is_writable($outDir)) {
    print $outHeader;
    fwrite(
        STDERR,
        "The output directory does not exist, or isn't writable.".
        PHP_EOL."Exiting.".PHP_EOL
    );
    exit(7);
}
$outDir .= DIRECTORY_SEPARATOR.RD::g()->dtNow->format('Y-m-d_H-i-s');

if (!is_dir($outDir) && !mkdir($outDir)) {
    print $outHeader;
    fwrite(
        STDERR,
        "The output directory could not be created.".
        PHP_EOL."Exiting.".PHP_EOL
    );
    exit(8);
}
$ce = new ChatExporter($wacDBfile, $msgDBfile, $mePhone, $outDir);
print $outHeader;
printf(
    '%d chat(s) exported successfully.%s',
    $ce->exportAllChats(),
    PHP_EOL
);
exit(0);
