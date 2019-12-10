<?php

namespace shmurakami\Spice\Ast;

use ast\Node;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Stub\Kind;

class ClassAst
{
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var Node
     */
    private $classRootNode;
    /**
     * @var string
     */
    private $className;

    /**
     * ClassAst constructor.
     */
    public function __construct(string $namespace, string $className, Node $classRootNode)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->classRootNode = $classRootNode;
    }

    /**
     * @param string $method
     * @return MethodAst
     * @throws MethodNotFoundException
     */
    public function parseMethod(string $method): MethodAst
    {
        /** @var Node $statementNode */
        $statementNode = $this->classRootNode->children['stmts'];
        assert($statementNode->kind === Kind::AST_STMT_LIST, 'unexpected node kind');

        foreach ($statementNode->children as $node) {
            if ($node->kind === Kind::AST_METHOD) {
                $rootMethod = $node->children['name'];
                if ($rootMethod === $method) {
                    return new MethodAst($this->namespace, $this->className, $node);
                }
            }
        }
        throw new MethodNotFoundException();
    }
}
