<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

use Packs\Https\Alm as Http;

include __DIR__ . '/../Packs/App/Boot.php';

App::init();

Http::init();

include __DIR__ . '/../App/Https/route.php';

Http::respond();
