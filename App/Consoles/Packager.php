<?php
/**
 * Created by IntelliJ IDEA.
 * User: ifehrim
 * Date: 11/9/2018
 * Time: 10:39
 */

namespace Consoles;


use Packs\System\Http;
use Packs\System\Yml;


class Packager
{
    public $path;
    public $cmd;
    public static $reps = [
        'github.com',
        'gitee.com',
        'bitbucket.org',
        'git.oschina.net',
        'git.coding.net',
        'code.aliyun.com',
        'code.csdn.net',
        'code.jd.com',
    ];

    public function __construct($arr = [])
    {
        global $argv;
        $this->path = $_SERVER['PWD'] . '/alim.yml';
        @list($_, $command) = $argv;
        $this->cmd = $_;
        $params = [];
        foreach ($argv as $v) {
            if (!in_array($v, [$_, $command])) $params[] = $v;
        }
        if (method_exists($this, $command)) {
            $this->$command($params);
        } else {
            $this->help();
        }
    }


    private function package($arr, $cmd)
    {
        $packager = $this->parse();
        @list($pck) = $arr;
        if (!isset($packager['packages'])) return;
        foreach ($packager['packages'] as &$package) {
            foreach ($package['package'] as &$item) {
                if (isset($item['name']) && isset($item['source']) && isset($item['type'])) {
                    if ($pck != $item['name']) continue;
                    self::prt("cmd:{$cmd} package:" . json_encode($item));
                    switch ($cmd) {
                        case 'install':
                            if ($item['type'] == 'look') {
                                self::prt("already installed ... ");
                                continue;
                            } else {
                                self::prt("starting installing ...");
                            }
                            foreach ($item['source'] as $source) {
                                if (isset($source['git']) && isset($source['tag'])) {
                                    self::down($source, $item['name']);
                                    $item['type'] = "look";
                                    $item['time'] = time();
                                    self::prt("finished installing ...");
                                }
                            }
                            break;
                        case 'remove':
                            $item['type'] = 'remove';
                            self::delete($item['name']);
                    }
                }
            }
        }
        $this->parse($packager);
    }

    private function parse($packager = null)
    {
        $res = [];
        $file = $this->path;
        if (!file_exists($file)) {
            self::prt("first init a package!!!");
            self::prt("command: " . $this->cmd . " init");
        } else {
            if (!empty($packager)) {
                file_put_contents($file, Yml::encode($packager));
            } else {
                $res = Yml::decode(file($file));
            }
        }
        return $res;
    }

    public function init($arr = [])
    {
        $path = $this->path;
        $dir = dirname($this->path);
        $gitPath = $dir . '/.git/config';
        self::prt("");
        self::prt("---------welcome to the [alim packager manager] config generator---------");
        self::prt("");
        self::prt("generate path: " . $dir . "        packager: " . basename($path));
        if (file_exists($path)) {
            self::prt("already init! :)\n");
            return;
        }
        $package = [
            "git" => "https://x.com/x/x.git@a8b5a491",
            "user" => "xxx",
            "mail" => "xxx@xx.com",
        ];

        if (file_exists($gitPath)) {
            $ar = file($gitPath);

            foreach ($ar as $line) {
                $hasU = !(strpos($line, "url") === false);
                $hasD = !(strpos($line, "=") === false);
                if ($hasU && $hasD) {
                    $package['git'] = trim(str_replace(["url", "="], "", $line));
                    break;
                }
            }

            $last = @trim(@end(@file(@dirname($gitPath) . '/logs/HEAD')));

            if (!empty($last)) {
                @list($_, $tag, $user, $mail) = explode(" ", $last);
                if ($package['git']) {
                    $package['git'] .= "@" . $tag;
                }
                $package['user'] = $user;
                $package['mail'] = @str_replace(['<', '>'], '', $mail);
            }
        }
        $package['ver'] = '1.0.0';
        $package['time'] = [['create' => timestamp()]];
        $package['depends'] = 'none';
        foreach ($package as $key => $item) {
            $v = $package[$key];
            $k = "$key (<$key>)";
            if ($key == 'depends') {
                $k = "$key (<https://x.com/x/x.git@a8b5a491> <git@commit> ...)";
            }
            if (is_array($item)) continue;
            self::prt("Package $k [$v]:", false);
            $input = trim(fgets(STDIN, 255));
            if (!empty($input)) $package[$key] = $input;
        }
        if (isset($package['depends'])) {
            $depends = explode(" ", $package['depends']);
            $arr = ['package' => []];
            foreach ($depends as $git) {
                if ($git == "none") continue;
                $arr['package'][] = ["git" => $git, 'time' => [['create' => timestamp()]]];
            }
            $package['depends'] = [$arr];
        }

        $con = Yml::encode($package);
        self::prt($con);
        self::prt("confirm generation package [yes]:", false);
        $input = strtolower(trim(fgets(STDIN, 255)));
        $yes = empty($input) || $input == "yes" || $input == "y" || $input == "yep" || $input == "ok";
        if ($yes) {
            file_put_contents($this->path, $con);
            self::prt("generation success!!!\n");
            return;
        }
        self::prt("generation canceled !!!\n");

        exit(250);
    }

