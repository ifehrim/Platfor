<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

namespace Packs\Consoles;


use _App;
use App;

class Alm
{

    use _App;

    protected static $params;

    public static function init()
    {
        $prt = [];
        $prt[] = "Project LooL[Console]\n";
        $prt[] = "Version:1.0.0";
        $prt[] = "Author:lool";
        $prt[] = "usage <command> [<args>]";
        $prt[] = "updating...";
        App::$app->take('help',$prt);

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


    private static function prt($str = null, $break = true)
    {
        if (is_array($str)) $str = implode("\n", $str);
        print $str . ($break ? "\n" : "");
    }

    public static function execute()
    {
        foreach (App::$app->takes as  $k=>$take) {
            self::prt($take);
        }
    }

    private static function match($route)
    {
        global $argv;

        if (in_array($route,$argv)){

            return true;
        }
        return false;
    }


}