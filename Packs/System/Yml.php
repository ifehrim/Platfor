<?php
/**
 * Created by IntelliJ IDEA.
 * User: pc
 * Date: 11/9/2018
 * Time: 10:38
 */

namespace Packs\System;


class Yml
{

    protected static $lines;
    protected static $ln = 1;
    protected static $deep = 0;

    private static function get_reg($num = 0)
    {
        $line = isset(self::$lines[$num]) ? self::$lines[$num] : null;
        if (empty($line)) return [];
        $regex = '#(?P<len>\s+)(?P<key>(?:.*?)) *\:(\s++(?P<value>.+))?$#u';
        if (preg_match($regex, rtrim($line), $values)) {
            return @[strlen($values['len']), $values['key'], $values['value']];
        }
        return [];
    }

    public static function decode($lines = [])
    {
        $data = $keys = [];
        if (empty(self::$lines)) self::$lines = $lines;
        self::$deep += 2;
        foreach (self::$lines as $k => $line) {
            if ($k < self::$ln) continue;
            self::$ln++;
            @list($len, $key, $val) = static::get_reg($k);
            @list($_len) = $bot = static::get_reg($k + 1);
            if (!empty($key)) {
                if (self::$deep != $len) {
                    self::$ln--;
                    self::$deep -= 2;
                    break;
                };
                $var = $val;
                if (isset($data[$key])) {
                    $var = $data[$key];
                    if (!is_array($var)) $var = [$var];
                }
                if (empty($val)) {
                    if (!empty($bot) && $_len == $len + 2) {
                        if (!is_array($var)) $var = [];
                        $var[] = self::decode();
                    }
                } else {
                    if (is_array($var)) {
                        if (!in_array($val, $var)) $var[] = $val;
                    } else {
                        $var = $val;
                    }
                }
                $data[$key] = $var;
            }
        }
        return $data;
    }


    public static function encode($lines, $offset = 2, $sk = null)
    {
        $str = '';
        if ($offset == 2) $str = "Yml:\n";
        foreach ($lines as $k => $line) {
            $offset_o = $offset;
            if (is_numeric($k)) {
                $offset_o -= 2;
                $k = $sk;
                $str .= '' . str_pad('', $offset_o, ' ') . $k . ':';
            }
            if (is_array($line)) {
                $str .= "\n" . static::encode($line, $offset_o + 2, $k);
            } else {
                if ($offset_o + 2 != $offset) {
                    $str .= '' . str_pad('', $offset, ' ') . $k . ':';
                }
                if ($k == 'uptime') $line = time();
                $str .= " " . $line . "\n";
            }
        }
        $str_s = explode("\n", $str);
        foreach ($str_s as $k => $sr) {
            if (empty(trim($sr)))
                unset($str_s[$k]);

        }
        return implode("\n", $str_s) . "\n";
    }

}