    private function help()
    {
        $prt = [];
        $prt[] = "help is updating....version:1.0.0";
        $prt[] = "usage <command> [<args>]";
        $prt[] = "\nThese are common commands used in various situations:";
        $prt[] = "\n-----------------------Step )__( -----------------------------";
        $prt[] = "  clone       Clone a package into a Packager directory";
        $prt[] = "  init        Create a empty package for the Frame";
        $prt[] = "\n-----------------------Step )__( -----------------------------";
        $prt[] = "  install     Install Form Package.list or Params";
        $prt[] = "  remove      Remove  Form Package.list or Params";
        $prt[] = "  upgrade     Upgrade Form Package.list or Params";
        $prt[] = "  update      Update  Form Package.list or Params";
        $prt[] = "\n-----------------------Step )__( -----------------------------";
        $prt[] = "  publish     Publish Form your local Package to online";

        self::prt($prt);
    }

    public function install($arr = [])
    {
        $package = $this->parse();
        if (empty($arr)) {
            if (isset($package['depends'])) {
                self::prt("start installing...");
            } else {
                self::prt("depends is empty !!!");
                self::prt("command: " . $this->cmd . " install <git@tag> ");
            }
        } else {

            if (!empty($arr)) {
                if (!isset($package['depends']) || !isset($package['depends'][0])) $package['depends'] = [['package' => []]];
                $_arr = $package['depends'][0]['package'];
                //step input resolve
                foreach ($arr as $item) {
                    @list($git) = self::rep_select($item);
                    if (self::rep_registered($git, $_arr)) {
                        self::prt('[' . $git . '] already register install.');
                    } else {
                        input_commit:
                        self::prt("input commit|branch|tag ex:<6f6a3cdf> [master]:", false);
                        $input = strtolower(trim(fgets(STDIN, 255)));
                        if (empty($input)) $input = 'master';
                        $item = ["git" => $git, 'tag' => $input, 'time' => [['create' => time()]]];
                        $_arr[] = $item;
                    }
                }
                //step stat resolve
                $install=[];
                foreach ($_arr as $item) {
                    if(!isset($item['install'])){
                        $install[]=$item;
                    }
                }
                self::prt('total: '.count($_arr).' ; need new install: '.count($install));
                foreach ($_arr as &$item) {
                    if(!isset($item['install'])){
                        if(self::rep_install($item)){
                            $item['install']=true;
                            $item['time'][0]['install']=time();
                        }
                    }
                }
                $package['depends'][0]['package'] = $_arr;
            }
            $this->parse($package);
        }
    }


    public function remove($arr = [])
    {
        self::package($arr, 'remove');
    }


    private static function prt($str = null, $break = true)
    {
        if (is_array($str)) $str = implode("\n", $str);
        print $str . ($break ? "\n" : "");
    }




    private static function delete($pack_name)
    {
        $d = FRAME_ROOT . '/' . $pack_name . '/';
        exec("rm -rf " . $d);
    }


    public static function rep_select($item)
    {
        $str = $rep = '';
        $matches = self::rep_parse($item);
        if (!empty($matches)) {
            @list($rep, $user, $pro) = $matches;
            $item = $user . '/' . $pro;
        } else {
            $i = 0;
            foreach (self::$reps as $k => $rep) {
                $i++;
                if ($i == 4) {
                    $i = 1;
                    $str .= "\n";
                }
                $str .= str_pad(" [{$k}] {$rep} ", 30, ' ');
            }
            self::prt("git repositories [{$item}]:");
            self::prt($str);
            input_rep:
            self::prt("select repository (if isn't, please input)[0]:", false);
            $input = strtolower(trim(fgets(STDIN, 255)));
            if (empty($input)) $input = 0;
            if (is_numeric($input)) {
                if (!isset(self::$reps[$input])) goto input_rep;
                $rep = self::$reps[$input];
            }
        }
        $git = "https://{$rep}/{$item}.git";
        self::prt("repositories: {$git}");
        return [$git, $rep, $item];
    }

