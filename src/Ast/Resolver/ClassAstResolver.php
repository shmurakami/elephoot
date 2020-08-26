<?php

namespace shmurakami\Spice\Ast\Resolver;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Exception\ClassNotFoundException;

class ClassAstResolver
{
    /**
     * @var ClassAst[]
     */
    private $resolved = [];
    /**
     * @var ClassMap
     */
    private $classMap;

    /**
     * ClassAstResolver constructor.
     */
    public function __construct(ClassMap $classMap)
    {
        $this->classMap = $classMap;
    }

    public function resolve(string $className): ?ClassAst
    {
        // null if parse failed
        if (array_key_exists($className, $this->resolved)) {
            return $this->resolved[$className];
        }

        try {
            $classAst = (new AstLoader($this->classMap))->loadByClass(new ClassContext($className));
        } catch (ReflectionException|ClassNotFoundException $e) {
            // should log error anyway
            $classAst = null;
        }

        $this->resolved[$className] = $classAst;
        return $classAst;
    }
}
