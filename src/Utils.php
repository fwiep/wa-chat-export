<?php
/**
 * Utility functions
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
/**
 * Utility functions
 *
 * @category Utils
 * @package  WaChatExport
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
abstract class Utils
{
    /**
     * Multi-byte sprintf
     * 
     * Source: https://www.php.net/manual/en/function.sprintf.php#89020
     * 
     * @param string $format the format specifier
     *
     * @return string
     */
    public static function sprintf($format)
    {
        $argv = func_get_args();
        array_shift($argv);
        return self::vsprintf($format, $argv);
    }
    /**
     * Multi-byte vsprintf
     * 
     * Source: https://www.php.net/manual/en/function.sprintf.php#89020
     * 
     * Works with all encodings in format and arguments.
     * Supported: Sign, padding, alignment, width and precision.
     * Not supported: Argument swapping.
     * 
     * @param string $format   the format specifier
     * @param array  $argv     the arguments
     * @param string $encoding the text encoding
     * 
     * @return string
     */
    public static function vsprintf(
        string $format, array $argv, string $encoding = null
    ) : string {

        if (is_null($encoding)) {
            $encoding = mb_internal_encoding();
        }
        // Use UTF-8 in the format so we can use the u flag in preg_split
        $format = mb_convert_encoding($format, 'UTF-8', $encoding);

        $newformat = ""; // build a new format in UTF-8
        $newargv = []; // unhandled args in unchanged encoding

        while ($format !== "") {

            // Split the format in two parts: $pre and $post by the first %-directive
            // We get also the matched groups
            // Suppress errors (@) for formats ending in one or more newlines
            @list($pre, $sign, $filler, $align, $size, $precision, $type, $post)
                = preg_split(
                    "!
                    %
                    (\+?)
                    ('.|[0 ]|)
                    (-?)
                    ([1-9][0-9]*|)
                    (\.[1-9][0-9]*|)
                    ([%a-zA-Z])
                    !ux",
                    $format,
                    2,
                    PREG_SPLIT_DELIM_CAPTURE
                );
            $newformat .= mb_convert_encoding($pre, $encoding, 'UTF-8');

            if ($type == '') {
                // didn't match. do nothing. this is the last iteration.
            } elseif ($type == '%') {
                // an escaped %
                $newformat .= '%%';
            } elseif ($type == 's') {
                $arg = array_shift($argv);
                $arg = mb_convert_encoding($arg, 'UTF-8', $encoding);
                $padding_pre = '';
                $padding_post = '';

                // truncate $arg
                if ($precision !== '') {
                    $precision = intval(substr($precision, 1));
                    if ($precision > 0 && mb_strlen($arg, $encoding) > $precision) {
                        $arg = mb_substr($precision, 0, $precision, $encoding);
                    }
                }
                // define padding
                if ($size > 0) {
                    $arglen = mb_strlen($arg, $encoding);
                    if ($arglen < $size) {
                        if ($filler === '') {
                            $filler = ' ';
                        }
                        if ($align == '-') {
                            $padding_post = str_repeat($filler, $size - $arglen);
                        } else {
                            $padding_pre = str_repeat($filler, $size - $arglen);
                        }
                    }
                }
                // escape % and pass it forward
                $newformat .= $padding_pre
                    .str_replace('%', '%%', $arg).$padding_post;
            } else {
                // another type, pass forward
                $newformat .= "%$sign$filler$align$size$precision$type";
                $newargv[] = array_shift($argv);
            }
            $format = strval($post);
        }
        // Convert new format back from UTF-8 to the original encoding
        $newformat = mb_convert_encoding($newformat, $encoding, 'UTF-8');
        return vsprintf($newformat, $newargv);
    }
}