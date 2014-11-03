<?php

namespace Bolt\Helpers;

use Maid\Maid;

class Html
{
    /**
     * Trim text to a given length.
     *
     * @param  string $str           String to trim
     * @param  int    $desiredLength Target string length
     * @return string Trimmed string
     */
    public static function trimText($str, $desiredLength)
    {
        $str = strip_tags($str);

        if (mb_strlen($str) > $desiredLength) {
            $str = mb_substr($str, 0, $desiredLength - 1) . '…';
        } 

        return $str;
    }


    /**
     * Transforms plain text to HTML. Plot twist: text between backticks (`) is
     * wrapped in a <tt> element.
     *
     * @param  string $str Input string. Treated as plain text.
     * @return string The resulting HTML
     */
    public static function decorateTT($str)
    {
        $str = htmlspecialchars($str, ENT_QUOTES);
        $str = preg_replace('/`([^`]*)`/', '<tt>\\1</tt>', $str);

        return $str;
    }
}
