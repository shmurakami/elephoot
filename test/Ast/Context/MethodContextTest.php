<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Test\Ast\Context;

use shmurakami\Elephoot\Ast\Context\MethodContext;
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
