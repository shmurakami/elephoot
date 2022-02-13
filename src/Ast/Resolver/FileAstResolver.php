<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Resolver;

use ReflectionException;
use shmurakami\Elephoot\Ast\AstLoader;
use shmurakami\Elephoot\Ast\ClassMap;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Entity\FileAst;

class FileAstResolver
{
    /**
     * @var <?FileAst[]>
     */
    private $resolved = [];

    public function __construct(private ClassMap $classMap)
    {
    }

    public function resolve(string $className): ?FileAst
    {
        // null if parse failed
        if (array_key_exists($className, $this->resolved)) {
            return $this->resolved[$className];
        }

        try {
            $fileAst = (new AstLoader($this->classMap))->loadFileAst(new ClassContext($className));
        } catch (ReflectionException $e) {
            // should log error anyway
            $fileAst = null;
        }

        $this->resolved[$className] = $fileAst;
        return $fileAst;
    }

}
