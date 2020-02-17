<?php

namespace shmurakami\Spice\Ast\Resolver;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Ast\Entity\FileAst;
use shmurakami\Spice\Ast\Entity\Imports;
use shmurakami\Spice\Exception\ClassNotFoundException;

class AstResolver
{
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

    public function resolveImports(string $className): Imports
    {
        return $fileAst = $this->resolveFileAst($className)->getImports();
    }
}
