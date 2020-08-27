<?php

namespace shmurakami\Spice\Test\Ast\Entity;

use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Entity\ClassProperty;
use shmurakami\Spice\Ast\Parser\ContextParser;
use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Example\ExtendApplication;
use shmurakami\Spice\Test\TestCase;

class ClassPropertyTest extends TestCase
{
    public function testClassFqcnListFromDocComment()
    {
        $somePropertyClassPath = __DIR__ . '/../../../src/Example/SomeProperty.php';
        $node = \ast\parse_file($somePropertyClassPath, 70);
        $propertyNodes = $node->children[1]->children['stmts']->children;

        // any way to write these tests more easier?
        $namespace = 'shmurakami\\Spice\\Example';
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
        // does not exist but parsable as string in ClassProperty context
        $expect = [new ClassContext("shmurakami\\Spice\\Example\\NotExistingClass")];
        $this->assertEquals($expect, $fqcnList);
    }

}
