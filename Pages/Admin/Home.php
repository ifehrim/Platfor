<?php


namespace Pages\Admin;


use App;
use Pages\Auth;
use Packs\Logger;
use Packs\_Frame;

class Home
{
    use _Frame;

    static $__before=[
        Auth::class
    ];

    static $__after=[
        Logger::class
    ];

    public static function getInfo(App $app){

        $app->take('Home',[
            'name'=>'Title',
            'page'=>'-',
            'date'=>time(),
        ]);

    }




}