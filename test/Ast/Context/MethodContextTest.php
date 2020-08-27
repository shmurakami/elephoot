<?php

namespace shmurakami\Spice\Test\Ast\Context;

use shmurakami\Spice\Ast\Context\MethodContext;
use PHPUnit\Framework\TestCase;

class MethodContextTest extends TestCase
{

    public function testFullName()
    {
        $context = new MethodContext('Foo\\Bar\\Class', 'method');
        $this->assertEquals('Foo\\Bar\\Class@method', $context->fullName());
    }

    public function testGlobalNamespaceFullname()
    {
        $context = new MethodContext('Class', 'method');
        $this->assertEquals('\\Class@method', $context->fullName());
    }
}
