<?php

namespace shmurakami\Spice\Ast\Entity\Node;

use ast\Node;
use shmurakami\Spice\Ast\Parser\DocCommentParser;
use shmurakami\Spice\Ast\Parser\TypeParser;

class MethodNode
{
    use DocCommentParser;
    use TypeParser;

    /**
     * @var string
     */
    private $namespace;
    /**
     * @var Node
     */
    private $node;

    public function __construct(string $namespace, Node $node)
    {
        $this->namespace = $namespace;
        $this->node = $node;
    }

    /**
     * @return string[]
     */
    public function parse()
    {
        $classFqcn = [];

        // doc comment
        $doComment = $this->node->children['docComment'] ?? '';
        $classFqcnList = $this->parseDocComment($doComment, '@param');
        $methodDocCommentDependencies = $this->parseType($this->namespace, $classFqcnList);
        foreach ($methodDocCommentDependencies as $dependency) {
            $classFqcn[] = $dependency;
        }

        // type hinting
        $argumentNodes = $this->node->children['params']->children ?? [];
        $retrieveFromTypeHinting = array_map(function (Node $node) {
            return $node->children['type']->children['name'] ?? '';
        }, $argumentNodes);
        $classFqcnListIntypeHinting = $this->parseType($this->namespace, $retrieveFromTypeHinting);
        foreach ($classFqcnListIntypeHinting as $fqcn) {
            $classFqcn[] = $fqcn;
        }

        // return type
        return $classFqcn;
    }

}
