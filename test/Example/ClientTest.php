<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Test\Example;

use shmurakami\Elephoot\Example\Application;
use shmurakami\Elephoot\Example\Client;
use shmurakami\Elephoot\Test\TestCase;

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
}
