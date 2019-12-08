<?php

namespace shmurakami\Spice\Test\Example;

use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Example\Client;
use shmurakami\Spice\Test\TestCase;

class ClientTest extends TestCase
{
    public function testCallApplication()
    {
        $client = new Client(new Application());
        $this->assertSame('Hello Alice', $client->callApplication());
    }

    public function testCallByClosure()
    {
        $client = new Client(new Application());
        $this->assertSame('Hello Bob', $client->callByClosure());
    }

    public function testCallExtendApplication()
    {
        $client = new Client(new Application());
        $this->assertSame(
            [
                'Howdy Charlie',
                'Hello Dave',
            ],
            $client->callExtendApplication());
    }

    public function ()
    {
        
    }
}
