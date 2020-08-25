<?php

namespace shmurakami\Spice\Test\Ast\Context;

use shmurakami\Spice\Ast\Context\MethodContext;
use PHPUnit\Framework\TestCase;

class MethodContextTest extends TestCase
{

    public function testFqcn()
    {
        $context = new MethodContext('Foo\\Bar\\Class', 'method');
        $this->assertEquals('Foo\\Bar\\Class@method', $context->fqcn());
    }

    public function testGlobalNamespace()
    {
        $context = new MethodContext('Class', 'method');
        $this->assertEquals('\\Class@method', $context->fqcn());
    }
}
