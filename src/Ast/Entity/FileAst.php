<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Entity;

use ast\Node;
use shmurakami\Elephoot\Ast\Context\Context;
use shmurakami\Elephoot\Ast\Parser\AstParser;
use shmurakami\Elephoot\Ast\Parser\ContextParser;
use shmurakami\Elephoot\Ast\Resolver\ClassAstResolver;
use shmurakami\Elephoot\Exception\ClassNotFoundException;
use shmurakami\Elephoot\Stub\Kind;

class FileAst
{

    public function __construct(
        private Node $rootNode,
        private Context $context,
        private ContextParser $contextParser,
        private AstParser $astParser
    )
    {
    }

    /**
     * @param Node|null $rootNode
     * @return ClassAst
     * @throws ClassNotFoundException
     */
    public function toClassAst(?Node $rootNode = null): ClassAst
    {
        $rootNode = $rootNode ?? $this->rootNode;

        $namespace = $this->astParser->parseNamespace($this->rootNode);
        foreach ($rootNode->children as $node) {
            if ($node->kind === Kind::AST_STMT_LIST) {
                // may stmt exist in this time?
                return $this->toClassAst($node);
            }

            if ($node->kind === Kind::AST_CLASS) {
                $nodeClassName = $node->children['name'];
                $nodeClassFqcn = $namespace . '\\' . $nodeClassName;
                if ($nodeClassFqcn === $this->context->fqcn()) {
                    return new ClassAst($node, $this->context, $this->contextParser, $this->astParser);
                }
            }
        }
        throw new ClassNotFoundException();
    }

    /**
     * @return ClassAst[]
     */
    public function dependentClassAstList(ClassAstResolver $classAstResolver): array
    {
        return array_merge(
            $this->astParser->importedClassAsts($this->rootNode),
            $this->astParser->extendClassAsts($this->rootNode),
            $this->relatedClasses($classAstResolver));
    }

    /**
     * if this works perfectly, import statement is not necessary. it's just redundant
     *
     * @return ClassAst[]
     * @throws ClassNotFoundException
     */
    private function relatedClasses(ClassAstResolver $classAstResolver): array
    {
        return $this->toClassAst()->relatedClasses($classAstResolver);
    }

}
