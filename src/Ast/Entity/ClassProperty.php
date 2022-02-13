<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Entity;

use ast\Node;
use shmurakami\Elephoot\Ast\Context\Context;
use shmurakami\Elephoot\Ast\Parser\ContextParser;
use shmurakami\Elephoot\Ast\Parser\DocCommentParser;

class ClassProperty
{
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
    /**
     * @var ContextParser
     */
    private $contextParser;

    public function __construct(ContextParser $contextParser, Context $context, Node $propertyNode)
    {
        $this->contextParser = $contextParser;
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

        return $this->parseDocComment($this->contextParser, $this->context, $this->docComment, '@var');
    }
}
