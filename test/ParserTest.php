<?php

namespace shmurakami\Spice\Test;

use BreakingPsr;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Request;
use shmurakami\Spice\Example\Application;
use shmurakami\Spice\Example\Client;
use shmurakami\Spice\Example\ExtendApplication;
use shmurakami\Spice\Example\Import\ByImport;
use shmurakami\Spice\Example\Inherit\InheritClass;
use shmurakami\Spice\Example\Inherit\InheritDependency;
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

    public function testBuildClassTree()
    {
        $request = new Request(Request::MODE_CLASS, Client::class, '', '');
        $parser = new Parser($request);

        $classMap = new ClassMap([
            BreakingPsr::class => __DIR__ . '/../src/Example/other/BreakingPsr.php',
        ]);

        $classAst = (new AstLoader($classMap))->loadByClass(new ClassContext(Client::class));
        $actual = $parser->buildClassTree($classAst, $classMap);

        $applicationTree = new ClassTree(new ClassTreeNode(new ClassContext(Application::class)));

        $importedClassTree = new ClassTree(new ClassTreeNode(new ClassContext(ByImport::class)));
        $applicationTree->add($importedClassTree);

        $inheritClassTree = new ClassTree(new ClassTreeNode(new ClassContext(InheritClass::class)));
        // depended class also has own tree
        $inheritDependencyClassTree = new ClassTree(new ClassTreeNode(new ClassContext(InheritDependency::class)));
        $inheritClassTree->add($inheritDependencyClassTree);
        $applicationTree->add($inheritClassTree);

        $interfaceClassTree = new ClassTree(new ClassTreeNode(new ClassContext(Implement1::class)));
        $interfaceClassTree2 = new ClassTree(new ClassTreeNode(new ClassContext(Implement2::class)));
        $applicationTree->add($interfaceClassTree);
        $applicationTree->add($interfaceClassTree2);

        $traitClassTree = new ClassTree(new ClassTreeNode(new ClassContext(UsingTrait::class)));
        $applicationTree->add($traitClassTree);

        $methodDocCommentTree = new ClassTree(new ClassTreeNode(new ClassContext(DocComment::class)));
        $methodTypeHintingTree = new ClassTree(new ClassTreeNode(new ClassContext(TypeHinting::class)));
        $applicationTree->add($methodDocCommentTree);
        $applicationTree->add($methodTypeHintingTree);

        $returnTypeTree = new ClassTree(new ClassTreeNode(new ClassContext(ReturnType::class)));
        $returnInDocCommentTree = new ClassTree(new ClassTreeNode(new ClassContext(ReturnInDocComment::class)));
        $applicationTree->add($returnTypeTree);
        $applicationTree->add($returnInDocCommentTree);


        $simplyNewTree = new ClassTree(new ClassTreeNode(new ClassContext(SimplyNew::class)));
        $applicationTree->add($simplyNewTree);

        $newInClosureTree = new ClassTree(new ClassTreeNode(new ClassContext(NewInClosure::class)));
        $applicationTree->add($newInClosureTree);

        // new statement has nested dependencies
        $newStatementTree = new ClassTree(new ClassTreeNode(new ClassContext(NewStatement::class)));
        $newStatementArgumentTree = new ClassTree(new ClassTreeNode(new ClassContext(NewStatementArgument::class)));
        $newStatementArgumentArgumentTree = new ClassTree(new ClassTreeNode(new ClassContext(NewStatementArgumentArgument::class)));
        $newStatementArgumentTree->add($newStatementArgumentArgumentTree);
        $newStatementTree->add($newStatementArgumentTree);

        $applicationTree->add($newStatementTree);
        // order by internally
        $applicationTree->add($newStatementArgumentArgumentTree);
        $applicationTree->add($newStatementArgumentTree);

        // static method call
        $staticMethodCallTree = new ClassTree(new ClassTreeNode(new ClassContext(StaticMethodCall::class)));
        $staticMethodCallArgumentTree = new ClassTree(new ClassTreeNode(new ClassContext(StaticMethodCallArgument::class)));
        $staticMethodCallTree->add($staticMethodCallArgumentTree);
        $applicationTree->add($staticMethodCallTree);
        $applicationTree->add($staticMethodCallArgumentTree);

        // breaking PSR-4 rule class
        $breakingPsrClassTree = new ClassTree(new ClassTreeNode(new ClassContext('\\' . BreakingPsr::class)));
        $applicationTree->add($breakingPsrClassTree);

        // root client tree
        $clientTree = new ClassTree(new ClassTreeNode(new ClassContext(Client::class)));
        $clientTree->add($applicationTree);

        // client has other dependency
        $extendApplicationTree = new ClassTree(new ClassTreeNode(new ClassContext(ExtendApplication::class)));
        $clientTree->add($extendApplicationTree);

        $expect = $clientTree;

        $this->assertEquals($expect, $actual);
    }
}
