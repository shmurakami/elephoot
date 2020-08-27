<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
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
    private $className;
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

        // retrieve doc comment

        /** @var Node $propDeclaration */
        $propDeclaration = $propertyNode->children['props'];
        /** @var Node $propElement */
        $propElement = $propDeclaration->children[0];
        $this->docComment = $propElement->children['docComment'] ?? '';
    }

    /**
     * parse doc comment
     * return AstEntity if this property is class instance
     *
     * @return Context[]
     */
    public function classContextListFromDocComment(): array
    {
        if ($this->docComment === '') {
            return [];
        }

        return $this->parseDocComment($this->context, $this->docComment, '@var');
    }
}
