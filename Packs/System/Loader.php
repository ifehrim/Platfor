<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 10/24/2018
 * Time: 12:50
 */

namespace Packs\System;


class Loader {

    /**
     * Autoload directories.
     *
     * @var array
     */
    protected static $dirs = [];


    /*** AutoLoading Functions ***/

    /**
     * Starts/stops autoloader.
     *
     * @param bool $enabled Enable/disable autoLoading
     * @param array $dirs Autoload directories
     */
    public static function autoload($enabled = true, $dirs = []) {
        if ($enabled) {
            spl_autoload_register([__CLASS__, 'loadClass']);
        }
        else {
            spl_autoload_unregister([__CLASS__, 'loadClass']);
        }

        if (!empty($dirs)) {
            self::addDirectory($dirs);
        }
    }

    /**
     * Autoloads classes.
     *
     * @param string $class Class name
     */
    public static function loadClass($class) {
        $class_file = str_replace('\\', '/', $class).'.php';
        $class_file_old = str_replace('\\', '/', $class).'.php';
        foreach (self::$dirs as $dir) {
            $file = $dir.'/'.$class_file;
            if (file_exists($file)) {
                require $file."";
                return;
            }else{
                $file = $dir.'/'.$class_file_old;
                if (file_exists($file)) {
                    require $file."";
                    return;
                }
            }
        }
    }

    /**
     * Adds a directory for autoLoading classes.
     *
     * @param mixed $dir Directory path
     */
    public static function addDirectory($dir) {
        if (is_array($dir) || is_object($dir)) {
            foreach ($dir as $value) {
                self::addDirectory($value);
            }
        }
        else if (is_string($dir)) {
            if (!in_array($dir, self::$dirs)) self::$dirs[] = $dir;
        }
    }

    public static function getDirs(){
        return self::$dirs;
    }
}
