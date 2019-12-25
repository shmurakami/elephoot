<?php

namespace shmurakami\Spice\Ast\Resolver;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Exception\ClassNotFoundException;

class ClassAstResolver
{
    use Resolver;

    /**
     * @var ClassAst[]
     */
    private $resolved = [];

    public function resolve(string $className): ?ClassAst
    {
        // null if parse failed
        if (array_key_exists($className, $this->resolved)) {
            return $this->resolved[$className];
        }

        try {
            $classAst = (new AstLoader())->loadByClass($className);
        } catch (ReflectionException|ClassNotFoundException $e) {
            // should log error anyway
            $classAst = null;
        }

        $this->resolved[$className] = $classAst;
        return $classAst;
    }
}
