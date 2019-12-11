<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Exception\ClassNotFoundException;
use shmurakami\Spice\Stub\Kind;

class FileAst
{
    /**
     * @var Node
     */
    private $rootNode;
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $namespace;

    public function __construct(Node $rootNode, string $className)
    {
        $this->rootNode = $rootNode;
        // file may not have class so weird to require
        $this->className = $className;

        $this->namespace = $this->parseNamespace();
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    private function parseNamespace(): string
    {
        // assuming namespace is put in top of file

        /** @var Node $node */
        foreach ($this->rootNode->children as $node) {
            if ($node->kind === Kind::AST_NAMESPACE) {
                return $node->children['name'];
            }
        }
        return '';
    }

    /**
     * @param Node|null $rootNode
     * @return ClassAst
     * @throws ClassNotFoundException
     */
    public function parseClass(?Node $rootNode = null): ClassAst
    {
        $rootNode = $rootNode ?? $this->rootNode;

        foreach ($rootNode->children as $node) {
            if ($node->kind === Kind::AST_STMT_LIST) {
                // may stmt exist in this time?
                return $this->parseClass($node);
            }

            if ($node->kind === Kind::AST_CLASS) {
                $nodeClassName =  $node->children['name'];
                $nodeClassFqcn = $this->getNamespace() . '\\' . $nodeClassName;
                if ($nodeClassFqcn === $this->className) {
                    return new ClassAst($this->getNamespace(), $nodeClassName, $node);
                }
            }
        }
        throw new ClassNotFoundException();
    }

}
