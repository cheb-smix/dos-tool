<?php

class Math
{
    public static function median(array $arr = [], $rounder = 10)
    {
        sort($arr);
        $mIndex = floor(count($arr) / 2);
        if ($mIndex % 2 == 1) {
            return round($arr[$mIndex], $rounder);
        } else {
            return round(($arr[$mIndex] + $arr[$mIndex + 1]) / 2, $rounder);
        }
    }

    public static function arithmetic(array $arr = [], $rounder = 10)
    {
        return round(array_sum($arr) / count($arr), $rounder);
    }

    public static function quadratic(array $arr = [], $rounder = 10)
    {
        return round(sqrt(array_sum(array_map(function($num) {
            return pow($num, 2);
        }, $arr)) / count($arr)), $rounder);
    }

    public static function min(array $arr = [], $rounder = 10)
    {
        sort($arr);
        return round($arr[0], $rounder);
    }

    public static function max(array $arr = [], $rounder = 10)
    {
        rsort($arr);
        return round($arr[0], $rounder);
    }
}
