<?php

namespace shmurakami\Spice;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Ast\Entity\MethodAst;
use shmurakami\Spice\Ast\Request;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Ast\Resolver\FileAstResolver;
use shmurakami\Spice\Output\Adaptor\AdaptorConfig;
use shmurakami\Spice\Output\Adaptor\GraphpAdaptor;
use shmurakami\Spice\Output\ClassTree;
use shmurakami\Spice\Output\Drawer;
use shmurakami\Spice\Output\MethodCallTree;
use shmurakami\Spice\Output\ObjectRelationTree;

class Parser
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function parse(): ObjectRelationTree
    {
        $context = $this->request->getTarget();

        if ($context instanceof ClassContext) {
            return $this->parseByClass($context);
        }

        /** @var MethodContext $context */
        return $this->parseByMethod($context);
    }

    /**
     * @throws Exception\ClassNotFoundException
     * @throws ReflectionException
     * @throws Exception\MethodNotFoundException
     */
    public function parseByMethod(MethodContext $context): ObjectRelationTree
    {
        /*
         * parse AST for Class and method
         * pool to buffer
         *
         * output graph
         */
//        $classAst = (new AstLoader())->loadByClass($classFqcn);
//        $methodAst = $classAst->parseMethod($methodName);

//        $methodCallTree = $this->buildMethodCallTree($methodAst);
        // TODO output from methodCallTree
    }

    public function parseByClass(ClassContext $context): ClassTree
    {
        $classMap = $this->request->getClassMap();
        $classAst = (new AstLoader($classMap))->loadByClass($context);
        return $this->buildClassTree($classAst, $classMap);
    }

    public function buildClassTree(ClassAst $classAst, ClassMap $classMap): ClassTree
    {
        $tree = new ClassTree($classAst->treeNode());

        $resolver = new FileAstResolver($classMap);
        $fileAst = $resolver->resolve($classAst->fqcn());
        if (!$fileAst) {
            return $tree;
        }

        $classAstResolver = new ClassAstResolver($classMap);
        $dependencies = $fileAst->dependentClassAstList($classAstResolver);
        // TODO set memo dependency and result tree
        foreach ($dependencies as $dependentClassAst) {
            $tree->add($this->buildClassTree($dependentClassAst, $classMap));
        }
        return $tree;
    }

    private function buildMethodCallTree(MethodAst $methodAst): MethodCallTree
    {
        $tree = new MethodCallTree($methodAst->treeNode());

        foreach ($methodAst->methodCallNodes() as $methodCallAstNode) {
            $methodCallTree = $this->buildMethodCallTree($methodCallAstNode);
            $tree->add($methodCallTree);
        }
        return $tree;
    }
}
