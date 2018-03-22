<?php
/**
 * Contains routing function definitions (to be re-used across projects)
 * User: wes
 * Date: 2/22/18
 * Time: 10:24 AM
 */

namespace lib;

/**
 * Creates a regex from a URL routing pattern.
 * Adapted from: https://stackoverflow.com/a/30359808
 *
 * @param string $pattern: URL routing pattern
 * @return string: A regex that matches the URL pattern.
 */
function gen_regex(string $pattern): string {
    if (preg_match('/[^-:\/_{}()a-zA-Z\d]/', $pattern))
        return false;

    $pattern = preg_replace('#\(/\)#', '/?', $pattern);

    $paramChars = '[a-zA-Z0-9\_\-]+';
    $pattern = preg_replace('/:('.$paramChars.')/', '(?<$1>'.$paramChars.')', $pattern);
    $pattern = preg_replace('/{('. $paramChars .')}/', '(?<$1>' . $paramChars . ')', $pattern);

    return "@^" . $pattern . "$@D";
}