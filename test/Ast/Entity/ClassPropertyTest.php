<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Test\Ast\Entity;

use shmurakami\Elephoot\Ast\ClassMap;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Entity\ClassProperty;
use shmurakami\Elephoot\Ast\Parser\ContextParser;
use shmurakami\Elephoot\Example\Application;
use shmurakami\Elephoot\Example\ExtendApplication;
use shmurakami\Elephoot\Test\TestCase;

class ClassPropertyTest extends TestCase
{
    public function testClassFqcnListFromDocComment()
    {
        $somePropertyClassPath = __DIR__ . '/../../../src/Example/SomeProperty.php';
        $node = \ast\parse_file($somePropertyClassPath, 70);
        $propertyNodes = $node->children[2]->children['stmts']->children;

        // any way to write these tests more easier?
        $namespace = 'shmurakami\\Elephoot\\Example';
        $className = 'SomeProperty';

        $contextParser = new ContextParser(new ClassMap([]));

        //
        $unionTypesWithAdditionalComment = $propertyNodes[0];
        $classProperty = new ClassProperty($contextParser, new ClassContext($namespace . '\\' . $className), $unionTypesWithAdditionalComment);
        $fqcnList = $classProperty->classContextListFromDocComment();
        $expect = [new ClassContext(Application::class), new ClassContext(ExtendApplication::class)];
        $this->assertEquals($expect, $fqcnList);

        //
        $fqcnType = $propertyNodes[1];
        $classProperty = new ClassProperty($contextParser, new ClassContext($namespace . '\\' . $className), $fqcnType);
        $fqcnList = $classProperty->classContextListFromDocComment();
        $expect = [new ClassContext(Application::class)];
        $this->assertEquals($expect, $fqcnList);

        //
        $simpleType = $propertyNodes[2];
        $classProperty = new ClassProperty($contextParser, new ClassContext($namespace . '\\' . $className), $simpleType);
        $fqcnList = $classProperty->classContextListFromDocComment();
        $expect = [new ClassContext(ExtendApplication::class)];
        $this->assertEquals($expect, $fqcnList);

        //
        $wrongType = $propertyNodes[3];
        $classProperty = new ClassProperty($contextParser, new ClassContext($namespace . '\\' . $className), $wrongType);
        $fqcnList = $classProperty->classContextListFromDocComment();
        // not exist class should be omitted
        $expect = [];
        $this->assertEquals($expect, $fqcnList);
    }

}
