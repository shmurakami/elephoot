<?php

namespace shmurakami\Spice\Test\Ast;

use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Ast\Entity\MethodAst;
use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Exception\ClassNotFoundException;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Test\TestCase;

class AstTest extends TestCase
{
    public function testAst()
    {
        $astLoader = new AstLoader(new ClassMap([]));
        $classAst = $astLoader->loadByClass(Application::class);
        $this->assertInstanceOf(ClassAst::class, $classAst);

        $methodAst = $classAst->parseMethod('sampleMethod');
//        $methodAst = $classAst->parseMethod('callNest');
        $this->assertInstanceOf(MethodAst::class, $methodAst);

        $methodCallNodes = $methodAst->methodCallNodes();
        $this->assertEquals([], $methodCallNodes);
    }

    public function testClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);
        (new AstLoader(new ClassMap([])))->loadByClass('_' . Application::class);
    }

    public function testMethodNotFoundException()
    {
        $this->expectException(MethodNotFoundException::class);
        (new AstLoader(new ClassMap([])))
            ->loadByClass(Application::class)
            ->parseMethod('notExistMethod');
    }

}
