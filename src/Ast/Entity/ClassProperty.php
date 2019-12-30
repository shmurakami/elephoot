<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Ast\Parser\DocCommentParser;

class ClassProperty
{
    use DocCommentParser;

    /**
     * @var string
     */
    private $namespace;
    /**
     * @var string
     */
    private $className;
    /**
     * @var Node
     */
    private $propertyNode;
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var string
     */
    private $docComment;

    public function __construct(string $namespace, string $className, Node $propertyNode)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->propertyNode = $propertyNode;

        // retrieve doc comment

        /** @var Node $propDeclaration */
        $propDeclaration = $propertyNode->children['props'];
        /** @var Node $propElement */
        $propElement = $propDeclaration->children[0];
        $this->propertyName = $propElement->children['name'];
        $this->docComment = $propElement->children['docComment'] ?? '';
    }

    /**
     * TODO check it's callable
     */
    public function parse(): ?ClassAst
    {

    }

    public function isCallable(): bool
    {
        return true;
    }

    /**
     * parse doc comment
     * return AstEntity if this property is class instance
     *
     * @return string[]
     */
    public function classFqcnListFromDocComment(): array
    {
        if ($this->docComment === '') {
            return [];
        }

        return $this->parseDocComment($this->namespace, $this->docComment, '@var');
    }
}