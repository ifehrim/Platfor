<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

trait _App
{


    public static function __run(App $app,$fun='__start')
    {
        $app = static::__register(true, $app);

        if (!$app->isCommit()) {
            try {
                $app->track(__CLASS__.'::center');
                $_tmp=null;
                if (method_exists(__CLASS__,$fun)){
                    $_tmp = call_user_func([__CLASS__, $fun], $app);
                }else{
                    $app->take(App::ERROR,'Cant find #('.$fun.') action from '.__CLASS__.'')->commit();
                }
                if ($_tmp instanceof App) $app = $_tmp;
            } catch (\Exception $e) {
                print_r($e);
            }
        }
        $app = static::__register(false, $app);

        return $app;
    }

    /**
     * @param bool $isBefore
     * @param App $app
     * @return App|mixed
     */
    private static function __register($isBefore, App $app)
    {
        $register = [];
        if ($isBefore) {
            if (isset(static::$__before)) $register = static::$__before;
        } else {
            if (isset(static::$__after)) $register = static::$__after;
        }
        $ex=($isBefore?'before':'after');


        if (!empty($register) && is_array($register)) {

            $app->middleWare(__CLASS__,[$ex=>$register]);

            foreach ($register as $class) {
                $app->track(__CLASS__.'::'.$ex.'->'.$class);
                $app = static::__callClass($app, $class, '__run');
            }
        }
        return $app;
    }


    public static function __callClass(App $app, $class, $func)
    {
        if (!$app->isCommit()) {
            try {
                $_tmp=null;


                if (is_array($class)){
                    if (class_exists($class[0]))
                    $_tmp = call_user_func_array([$class[0], $func], [$app,$class[1]]);
                }else{
                    if (class_exists($class))
                    $_tmp = call_user_func([$class, $func], $app);
                }

                if ($_tmp instanceof App) $app = $_tmp;
            } catch (\Exception $e) {
                print_r($e);
            }
        }
        return $app;
    }


}