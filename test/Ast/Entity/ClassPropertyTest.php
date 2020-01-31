<?php

namespace shmurakami\Spice\Test\Ast\Entity;

use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Entity\ClassProperty;
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
        $context = new Context($namespace, $className);

        //
        $unionTypesWithAdditionalComment = $propertyNodes[0];
        $classProperty = new ClassProperty($context, $unionTypesWithAdditionalComment);
        $fqcnList = $classProperty->classFqcnListFromDocComment();
        $expect = [Application::class, ExtendApplication::class];
        $this->assertEquals($expect, $fqcnList);

        //
        $fqcnType = $propertyNodes[1];
        $classProperty = new ClassProperty($context, $fqcnType);
        $fqcnList = $classProperty->classFqcnListFromDocComment();
        $expect = [Application::class];
        $this->assertEquals($expect, $fqcnList);

        //
        $simpleType = $propertyNodes[2];
        $classProperty = new ClassProperty($context, $simpleType);
        $fqcnList = $classProperty->classFqcnListFromDocComment();
        $expect = [ExtendApplication::class];
        $this->assertEquals($expect, $fqcnList);

        //
        $wrongType = $propertyNodes[3];
        $classProperty = new ClassProperty($context, $wrongType);
        $fqcnList = $classProperty->classFqcnListFromDocComment();
        // does not exist but parsable as string in ClassProperty context
        $expect = ["shmurakami\\Spice\\Example\\NotExistingClass"];
        $this->assertEquals($expect, $fqcnList);
    }

}
