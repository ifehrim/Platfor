<?php


namespace Pages;


use App;
use Packs\_Frame;

class Auth
{

    use _Frame;

    static function __start(App $app){

        if ($_GET['token']!='123'){
            $app->take('error',['token is error'])->commit();
        }
        return $app;
    }


}