<?php

namespace shmurakami\Spice\Test\Ast;

use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Exception\ClassNotFoundException;
use shmurakami\Spice\Test\TestCase;

class AstTest extends TestCase
{

    public function testClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);
        (new AstLoader(new ClassMap([])))->loadByClass(new ClassContext('_' . Application::class));
    }

}
