<?php

use Packs\Consoles\Alm as Console;

/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 13:05
 */

include 'AppTest.php';

class ConsoleTest extends AppTest
{

    public function testConsoleInter(): void
    {
        global $argv;
        $argv=[
            './cli',
            'info',
        ];
        App::init();

        Console::init();

        include dirname(__DIR__) . '/App/Consoles/route.php';

        Console::unitTest();


        $this->assertIsInt(1,1);

    }

}