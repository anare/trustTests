<?php

//https://github.com/php/php-src/blob/a555cc0b3d4f745e6d0bb8c595de400a0c728827/ext/standard/string.c#L3332

function reverse($str)
{
    for ($i = strlen($str) - 1, $j = 0; $j < $i; $i--, $j++) {
        $temp    = $str[$i];
        $str[$i] = $str[$j];
        $str[$j] = $temp;
    }

    return $str;
}

$str = 'Hello World!';
echo $str . "\n";
echo reverse($str) . "\n";
