<?php


namespace Pages;


use App;
use Packs\_Frame;

class Article
{

    use _Frame;

    public static function edit(App $app){

        $app->take(__FUNCTION__,"ok");

    }


    public static function __start(App $app)
    {
        return $app;
    }


}