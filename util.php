<?php
/**
 * Contains routing function definitions (to be re-used across projects)
 * User: wes
 * Date: 2/22/18
 * Time: 10:24 AM
 */

namespace lib;
use DateTime;

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

function ellipsize(string $in, int $n = 50) : string {
    return strlen($in) > $n ? substr($in,0,$n)."..." : $in;
}

function timeAgo($timestamp){
    $datetime1=new DateTime("now");
    $datetime2=date_create($timestamp);
    $diff=date_diff($datetime1, $datetime2);
    $timemsg='';
    if($diff->y > 0){
        $timemsg = $diff->y .' year'. ($diff->y > 1?"s":'');

    }
    else if($diff->m > 0){
        $timemsg = $diff->m . ' month'. ($diff->m > 1?"s":'');
    }
    else if($diff->d > 0){
        $timemsg = $diff->d .' day'. ($diff->d > 1?"s":'');
    }
    else if($diff->h > 0){
        $timemsg = $diff->h .' hour'.($diff->h > 1 ? "s":'');
    }
    else if($diff->i > 0){
        $timemsg = $diff->i .' minute'. ($diff->i > 1?"s":'');
    }
    else if($diff->s > 0){
        $timemsg = $diff->s .' second'. ($diff->s > 1?"s":'');
    }

    $timemsg = $timemsg.' ago';
    return $timemsg;
}