<?php

namespace shmurakami\Spice\Test\Ast;

use BreakingPsr;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Request;
use shmurakami\Spice\Test\TestCase;

class RequestTest extends TestCase
{
    public function testGetOutputDirectory()
    {
        $request = new Request(Request::MODE_CLASS, '', '', __DIR__ . '/resource/config.json');
        $this->assertEquals('/tmp/foobar', $request->getOutputDirectory());

        // option is prioritized
        $request = new Request(Request::MODE_CLASS, '', 'output', '');
        $this->assertEquals('output', $request->getOutputDirectory());
    }

    public function testGetTargetByClass()
    {
        $targetClass = Request::class;
        $expect = new ClassContext('shmurakami\\Spice\\Ast\\Request');

        $request = new Request(Request::MODE_CLASS, '', '', __DIR__ . '/resource/config.json');
        $this->assertEquals($expect, $request->getTarget());

        // option is prioritized
        $request = new Request(Request::MODE_CLASS, $targetClass, '', __DIR__ . '/resource/config.json');
        $this->assertEquals($expect, $request->getTarget());
    }

    public function testGetTargetByMethod()
    {
        $targetClass = Request::class;
        $request = new Request(Request::MODE_CLASS, '', '', __DIR__ . '/resource/config_target_method.json');
        $this->assertEquals(new MethodContext($targetClass, 'sample'), $request->getTarget());

        // option is prioritized
        $target = "$targetClass@sample";
        $request = new Request(Request::MODE_CLASS, $target, '', __DIR__ . '/resource/config_target_method.json');
        $this->assertEquals(new MethodContext($targetClass, 'sample'), $request->getTarget());

        // even combined, prior argument
        $request = new Request(Request::MODE_CLASS, $targetClass, '', __DIR__ . '/resource/config_target_method.json');
        $this->assertEquals(new ClassContext($targetClass), $request->getTarget());
    }

    public function testIsValid()
    {
        $request = new Request(Request::MODE_CLASS, 'target', 'output', '');
        $this->assertTrue($request->isValid());

        $request = new Request(Request::MODE_CLASS, '', '', '');
        $this->assertFalse($request->isValid());


        $request = new Request(Request::MODE_CLASS, '', '', __DIR__ . '/resource/config.json');
        $this->assertTrue($request->isValid());

        $request = new Request(Request::MODE_CLASS, '', '', __DIR__ . '/resource/config_not_enough.json');
        $this->assertFalse($request->isValid());
    }

    public function testGetClassMap()
    {
        // argument is not supported
        $request = new Request(Request::MODE_CLASS, '', '', __DIR__ . '/resource/config.json');
        $expect = new ClassMap([
            BreakingPsr::class => '/path/to/src/Example/other/BreakingPsr.php',
        ]);
        $this->assertEquals($expect, $request->getClassMap());
    }
}
