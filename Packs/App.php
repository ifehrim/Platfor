<?php


class App
{

    const VERSION = '1.0.0';

    const ERROR = 'error';
    /**
     * @var App
     */
    public static $app=null;
    /**
     * @var array
     */
    protected $takes=[];
    /**
     * @var bool
     */
    protected $commit=false;
    /**
     * @var array
     */
    protected $tracks;
    protected $middleWares;


    public static function init()
    {
        if (empty(static::$app)){
            static::$app=new App();
        }
        return static::$app;
    }

    public function execute(){

        echo(json_encode($this->takes));
    }

    public function take($key='_',$array=[])
    {
        $this->takes[$key]=$array;
        return $this;
    }

    public function commit()
    {
        $this->commit=true;
    }

    public function isCommit(){
        return $this->commit;
    }

    public function track($class)
    {
        $this->tracks[]=$class;
    }

    public function middleWare($key='_',$array=[])
    {
        if (isset($this->middleWares[$key])){
            $this->middleWares[$key]=array_merge($this->middleWares[$key],$array);
        }else{
            $this->middleWares[$key]=$array;
        }

        return $this;
    }

}