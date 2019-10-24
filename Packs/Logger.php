<?php


namespace Packs;



use App;

class Logger
{
    use _Frame;

    public static function __start(App $app){
        return $app;
    }


}