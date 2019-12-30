<?php

namespace shmurakami\Spice\Test;

use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Example\Client;
use shmurakami\Spice\Example\Import\ByImport;
use shmurakami\Spice\Example\Method\DocComment;
use shmurakami\Spice\Example\Method\TypeHinting;
use shmurakami\Spice\Example\ReturnType\ReturnInDocComment;
use shmurakami\Spice\Example\ReturnType\ReturnType;
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

        $methodDocCommentTree = new ClassTree(new ClassTreeNode(DocComment::class));
        $methodTypeHintingTree = new ClassTree(new ClassTreeNode(TypeHinting::class));
        $applicationTree->add($methodDocCommentTree);
        $applicationTree->add($methodTypeHintingTree);

        $returnTypeTree = new ClassTree(new ClassTreeNode(ReturnType::class));
//        $returnInDocCommentTree = new ClassTree(new ClassTreeNode(ReturnInDocComment::class));
        $applicationTree->add($returnTypeTree);
//        $applicationTree->add($returnInDocCommentTree);

        // root client tree
        $clientTree = new ClassTree(new ClassTreeNode(Client::class));
        $clientTree->add($applicationTree);

        $expect = $clientTree;

        $this->assertEquals($expect, $actual);
    }
}
