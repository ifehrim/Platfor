<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

namespace Https;


use App;
use Packs\Https\_Use;

class Article
{
    use _Use;

    public static function edit(App $app){
        $app->take(__FUNCTION__,[
            "actions"=>"ok",
            "gets"=>$_GET
        ]);
    }

}