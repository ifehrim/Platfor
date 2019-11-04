<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

namespace Packs\Https;


use _App;
use App;

class Alm
{
    use _App;

    const version='1.0.0';


    public static function init()
    {
        header('Y-Powered-By:LooL-Http/'.self::version);
    }

    public static $params = [];

    public static function match($pattern, $case_sensitive = false)
    {

        static::$params = [];
        $url = urldecode($_SERVER['REQUEST_URI']);

        // Wildcard or exact match
        if ($pattern === '*' || $pattern === $url) {
            return true;
        }
        $ids = array();
        $last_char = substr($pattern, -1);
        // Get splat
        if ($last_char === '*') {
            $n = 0;
            $len = strlen($url);
            $count = substr_count($pattern, '/');
            for ($i = 0; $i < $len; $i++) {
                if ($url[$i] == '/') $n++;
                if ($n == $count) break;
            }
            $splat = (string)substr($url, $i + 1);
        }
        // Build the regex for matching
        $regex = str_replace(array(')', '/*'), array(')?', '(/?|/.*?)'), $pattern);
        $regex = preg_replace_callback(
            '#@([\w]+)(:([^/\(\)]*))?#',
            function ($matches) use (&$ids) {
                $ids[$matches[1]] = null;
                if (isset($matches[3])) {
                    return '(?P<' . $matches[1] . '>' . $matches[3] . ')';
                }
                return '(?P<' . $matches[1] . '>[^/\?]+)';
            },
            $regex
        );
        // Fix trailing slash
        if ($last_char === '/') {
            $regex .= '?';
        } // Allow trailing slash
        else {
            $regex .= '/?';
        }
        // Attempt to match route and named parameters
        if (preg_match('#^' . $regex . '(?:\?.*)?$#' . (($case_sensitive) ? '' : 'i'), $url, $matches)) {
            foreach ($ids as $k => $v) {
                static::$params[$k] = (array_key_exists($k, $matches)) ? urldecode($matches[$k]) : null;
            }
            return true;
        }
        return false;
    }

    /**
     * @param $route
     * @param callable $class
     * @return App|mixed|null
     */
    public static function get($route,$class)
    {
        if (static::match($route)) {
            return static::__callClass(App::$app, $class, '__run');
        }
        return App::$app;
    }

    /**
     * @param $route
     * @param callable $class
     * @return App|mixed|null
     */
    public static function post($route,  $class)
    {
        if (static::match($route)) {
            return static::__callClass(App::$app, $class, '__run');
        }
        return App::$app;
    }

    /**
     * @notice Please make sure route must require @action
     * @param $route
     * @param callable $class
     * @return App|mixed|null
     */
    public static function model($route, $class)
    {

        if (static::match($route)) {
            if (isset(static::$params['action'])) $class=[$class,static::$params['action']];
            return static::__callClass(App::$app, $class, '__run');
        }
        return App::$app;
    }

    public static function respond()
    {
        echo json_encode(App::$app->takes);
    }




}