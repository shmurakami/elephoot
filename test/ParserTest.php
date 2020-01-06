<?php

namespace shmurakami\Spice\Test;

use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Example\Client;
use shmurakami\Spice\Example\Import\ByImport;
use shmurakami\Spice\Example\Inherit\InheritClass;
use shmurakami\Spice\Example\Interfaces\Implement1;
use shmurakami\Spice\Example\Interfaces\Implement2;
use shmurakami\Spice\Example\Method\DocComment;
use shmurakami\Spice\Example\Method\TypeHinting;
use shmurakami\Spice\Example\NewStatement\NewInClosure;
use shmurakami\Spice\Example\NewStatement\NewStatement;
use shmurakami\Spice\Example\NewStatement\NewStatementArgument;
use shmurakami\Spice\Example\NewStatement\NewStatementArgumentArgument;
use shmurakami\Spice\Example\NewStatement\SimplyNew;
use shmurakami\Spice\Example\ReturnType\ReturnInDocComment;
use shmurakami\Spice\Example\ReturnType\ReturnType;
use shmurakami\Spice\Example\StaticMethod\StaticMethodCall;
use shmurakami\Spice\Example\StaticMethod\StaticMethodCallArgument;
use shmurakami\Spice\Example\Traits\UsingTrait;
use shmurakami\Spice\Output\ClassTree;
use shmurakami\Spice\Output\ClassTreeNode;
use shmurakami\Spice\Parser;

class ParserTest extends TestCase
{
    public function testClassTreeNode()
    {
        $importClassTree = new ClassTree(new ClassTreeNode(ByImport::class));
        $applicationTree = new ClassTree(new ClassTreeNode(Application::class));
        $applicationTree->add($importClassTree);

        $clientTree = new ClassTree(new ClassTreeNode(Client::class));
        $clientTree->add($applicationTree);

        $expect = [
            'className'  => Client::class,
            'childNodes' => [
                [
                    'className'  => Application::class,
                    'childNodes' => [
                        [
                            'className'  => ByImport::class,
                            'childNodes' => [],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expect, $clientTree->toArray());
    }

    public function testParseClassRelation()
    {
        $importClassTree = new ClassTree(new ClassTreeNode(ByImport::class));
        $applicationTree = new ClassTree(new ClassTreeNode(Application::class));
        $applicationTree->add($importClassTree);

        $clientTree = new ClassTree(new ClassTreeNode(Client::class));
        $clientTree->add($applicationTree);

        $expect = $clientTree;

        $parser = new Parser();
        $actual = $parser->parseClassRelation(Client::class);
        $this->assertEquals($expect, $actual);
    }

    public function testStepByStep()
    {
        $parser = new Parser();
        $actual = $parser->parseClassRelation(Client::class);

        $applicationTree = new ClassTree(new ClassTreeNode(Application::class));

        $importedClassTree = new ClassTree(new ClassTreeNode(ByImport::class));
        $applicationTree->add($importedClassTree);

        $inheritClassTree = new ClassTree(new ClassTreeNode(InheritClass::class));
        $applicationTree->add($inheritClassTree);

        $interfaceClassTree = new ClassTree(new ClassTreeNode(Implement1::class));
        $interfaceClassTree2 = new ClassTree(new ClassTreeNode(Implement2::class));
        $applicationTree->add($interfaceClassTree);
        $applicationTree->add($interfaceClassTree2);

        $traitClassTree = new ClassTree(new ClassTreeNode(UsingTrait::class));
        $applicationTree->add($traitClassTree);

        $methodDocCommentTree = new ClassTree(new ClassTreeNode(DocComment::class));
        $methodTypeHintingTree = new ClassTree(new ClassTreeNode(TypeHinting::class));
        $applicationTree->add($methodDocCommentTree);
        $applicationTree->add($methodTypeHintingTree);

        $returnTypeTree = new ClassTree(new ClassTreeNode(ReturnType::class));
        $returnInDocCommentTree = new ClassTree(new ClassTreeNode(ReturnInDocComment::class));
        $applicationTree->add($returnTypeTree);
        $applicationTree->add($returnInDocCommentTree);


        $simplyNewTree = new ClassTree(new ClassTreeNode(SimplyNew::class));
        $applicationTree->add($simplyNewTree);

        $newInClosureTree = new ClassTree(new ClassTreeNode(NewInClosure::class));
        $applicationTree->add($newInClosureTree);

        // new statement has nested dependencies
        $newStatementTree = new ClassTree(new ClassTreeNode(NewStatement::class));
        $newStatementArgumentTree = new ClassTree(new ClassTreeNode(NewStatementArgument::class));
        $newStatementArgumentArgumentTree = new ClassTree(new ClassTreeNode(NewStatementArgumentArgument::class));
        $newStatementArgumentTree->add($newStatementArgumentArgumentTree);
        $newStatementTree->add($newStatementArgumentTree);

        $applicationTree->add($newStatementTree);
        $applicationTree->add($newStatementArgumentTree);
        $applicationTree->add($newStatementArgumentArgumentTree);

        // static method call
        $staticMethodCallTree = new ClassTree(new ClassTreeNode(StaticMethodCall::class));
        $staticMethodCallArgumentTree = new ClassTree(new ClassTreeNode(StaticMethodCallArgument::class));
        $staticMethodCallTree->add($staticMethodCallArgumentTree);
        $applicationTree->add($staticMethodCallTree);
        $applicationTree->add($staticMethodCallArgumentTree);

        // root client tree
        $clientTree = new ClassTree(new ClassTreeNode(Client::class));
        $clientTree->add($applicationTree);


        $expect = $clientTree;

        $this->assertEquals($expect, $actual);
    }
}
