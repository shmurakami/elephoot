<?php

namespace shmurakami\Spice;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Ast\Entity\MethodAst;
use shmurakami\Spice\Ast\Request;
use shmurakami\Spice\Ast\Resolver\AstResolver;
use shmurakami\Spice\Ast\Resolver\ClassAstResolver;
use shmurakami\Spice\Ast\Resolver\FileAstResolver;
use shmurakami\Spice\Ast\Resolver\MethodAstResolver;
use shmurakami\Spice\Output\Adaptor\AdaptorConfig;
use shmurakami\Spice\Output\Adaptor\GraphpAdaptor;
use shmurakami\Spice\Output\ClassTree;
use shmurakami\Spice\Output\Drawer;
use shmurakami\Spice\Output\MethodTree;

class Parser
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var AstResolver
     */
    private $astResolver;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function parse()
    {
        ['class' => $classFqcn, 'method' => $method] = $this->request->getTarget();

        if ($this->request->isClassMode()) {
            $this->parseByClass($classFqcn);
            return;
        }
        $this->parseByMethod($classFqcn, $method);
    }

    /**
     * @param string $classFqcn
     * @param string $methodName
     * @throws Exception\ClassNotFoundException
     * @throws ReflectionException
     * @throws Exception\MethodNotFoundException
     */
    public function parseByMethod(string $classFqcn, string $methodName): void
    {
        $classMap = $this->request->getClassMap();
        $classAst = (new AstLoader($classMap))->loadByClass($classFqcn);
        $methodAst = $classAst->parseMethod($methodName);

        $methodTree = $this->buildMethodTree($methodAst, $classMap);
        $graphpAdaptor = new GraphpAdaptor(new AdaptorConfig($this->request->getOutputDirectory()));
        $drawer = new Drawer($graphpAdaptor);
        $filepath = $drawer->draw($methodTree);
        // TODO output from methodCallTree
    }

    public function parseByClass(string $classFqcn): void
    {
        $classMap = $this->request->getClassMap();
        $classAst = (new AstLoader($classMap))->loadByClass($classFqcn);
        $astResolver = new AstResolver($classMap);

        $classTree = $this->buildClassTree($classAst, $astResolver);
        $graphpAdaptor = new GraphpAdaptor(new AdaptorConfig($this->request->getOutputDirectory()));
        $drawer = new Drawer($graphpAdaptor);
        $filepath = $drawer->draw($classTree);
    }

    public function buildClassTree(ClassAst $classAst, AstResolver $astResolver): ClassTree
    {
        $tree = new ClassTree($classAst->treeNode());

        $fileAst = $astResolver->resolveFileAst($classAst->fqcn());
        if (!$fileAst) {
            return $tree;
        }

        $dependencies = $fileAst->dependentClassAstList($astResolver);
        foreach ($dependencies as $dependentClassAst) {
            $tree->add($this->buildClassTree($dependentClassAst, $astResolver));
        }
        return $tree;
    }

    public function buildMethodTree(MethodAst $methodAst, ClassMap $classMap): MethodTree
    {
        $tree = new MethodTree($methodAst->treeNode());

        $fileAstResolver = new FileAstResolver($classMap);
        $methodAstResolver = new MethodAstResolver($classMap, $fileAstResolver->resolveImports($methodAst->fqcn()));

        foreach ($methodAst->dependentMethodAstList($methodAstResolver) as $methodAstNode) {
            $methodCallTree = $this->buildMethodTree($methodAstNode, $classMap);
            $tree->add($methodCallTree);
        }
        return $tree;
    }
}
