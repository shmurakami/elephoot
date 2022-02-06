<?php

namespace shmurakami\Elephoot\Test\Ast;

use shmurakami\Elephoot\Ast\AstLoader;
use shmurakami\Elephoot\Ast\ClassMap;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Example\Application;
use shmurakami\Elephoot\Exception\ClassNotFoundException;
use shmurakami\Elephoot\Test\TestCase;

class AstTest extends TestCase
{

    public function testClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);
        (new AstLoader(new ClassMap([])))->loadByClass(new ClassContext('_' . Application::class));
    }

}
