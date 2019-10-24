<?php

namespace Packs;


use App;

class Http
{
    use _Frame;

    public static $params = [];

    public static function matchUrl($pattern, $case_sensitive = false)
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
     * @param App $app
     * @param callable $class
     * @return App|mixed|null
     */
    public static function get($route, App $app, $class)
    {
        if (static::matchUrl($route)) {
            return static::__callClass($app, $class, '__run');
        }
        return $app;
    }

    /**
     * @param $route
     * @param App $app
     * @param callable $class
     * @return App|mixed|null
     */
    public static function post($route, App $app, $class)
    {
        if (static::matchUrl($route)) {
            return static::__callClass($app, $class, '__run');
        }
        return $app;
    }

    /**
     * @notice Please make sure route must require @action
     * @param $route
     * @param App $app
     * @param callable $class
     * @return App|mixed|null
     */
    public static function model($route, App $app, $class)
    {
        if (static::matchUrl($route)) {
            if (isset(static::$params['action'])) $class=[$class,static::$params['action']];

            return static::__callClass($app, $class, '__run');
        }
        return $app;
    }




}