<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Test;

use BreakingPsr;
use shmurakami\Elephoot\Ast\AstLoader;
use shmurakami\Elephoot\Ast\ClassMap;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Request;
use shmurakami\Elephoot\Example\Application;
use shmurakami\Elephoot\Example\CircularReference\CircularReference1;
use shmurakami\Elephoot\Example\CircularReference\CircularReference2;
use shmurakami\Elephoot\Example\Client;
use shmurakami\Elephoot\Example\Enum\Animal;
use shmurakami\Elephoot\Example\ExtendApplication;
use shmurakami\Elephoot\Example\Import\ByImport;
use shmurakami\Elephoot\Example\Inherit\InheritClass;
use shmurakami\Elephoot\Example\Inherit\InheritDependency;
use shmurakami\Elephoot\Example\Interfaces\Implement1;
use shmurakami\Elephoot\Example\Interfaces\Implement2;
use shmurakami\Elephoot\Example\Method\DocComment;
use shmurakami\Elephoot\Example\Method\TypeHinting;
use shmurakami\Elephoot\Example\NewStatement\NewInClosure;
use shmurakami\Elephoot\Example\NewStatement\NewInShorthandClosure;
use shmurakami\Elephoot\Example\NewStatement\NewStatement;
use shmurakami\Elephoot\Example\NewStatement\NewStatementArgument;
use shmurakami\Elephoot\Example\NewStatement\NewStatementArgumentArgument;
use shmurakami\Elephoot\Example\NewStatement\SimplyNew;
use shmurakami\Elephoot\Example\ReturnType\ReturnInDocComment;
use shmurakami\Elephoot\Example\ReturnType\ReturnType;
use shmurakami\Elephoot\Example\ReturnType\UnionReturnType;
use shmurakami\Elephoot\Example\StaticMethod\StaticMethodCall;
use shmurakami\Elephoot\Example\StaticMethod\StaticMethodCallArgument;
use shmurakami\Elephoot\Example\Traits\UsingTrait;
use shmurakami\Elephoot\Output\ClassTree;
use shmurakami\Elephoot\Output\ClassTreeNode;
use shmurakami\Elephoot\Parser;

class ParserTest extends TestCase
{

    public function testBuildClassTree()
    {
        $request = new Request(Request::MODE_CLASS, Client::class, '', '');
        $parser = new Parser($request);

        $classMap = new ClassMap([
            BreakingPsr::class => __DIR__ . '/../src/Example/other/BreakingPsr.php',
        ]);

        $actual = $parser->parseByClass($request->getTarget(), $classMap);

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
        $unionReturnTypeTree = new ClassTree(new ClassTreeNode(new ClassContext(UnionReturnType::class)));
        $returnInDocCommentTree = new ClassTree(new ClassTreeNode(new ClassContext(ReturnInDocComment::class)));
        $applicationTree->add($returnTypeTree);
        $applicationTree->add($unionReturnTypeTree);
        $applicationTree->add($returnInDocCommentTree);


        $simplyNewTree = new ClassTree(new ClassTreeNode(new ClassContext(SimplyNew::class)));
        $applicationTree->add($simplyNewTree);

        $newInClosureTree = new ClassTree(new ClassTreeNode(new ClassContext(NewInClosure::class)));
        $applicationTree->add($newInClosureTree);

        $newInShorthandClosureTree = new ClassTree(new ClassTreeNode(new ClassContext(NewInShorthandClosure::class)));
        $applicationTree->add($newInShorthandClosureTree);

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

        // PHP Native Enum
        $enumTree = new ClassTree(new ClassTreeNode(new ClassContext(Animal::class)));
        $applicationTree->add($enumTree);

        // root client tree
        $clientTree = new ClassTree(new ClassTreeNode(new ClassContext(Client::class)));
        $clientTree->add($applicationTree);

        // client has other dependency
        $extendApplicationTree = new ClassTree(new ClassTreeNode(new ClassContext(ExtendApplication::class)));
        $clientTree->add($extendApplicationTree);

        // circular reference should be overwritten without childTree of original
        // 1 and 2 depend each other but child of 2 does not have 1
        $secondCircularReferenceTree = new ClassTree(new ClassTreeNode(new ClassContext(CircularReference2::class)));
        $secondCircularReferenceTree->add(new ClassTree(new ClassTreeNode(new ClassContext(CircularReference1::class))));
        $circularReferenceTree = new ClassTree(new ClassTreeNode(new ClassContext(CircularReference1::class)));
        $circularReferenceTree->add($secondCircularReferenceTree);
        $clientTree->add($circularReferenceTree);

        $expect = $clientTree;

        $this->assertEquals($expect, $actual);
    }
}
