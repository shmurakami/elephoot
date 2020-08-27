<?php

namespace shmurakami\Spice\Test\Ast\Context;

use shmurakami\Spice\Ast\Context\ClassContext;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{

    public function testFqcn()
    {
        $context = new ClassContext('Foo\\Bar\\Class');
        $this->assertEquals('Foo\\Bar\\Class', $context->fqcn());
    }

    public function testGlobalNamespace()
    {
        $context = new ClassContext('Class');
        $this->assertEquals('\\Class', $context->fqcn());
    }
}
