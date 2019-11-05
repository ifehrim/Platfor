<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

use PHPUnit\Framework\TestCase;

include dirname(__DIR__).'/bootstrap.php';

class AppTest extends TestCase
{

    public function testAppInter(): void
    {


        $this->assertEquals(
            'user@example.com',
            'user@example.com'
        );
    }
}
