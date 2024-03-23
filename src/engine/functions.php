<?php

declare(strict_types = 1);

/**
 * @param $string
 *
 * @return array|mixed|string|string[]|null
 */
function to_html($string)
{
    if (is_string($string)) {
        $string = preg_replace(['/</', '/>/', '/"/'], ['&lt;', '&gt;', '&quot;'], $string);
    }

    return $string;
}


/**
 * @param $string
 * @param  bool  $urldecode
 *
 * @return string
 */
function normalizeName($string, bool $urldecode = true): string
{
    // Is email? No need to decode + signs.
    if ((strpos($string, '@') && strpos($string, '.')) || filter_var($string, FILTER_VALIDATE_EMAIL)) {
        return strip_tags(htmlspecialchars_decode(html_entity_decode(rawurldecode($string)), ENT_QUOTES));
    }
    // Separated for readability
    $normalizedString = replacePercent($string);
    if ($urldecode) {
        $normalizedString = rawurldecode($normalizedString);
        $normalizedString = urldecode(replacePercent($normalizedString));
    }
    $normalizedString = html_entity_decode($normalizedString);
    $normalizedString = htmlspecialchars_decode($normalizedString, ENT_QUOTES);
    return strip_tags($normalizedString);
}

/**
 * @param $source
 *
 * @return string
 */
function replacePercent($source): string {
    $pattern = '/[0-9A-Fa-f]/';
    $decodedStr = '';
    $pos = 0;
    $len = strlen($source);
    while ($pos < $len) {
        $charAt = substr($source, $pos, 1);
        if ($charAt === '%') {
            $pos++;
            $hexVal = substr($source, $pos, 2);
            $decodedStr .= preg_match($pattern, $hexVal) ? "{$charAt}{$hexVal}" : "{$charAt}25";
            $pos += preg_match($pattern, $hexVal) ? 2 : 0;
        } else {
            $decodedStr .= $charAt;
            $pos++;
        }
    }
    return $decodedStr;
}
