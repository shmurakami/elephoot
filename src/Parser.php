<?php

declare(strict_types=1);

namespace shmurakami\Elephoot;

use ReflectionException;
use shmurakami\Elephoot\Ast\AstLoader;
use shmurakami\Elephoot\Ast\ClassMap;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Context\MethodContext;
use shmurakami\Elephoot\Ast\Entity\ClassAst;
use shmurakami\Elephoot\Ast\Entity\MethodAst;
use shmurakami\Elephoot\Ast\Request;
use shmurakami\Elephoot\Ast\Resolver\ClassAstResolver;
use shmurakami\Elephoot\Ast\Resolver\FileAstResolver;
use shmurakami\Elephoot\Output\ClassTree;
use shmurakami\Elephoot\Output\LazyReplacementTree;
use shmurakami\Elephoot\Output\MethodCallTree;
use shmurakami\Elephoot\Output\ObjectRelationTree;

class Parser
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ObjectRelationTree[]
     */
    private $builtTreeCache = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function parse(): ObjectRelationTree
    {
        $context = $this->request->getTarget();
        $classMap = $this->request->getClassMap();

        if ($context instanceof ClassContext) {
            return $this->parseByClass($context, $classMap);
        }

        /** @var MethodContext $context */
        return $this->parseByMethod($context);
    }

    /**
     * @throws Exception\ClassNotFoundException
     * @throws ReflectionException
     * @throws Exception\MethodNotFoundException
     */
    public function parseByMethod(MethodContext $context): ObjectRelationTree
    {
        throw new \LogicException('not implemented');
        /*
         * parse AST for Class and method
         * pool to buffer
         *
         * output graph
         */
//        $classAst = (new AstLoader())->loadByClass($classFqcn);
//        $methodAst = $classAst->parseMethod($methodName);

//        $methodCallTree = $this->buildMethodCallTree($methodAst);
        // TODO output from methodCallTree
    }

    public function parseByClass(ClassContext $context, ClassMap $classMap): ClassTree
    {
        $classAst = (new AstLoader($classMap))->loadByClass($context);
        $classTree = $this->buildClassTree($classAst, $classMap);

        /** @var ClassTree $resolvedClassTree */
        $resolvedClassTree = $this->resolveLazyReplacements($classTree);
        return $resolvedClassTree;
    }

    public function buildClassTree(ClassAst $classAst, ClassMap $classMap): ClassTree
    {
        $tree = new ClassTree($classAst->treeNode());

        $resolver = new FileAstResolver($classMap);
        $fileAst = $resolver->resolve($classAst->fqcn());
        if (!$fileAst) {
            return $tree;
        }

        $classAstResolver = new ClassAstResolver($classMap);
        $dependencies = $fileAst->dependentClassAstList($classAstResolver);
        foreach ($dependencies as $dependentClassAst) {
            $fqcn = $dependentClassAst->fqcn();
            if (!array_key_exists($fqcn, $this->builtTreeCache)) {
                // mark as already parsed to support circular reference
                $this->builtTreeCache[$fqcn] = new LazyReplacementTree($fqcn);
                $this->builtTreeCache[$fqcn] = $this->buildClassTree($dependentClassAst, $classMap);
            }

            $tree->add($this->builtTreeCache[$fqcn]);
        }
        return $tree;
    }

    private function buildMethodCallTree(MethodAst $methodAst): MethodCallTree
    {
        $tree = new MethodCallTree($methodAst->treeNode());

        foreach ($methodAst->methodCallNodes() as $methodCallAstNode) {
            $methodCallTree = $this->buildMethodCallTree($methodCallAstNode);
            $tree->add($methodCallTree);
        }
        return $tree;
    }

    /**
     * deep copy tree with replace LazyReplacementTree
     * @param ObjectRelationTree[] $newChildTrees
     */
    private function resolveLazyReplacements(ObjectRelationTree $sourceTree): ObjectRelationTree
    {
        if ($sourceTree instanceof LazyReplacementTree) {
            $replacementFqcn = $sourceTree->nameShouldBeReplaced();
            $replacementTree = $this->builtTreeCache[$replacementFqcn]->replacementTree();
            return $replacementTree;
        }

        $copyTree = $sourceTree->replacementTree();
        foreach ($sourceTree->getChildTrees() as $childTree) {
            $copyTree->add($this->resolveLazyReplacements($childTree));
        }
        return $copyTree;
    }
}
