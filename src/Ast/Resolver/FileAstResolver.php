<?php

namespace shmurakami\Spice\Ast\Resolver;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\Entity\FileAst;

class FileAstResolver
{
    use Resolver;

    /**
     * @var FileAst[]
     */
    private $resolved = [];

    public function resolve(string $className): FileAst
    {
        // null if parse failed
        if (array_key_exists($className, $this->resolved)) {
            return $this->resolved[$className];
        }

        try {
            $fileAst = (new AstLoader())->loadFileAst($className);
        } catch (ReflectionException $e) {
            // should log error anyway
            $fileAst = null;
        }

        $this->resolved[$className] = $fileAst;
        return $fileAst;
    }

}
