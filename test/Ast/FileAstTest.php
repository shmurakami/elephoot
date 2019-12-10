<?php

namespace shmurakami\Spice\Test\Ast;

use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassAst;
use shmurakami\Spice\Ast\FileAst;
use shmurakami\Spice\Ast\MethodAst;
use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Exception\ClassNotFoundException;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Test\TestCase;

class FileAstTest extends TestCase
{
    public function testAst()
    {
        $astLoader = new AstLoader();
        $fileAst = $astLoader->loadByClass(Application::class);
        $this->assertInstanceOf(FileAst::class, $fileAst);

        // if namespace does not exist?
        // if doing declare(strict_types=1)?
        $this->assertSame('shmurakami\Spice\Example', $fileAst->getNamespace());

        $classAst = $fileAst->parseClass();
        $this->assertInstanceOf(ClassAst::class, $classAst);

        $methodAst = $classAst->parseMethod('sampleMethod');
        $this->assertInstanceOf(MethodAst::class, $methodAst);
    }

    public function testClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);
        (new AstLoader())->loadByClass('_' . Application::class);
    }

    public function testMethodNotFoundException()
    {
        $this->expectException(MethodNotFoundException::class);
        $fileAst = (new AstLoader())->loadByClass(Application::class);
        $classAst = $fileAst->parseClass();
        $classAst->parseMethod('notExistMethodName');
    }

}
