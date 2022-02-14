<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Entity\Node;

use ast\Node;
use shmurakami\Elephoot\Ast\Context\Context;
use shmurakami\Elephoot\Ast\Parser\ContextParser;
use shmurakami\Elephoot\Ast\Parser\DocCommentParser;
use shmurakami\Elephoot\Stub\Kind;

class MethodNode
{
    use DocCommentParser;

    private string $namespace;

    public function __construct(
        private ContextParser $contextParser,
        private Context $context,
        private Node $node
    )
    {
        $this->namespace = $context->extractNamespace();
    }

    /**
     * @return Context[]
     */
    public function parseMethodAttributeToContexts(): array
    {
        $dependencyContexts = [];

        // doc comment
        $doComment = $this->node->children['docComment'] ?? '';
        $contexts = $this->parseDocComment($this->contextParser, $this->context, $doComment, '@param');
        foreach ($contexts as $context) {
            $dependencyContexts[] = $context;
        }

        // type hinting
        // consider nullable type?
        $argumentNodes = $this->node->children['params']->children ?? [];
        $typeNames = array_map(function (Node $node) {
            return $node->children['type']->children['name'] ?? '';
        }, $argumentNodes);
        // remove empty string
        $typeNames = array_filter($typeNames, function (string $name) {
            return (bool)$name;
        });
        foreach ($this->contextParser->toContextList($this->namespace, $typeNames) as $context) {
            $dependencyContexts[] = $context;
        }

        // return statement
        // return type
        $returnTypeNode = $this->node->children['returnType'] ?? null;
        if ($returnTypeNode
            && ($returnTypeNode->kind === Kind::AST_TYPE || $returnTypeNode->kind === Kind::AST_NULLABLE_TYPE)
        ) {
            $typeName = $returnTypeNode->children['type']->children['name'] ?? '';
            if ($typeName) {
                $context = $this->contextParser->toContext($this->namespace, $typeName);
                if ($context) {
                    $dependencyContexts[] = $context;
                }
            }
        }

        // return type in doc comment
        // redundant to parse doc comment again?
        $doComment = $this->node->children['docComment'] ?? '';
        $contexts = $this->parseDocComment($this->contextParser, $this->context, $doComment, '@return');
        foreach ($contexts as $context) {
            $dependencyContexts[] = $context;
        }

        // fqcn key is not needed
        return array_values($this->contextParser->unique($dependencyContexts));
    }

}
