<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Exception\MethodNotFoundException;
use shmurakami\Spice\Stub\Kind;

class ClassAst
{
    /**
     * @var ClassProperty[]
     */
    private $properties = [];
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

        $this->parse($namespace, $className, $classRootNode);
    }

    private function parse(string $namespace, string $className, Node $classRootNode): void
    {
        $classStatements = $classRootNode->children['stmts'] ?? (object)['children' => []];

        // TODO AST should has it? consider to make Ast Parser
        foreach ($classStatements->children as $node) {
            if ($node->kind === Kind::AST_PROP) {
                $classProperty = new ClassProperty($namespace, $className, $node);
                if ($classProperty->isCallable()) {
                    $this->properties[] = $classProperty;
                }
            }
        }
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
                    return new MethodAst($this->namespace, $this->className, $this->properties, $node);
                }
            }
        }
        throw new MethodNotFoundException();
    }

    /**
     * TODO this method is required really?
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

}
