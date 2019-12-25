<?php

namespace shmurakami\Spice\Test;

use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Example\Client;
use shmurakami\Spice\Example\Nest\NestClass;
use shmurakami\Spice\Output\ClassTree;
use shmurakami\Spice\Output\ClassTreeNode;
use shmurakami\Spice\Parser;

class ParserTest extends TestCase
{
    public function testClassTreeNode()
    {
        $nestClassTree = new ClassTree(new ClassTreeNode(NestClass::class));
        $applicationTree = new ClassTree(new ClassTreeNode(Application::class));
        $applicationTree->add($nestClassTree);

        $clientTree = new ClassTree(new ClassTreeNode(Client::class));
        $clientTree->add($applicationTree);

        $expect = [
            'className'  => Client::class,
            'childNodes' => [
                [
                    'className'  => Application::class,
                    'childNodes' => [
                        [
                            'className'  => NestClass::class,
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
//        $nestClassTree = new ClassTree(new ClassTreeNode(NestClass::class));
        $applicationTree = new ClassTree(new ClassTreeNode(Application::class));
//        $applicationTree->add($nestClassTree);

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

        $nestClassTree = new ClassTree(new ClassTreeNode(NestClass::class));
        $applicationTree = new ClassTree(new ClassTreeNode(Application::class));
        $applicationTree->add($nestClassTree);

        $clientTree = new ClassTree(new ClassTreeNode(Client::class));
        $clientTree->add($applicationTree);

        $expect = $clientTree;

        $this->assertEquals($expect, $actual);
    }
}
