<?php

namespace shmurakami\Spice\Test\Ast;

use BreakingPsr;
use shmurakami\Spice\Ast\ClassMap;
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
        $request = new Request(Request::MODE_CLASS, '', '', __DIR__ . '/resource/config.json');
        $this->assertEquals(['class' => $targetClass, 'method' => ''], $request->getTarget());

        // option is prioritized
        $request = new Request(Request::MODE_CLASS, $targetClass, '', __DIR__ . '/resource/config.json');
        $this->assertEquals(['class' => $targetClass, 'method' => ''], $request->getTarget());
    }

    public function testGetTargetByMethod()
    {
        $targetClass = Request::class;
        $request = new Request(Request::MODE_CLASS, '', '', __DIR__ . '/resource/config_target_method.json');
        $this->assertEquals(['class' => $targetClass, 'method' => 'sample'], $request->getTarget());

        // option is prioritized
        $target = "$targetClass@sample";
        $request = new Request(Request::MODE_CLASS, $target, '', __DIR__ . '/resource/config_target_method.json');
        $this->assertEquals(['class' => $targetClass, 'method' => 'sample'], $request->getTarget());

        // even combined, prior argument
        $request = new Request(Request::MODE_CLASS, $targetClass, '', __DIR__ . '/resource/config_target_method.json');
        $this->assertEquals(['class' => $targetClass, 'method' => ''], $request->getTarget());
    }

    /**
     * @dataProvider dataProviderForTestIsClassMode
     */
    public function testIsClassMode(string $mode, string $configFile, bool $expect)
    {
        $request = new Request($mode, '', '', $configFile);
        $this->assertSame($expect, $request->isClassMode());
    }

    public function dataProviderForTestIsClassMode()
    {
        return [
            ['',                   __DIR__ . '/resource/config.json', true],
            [Request::MODE_METHOD, __DIR__ . '/resource/config.json', false],
            ['',                  __DIR__ . '/resource/config_target_method.json', false],
            [Request::MODE_CLASS, __DIR__ . '/resource/config_target_method.json', true],
            [Request::MODE_METHOD, '', false],
            [Request::MODE_CLASS,  '', true],
        ];
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
