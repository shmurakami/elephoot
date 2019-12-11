<?php

namespace shmurakami\Spice\Ast;

use ReflectionClass;
use ReflectionException;
use shmurakami\Spice\Ast\Entity\FileAst;
use shmurakami\Spice\Exception\ClassNotFoundException;
use function ast\parse_file;

class AstLoader
{
    /**
     * @param string $className
     * @return FileAst
     * @throws ClassNotFoundException
     * @throws ReflectionException
     */
    public function loadByClass(string $className): FileAst
    {
        // class path must be enabled to load
        if (!class_exists($className)) {
            throw new ClassNotFoundException("class $className not found");
        }

        $reflector = new ReflectionClass($className);
        $fileName = $reflector->getFileName();

        $rootNode = parse_file($fileName, 70);

        // should return ClassAst? who should parse namespace?
        return new FileAst($rootNode, $className);
    }
}
