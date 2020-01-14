<?php

namespace shmurakami\Spice;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Ast\Entity\MethodAst;
use shmurakami\Spice\Ast\Request;
use shmurakami\Spice\Ast\Resolver\FileAstResolver;
use shmurakami\Spice\Output\Adaptor\AdaptorConfig;
use shmurakami\Spice\Output\Adaptor\GraphpAdaptor;
use shmurakami\Spice\Output\ClassTree;
use shmurakami\Spice\Output\Drawer;
use shmurakami\Spice\Output\MethodCallTree;

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

    public function parse()
    {
        [$classFqcn, $method] = $this->request->getTarget();

        if ($this->request->isClassMode()) {
            $this->parseByClass($classFqcn);
            return;
        }
        $this->parseByMethod($classFqcn, $method);
    }

    /**
     * @param string $classFqcn
     * @param string $methodName
     * @throws Exception\ClassNotFoundException
     * @throws ReflectionException
     * @throws Exception\MethodNotFoundException
     */
    public function parseByMethod(string $classFqcn, string $methodName): void
    {
        /*
         * parse AST for Class and method
         * pool to buffer
         *
         * output graph
         */
        $classAst = (new AstLoader())->loadByClass($classFqcn);
        $methodAst = $classAst->parseMethod($methodName);

        $methodCallTree = $this->buildMethodCallTree($methodAst);
        // TODO output from methodCallTree
    }

    public function parseByClass(string $classFqcn): void
    {
        $classTree = $this->_parseByClass($classFqcn);
        $graphpAdaptor = new GraphpAdaptor(new AdaptorConfig($this->request->getOutputDirectory()));
        $drawer = new Drawer($graphpAdaptor);
        $filepath = $drawer->draw($classTree);
        // TODO should not do
        echo $filepath . "\n";
    }

    /**
     * public for test
     *
     * @param string $classFqcn
     * @return ClassTree
     * @throws Exception\ClassNotFoundException
     * @throws ReflectionException
     */
    public function _parseByClass(string $classFqcn): ClassTree
    {
        $classAst = (new AstLoader())->loadByClass($classFqcn);
        return $this->buildClassTree($classAst);
    }

    private function buildClassTree(ClassAst $classAst): ClassTree
    {
        $tree = new ClassTree($classAst->treeNode());

        $resolver = FileAstResolver::getInstance();
        $fileAst = $resolver->resolve($classAst->fqcn());
        if (!$fileAst) {
            return $tree;
        }

        $dependencies = $fileAst->dependentClassAstList();
        foreach ($dependencies as $node) {
            $tree->add($this->buildClassTree($node));
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
