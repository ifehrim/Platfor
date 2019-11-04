<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

namespace Consoles;


use App;
use Packs\Consoles\_Use;

class Command
{
    use _Use;

    public static function getInfo(App $app){

        $app->take(__FUNCTION__,[
            'name'=>'Title',
            'page'=>'Hello World!',
            'date'=>time(),
        ]);
    }

}