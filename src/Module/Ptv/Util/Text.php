<?php

namespace App\Module\Ptv\Util;

class Text
{
    public static function truncate(?string $text, int $maxlen) : ?string
    {
        if (is_null($text)) {
            return $text;
        }
        $offset = 0;
        $matches = [];
        while ($offset <= $maxlen) {
            if (preg_match('/[\!\?\.]+/', $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $pos = mb_strlen($matches[0][0]) + $matches[0][1];
                if ($pos > $maxlen) {
                    $text = mb_substr($text, 0, $offset);
                    break;
                } else {
                    $offset = $pos;
                }
            } else {
                $text = mb_substr($text, 0, $offset ?: $maxlen);
                break;
            }
        }
        return $text;
    }
}
