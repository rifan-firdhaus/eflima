<?php namespace modules\core\helpers;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Common
{
    public static function isEmpty($value)
    {
        return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }

    public static function randomHexColor()
    {
        return '#' . str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT);
    }

    public static function logicalRandom(){
        $result = [];
        $strings = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $stringLength = strlen($strings);
        $randomString = '';

        for ($i = 0; $i < 10; $i++) {
            $randomString .= $strings[mt_rand(0, $stringLength - 1)];
        }

        $result[] = $randomString;

        $microtime = explode('.', microtime(true));
        $microtime[1] = str_pad($microtime[1], 4, 0, STR_PAD_LEFT);
        $microtime = implode('', $microtime);
        $microtimeString = '';

        foreach (str_split($microtime) AS $char) {
            $microtimeString .= $randomString[$char];
        }

        $result[] = $microtimeString;

        return implode('-', $result);
    }
}