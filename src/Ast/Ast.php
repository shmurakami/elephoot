<?php

namespace shmurakami\Spice\Ast;

use ast\Node;
use shmurakami\Spice\Stub\Kind;

class Ast
{
    /**
     * @var Node
     */
    private $rootNode;

    /**
     * Ast constructor.
     */
    public function __construct(Node $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    public function parseMethod(string $method): ?Ast
    {
        // if it returns Ast, new instance does not have namespace
        // and it will not have namespace. probably it can't detect related method instance
        // maybe should not make Ast instance with root node and should not have getNamespace.
        // it isn't responsibility of this class
        // then how namespace should be given? Loader gives and take it to nested instance?

        if ($this->rootNode->kind === Kind::AST_STMT_LIST) {


        } else {
            // how it happens?
        }

        /** @var Node $node */
        foreach ($this->rootNode as $node) {
            if ($node->kind === Kind::AST_STMT_LIST) {
                $ast = new Ast($node);
                $methodNode = $ast->parseMethod($method);
                if ($methodNode) {
                    return new Ast($methodNode);
                }
            }

            if ($node->kind === Kind::AST_METHOD) {
                if ($node->children['name'] === $method) {
                    return new Ast($node);
                }
            }
        }
        return null;
    }

}
