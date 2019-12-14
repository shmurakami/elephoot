<?php

namespace shmurakami\Spice;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\Entity\MethodAst;
use shmurakami\Spice\Output\MethodCallTree;
use shmurakami\Spice\Output\TreeNode;

class Parser
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $classFqcn
     * @param string $methodName
     * @throws Exception\ClassNotFoundException
     * @throws ReflectionException
     * @throws Exception\MethodNotFoundException
     */
    public function parse(string $classFqcn, string $methodName): void
    {
        /*
         * parse AST for Class and method
         * pool to buffer
         *
         * output graph
         */
        $classAst = (new AstLoader())->loadByClass($classFqcn);
        $methodAst = $classAst->parseMethod($methodName);

        $methodCallTree = $this->_parse($methodAst);
        // TODO output from methodCallTree
    }

    private function _parse(MethodAst $methodAst): MethodCallTree
    {
        $tree = new MethodCallTree($methodAst->treeNode());

        foreach ($methodAst->methodCallNodes() as $methodCallAstNode) {
            $methodCallTree = $this->_parse($methodCallAstNode);
            $tree->add($methodCallTree);
        }
        return $tree;
    }
}
