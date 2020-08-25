<?php

namespace shmurakami\Spice\Test\Ast\Context;

use shmurakami\Spice\Ast\Context\Context;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{

    public function testFqcn()
    {
        $context = new Context('Foo\\Bar', 'Class');
        $this->assertEquals('Foo\\Bar\\Class', $context->fqcn());
    }

    public function testGlobalNamespace()
    {
        $context = new Context('', 'Class');
        $this->assertEquals('\\Class', $context->fqcn());
    }
}