    public static function rep_parse($s)
    {
        $s = str_replace('.git', '', $s);
        $s .= '.git';
        $isMatched = preg_match('/https:\/\/(.+?)\/(.+?)\/(.+?)\.git/', $s, $matches);
        if (!$isMatched) $isMatched = preg_match('/git\@(.+?):(.+?)\/(.+?)\.git/', $s, $matches);
        if ($isMatched) {
            @list($_, $rep, $user, $pro) = $matches;
            return [$rep, $user, $pro, $_];
        }
        return [];
    }

    public static function rep_registered($git, $_arr)
    {
        $res = false;
        foreach ($_arr as $item) {
            if (isset($item['git'])) {
                $res = self::compare($git, $item['git'], '=');
                if ($res) break;
            }
        }
        return $res;
    }

    public static function rep_install($item){
        $git=$item['git'];
        self::prt('[' . $git . ']   start installing....');
        @list($rep, $user, $pro)=self::rep_parse($git);
        $target = PROJECT_ROOT . '/.alim/' .$user.'/'.$pro.'/'.$item['tag'] . '.zip';
        self::down_zip($git,$item['tag'],$target);
        self::prt('[' . $git . ']   finish install...');
        return false;
    }

    public static function compare($git, $git2, $op = '=')
    {
        switch ($op){
            case '=':
                return $git==$git2;
        }
        return true;
    }

    public static function runCommand($bin, $command = '',$path='pipe', $force = true)
    {
        $stream = null;
        $bin .= $force ? ' 2>&1' : '';

        $descriptorSpec = array
        (
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
        );
        //if($path!='pipe') $descriptorSpec[2]=array("file", $path.'-.txt', "w");
        $process = proc_open($bin, $descriptorSpec, $pipes);
        if (is_resource($process))
        {
            stream_set_blocking($pipes[0], 0);
            stream_set_blocking($pipes[1], 0);
            fwrite($pipes[0], $command);

            $str='';
            while(!feof($pipes[1])) {
                $str.= fread($pipes[1], 16384 * 4);
            }
            file_put_contents($path.'-.txt',$str);
            //$stream = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[0]);
            proc_close($process);
        }

        return $stream;
    }


    public static function down_zip($git,$tag, $target)
    {
        @exec('mkdir -p '.dirname($target));

        if (!file_exists($target)) {
            $path=$target.'-git';
            $log=$target.'-log';
            exec("git clone {$git} {$path} --progress --depth 1 >>{$log} 2>&1 &");
            $k=1;

            self::window();

            echo "Progress :      ";  // 5 characters of padding at the end
            for ($i=0 ; $i<=100 ; $i++) {
                echo "\033[5D";      // Move 5 characters backward
                echo str_pad($i, 3, ' ') . " %";    // Output is always 5 characters long
                sleep(1);           // wait for a while, so we see the animation
            }
            while ($k){
                $k++;
                $last=@trim(@end(@file($log)));
                $done=strpos($last,'.. done.');
                if(is_numeric($done)){
                    $k=0;
                }
                $fatal=strpos($last,'fatal:');
                if(is_numeric($fatal)){
                    $k=0;
                }
                var_dump($last);
                usleep(900);
            }

            die;
        }


    }

    public static function down($source, $pack_name)
    {
        $res = false;
        $url = "https://api.github.com/repos/" . $source['git'] . "/zipball/" . $source['tag'];
        $destination = FRAME_ROOT . '/Packager/tmp/' . $source['tag'] . '.zip';
        $pack_zip = str_replace('/', '-', $source['git']) . '-' . substr($source['tag'], 0, 7);
        $pwd = dirname($destination);
        $d = FRAME_ROOT . '/' . $pack_name . '/';
        @mkdir($d);
        if (!file_exists($destination)) {
            @mkdir($pwd);
            $con = Http::get($url);
            file_put_contents($destination, $con['content']);
        }
        $zip = new \ZipArchive;
        if ($zip->open($destination) === TRUE) {
            $zip->extractTo($pwd);
            $zip->close();
        } else {
            self::prt('unzip failed');
        }
        $file = $_ENV['PROJECT_PATH'] . '/Packager/tmp/' . $pack_zip . '/Frame/' . $pack_name;
        $ignore = $_ENV['PROJECT_PATH'] . '/.gitignore';
        if (is_dir($file)) {
            @file_put_contents($ignore, "\n/{$pack_name}/", FILE_APPEND);
            $m = $file . '/*';
            exec("mv -f {$m} $d");
            exec("rm -rf " . dirname(dirname($file)));
            $res = true;
        }
        return $res;
    }


    public static function window(){
        $res=[];
        preg_match("/rows.([0-9]+);.columns.([0-9]+);/", strtolower(exec('stty -a |grep columns')), $output);
        if(sizeof($output)==3){
            $res=[$output[1],$output[1]];
        }
        var_dump($output);die;
        return null;
    }

}