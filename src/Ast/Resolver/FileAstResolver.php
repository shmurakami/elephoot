<?php

namespace shmurakami\Spice\Ast\Resolver;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Entity\FileAst;

class FileAstResolver
{
    /**
     * @var FileAst[]
     */
    private $resolved = [];
    /**
     * @var ClassMap
     */
    private $classMap;

    /**
     * FileAstResolver constructor.
     */
    public function __construct(ClassMap $classMap)
    {
        $this->classMap = $classMap;
    }

    public function resolve(string $className): FileAst
    {
        // null if parse failed
        if (array_key_exists($className, $this->resolved)) {
            return $this->resolved[$className];
        }

        try {
            $fileAst = (new AstLoader($this->classMap))->loadFileAst($className);
        } catch (ReflectionException $e) {
            // should log error anyway
            $fileAst = null;
        }

        $this->resolved[$className] = $fileAst;
        return $fileAst;
    }

}
