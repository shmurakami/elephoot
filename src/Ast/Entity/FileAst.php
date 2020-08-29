<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Parser\AstParser;
use shmurakami\Spice\Ast\Parser\ContextParser;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Exception\ClassNotFoundException;
use shmurakami\Spice\Stub\Kind;

class FileAst
{
    /**
     * @var Node
     */
    private $rootNode;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var ContextParser
     */
    private $contextParser;
    /**
     * @var AstParser
     */
    private $astParser;

    public function __construct(Node $rootNode, Context $context, ContextParser $contextParser, AstParser $astParser)
    {
        $this->rootNode = $rootNode;
        $this->contextParser = $contextParser;
        $this->context = $context;
        $this->astParser = $astParser;
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
                $nodeClassName =  $node->children['name'];
                $nodeClassFqcn = $namespace . '\\' . $nodeClassName;
                if ($nodeClassFqcn === $this->context->fqcn()) {
                    return new ClassAst($this->contextParser, $this->context, $node);
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
