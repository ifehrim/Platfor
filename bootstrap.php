<?php

use Packs\Loader;

include 'Packs/Loader.php';

ini_set('display_errors', E_ALL);

Loader::autoload(true, [
    dirname(__DIR__),
    __DIR__.'/Packs',
    __DIR__
]);





