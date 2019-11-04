<?php
/**
 * Created by LooL.
 * User: ifehrim@gmail.com
 * Date: 2019-11-04
 * Time: 12:57
 */

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{

    public function testCanBeUsedAsString(): void
    {

        $this->assertEquals(
            'user@example.com',
            'user@example.com'
        );
    }
}
