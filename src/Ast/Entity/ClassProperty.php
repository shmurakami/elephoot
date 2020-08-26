<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Parser\DocCommentParser;
use shmurakami\Spice\Ast\Parser\TypeParser;

class ClassProperty
{
    use TypeParser;
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
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context, Node $propertyNode)
    {
        $this->context = $context;
        $this->propertyNode = $propertyNode;
        $this->namespace = $context->extractNamespace();

        // retrieve doc comment

        /** @var Node $propDeclaration */
        $propDeclaration = $propertyNode->children['props'];
        /** @var Node $propElement */
        $propElement = $propDeclaration->children[0];
        $this->propertyName = $propElement->children['name'];
        $this->docComment = $propElement->children['docComment'] ?? '';
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

        $classFqcnListInComment = $this->parseDocComment($this->docComment, '@var');
        return array_map(function (string $className) {
            if ($this->isFqcn($className)) {
                $context = new ClassContext($className);
            } else {
                $context = new ClassContext($this->namespace . '\\' . $className);
            }
            return $this->parseType($context);
        }, $classFqcnListInComment);
    }
}
