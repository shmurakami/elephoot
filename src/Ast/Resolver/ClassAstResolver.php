<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Resolver;

use ReflectionException;
use shmurakami\Elephoot\Ast\AstLoader;
use shmurakami\Elephoot\Ast\ClassMap;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Entity\ClassAst;
use shmurakami\Elephoot\Exception\ClassNotFoundException;

class ClassAstResolver
{
    /**
     * @var array<string, ?ClassAst>
     */
    private array $resolved = [];

    public function __construct(private ClassMap $classMap)
    {
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
