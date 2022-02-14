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

//    private string $className;

    private string $docComment;

    public function __construct(
        private ContextParser $contextParser,
        private Context $context,
        private Node $propertyNode
    )
    {
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
