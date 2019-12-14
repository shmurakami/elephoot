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
    private $classFqcn;
    /**
     * @var string
     */
    private $namespace;

    public function __construct(Node $rootNode, string $classFqcn)
    {
        $this->rootNode = $rootNode;
        // file may not have class so weird to require
        $this->classFqcn = $classFqcn;
    }

    /**
     * @param Node|null $rootNode
     * @return ClassAst
     * @throws ClassNotFoundException
     */
    public function parse(?Node $rootNode = null): ClassAst
    {
        $rootNode = $rootNode ?? $this->rootNode;

        foreach ($rootNode->children as $node) {
            if ($node->kind === Kind::AST_STMT_LIST) {
                // may stmt exist in this time?
                return $this->parse($node);
            }

            if ($node->kind === Kind::AST_CLASS) {
                $nodeClassName =  $node->children['name'];
                $nodeClassFqcn = $this->getNamespace() . '\\' . $nodeClassName;
                if ($nodeClassFqcn === $this->classFqcn) {
                    return new ClassAst($this->getNamespace(), $nodeClassName, $node);
                }
            }
        }
        throw new ClassNotFoundException();
    }

    /**
     * @return string
     */
    private function getNamespace(): string
    {
        if ($this->namespace === null) {
            $namespace = '';
            /** @var Node $node */
            foreach ($this->rootNode->children as $node) {
                if ($node->kind === Kind::AST_NAMESPACE) {
                    $namespace = $node->children['name'];
                    break;
                }
            }
            $this->namespace = $namespace;
        }
        return $this->namespace;
    }

}
