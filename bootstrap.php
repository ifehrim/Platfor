<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */
ini_set('display_errors', E_ALL);

use Packs\System\Loader;

include 'Packs/System/Loader.php';

if (file_exists(__DIR__.'vendor/autoload.php')) include 'vendor/autoload.php';

$_ENV['PROJECT_PATH']=__DIR__;

Loader::autoload(true, [
    dirname(__DIR__),
    __DIR__.'/Packs',
    __DIR__.'/App',
    __DIR__
]);





