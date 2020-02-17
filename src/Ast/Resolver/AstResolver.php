<?php

namespace shmurakami\Spice\Ast\Resolver;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Context\MethodContext;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Ast\Entity\FileAst;
use shmurakami\Spice\Ast\Entity\Imports;
use shmurakami\Spice\Ast\Entity\MethodAst;
use shmurakami\Spice\Ast\Parser\FqcnParser;
use shmurakami\Spice\Exception\ClassNotFoundException;

class AstResolver
{
    use FqcnParser;

    /**
     * @var ClassMap
     */
    private $classMap;
    /**
     * @var FileAst[]
     */
    private $resolvedFileAst = [];
    /**
     * @var ClassAst[]
     */
    private $resolvedClassAst = [];
    /**
     * @var MethodAst[]
     */
    private $resolvedMethodAst = [];
    /**
     * @var Imports[] className => Imports
     */
    private $imports = [];

    public function __construct(ClassMap $classMap)
    {
        $this->classMap = $classMap;
    }

    public function resolveFileAst(string $className): ?FileAst
    {
        // null if parse failed
        if (array_key_exists($className, $this->resolvedFileAst)) {
            return $this->resolvedFileAst[$className];
        }

        try {
            $fileAst = (new AstLoader($this->classMap))->loadFileAst($className);
        } catch (ReflectionException $e) {
            // should log error anyway
            $fileAst = null;
        }

        $this->resolvedFileAst[$className] = $fileAst;
        return $fileAst;
    }

    public function resolveClassAst(string $className): ?ClassAst
    {
        // null if parse failed
        if (array_key_exists($className, $this->resolvedClassAst)) {
            return $this->resolvedClassAst[$className];
        }

        try {
            $classAst = (new AstLoader($this->classMap))->loadByClass($className);
        } catch (ReflectionException|ClassNotFoundException $e) {
            // should log error anyway
            $classAst = null;
        }

        $this->resolvedClassAst[$className] = $classAst;
        return $classAst;
    }

    public function resolveMethodAst(string $className, string $methodName): ?MethodAst
    {
        $key = $this->classMethodName($className, $methodName);
        if (array_key_exists($key, $this->resolvedMethodAst)) {
            return $this->resolvedMethodAst[$key];
        }

        $methodAst = null;
        try {
            $fileAst = (new AstLoader($this->classMap))->loadFileAst($className);
            if ($fileAst) {
                $methodAst = $fileAst->parseMethod($methodName);
            }
        } catch (ReflectionException $e) {
            // should log error anyway
        }

        $this->resolvedMethodAst[$key] = $methodAst;
        return $methodAst;
    }

    public function resolveContext(Context $context, string $className): ?Context
    {
        if ($this->isFqcn($className)) {
            return $this->parseFqcn($className);
        }

        $importedClassName = $this->resolveImports($context->fqcn())->resolve($className);
        if (!$importedClassName) {
            return null;
        }
        return $this->parseFqcn($importedClassName);
    }

    public function resolveImports(string $className): Imports
    {
        if (!isset($this->imports[$className])) {
            $this->imports[$className] = $this->resolveFileAst($className)->getImports();
        }
        return $this->imports[$className];
    }

    private function classMethodName(string $className, string $methodName): string
    {
        return "${className}@${methodName}";
    }
}
