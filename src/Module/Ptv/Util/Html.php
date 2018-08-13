<?php

namespace App\Module\Ptv\Util;

use Html2Text\Html2Text;

class Html
{
    public static function toPlainText(?string $html, int $maxlen) : ?string
    {
        $text = (new Html2Text($html))->getText();
        return Text::truncate($text, $maxlen);
    }
}
