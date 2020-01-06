<?php

namespace shmurakami\Spice\Ast;

use ReflectionClass;
use ReflectionException;
use shmurakami\Spice\Ast\Entity\ClassAst;
use shmurakami\Spice\Ast\Entity\FileAst;
use shmurakami\Spice\Exception\ClassNotFoundException;
use function ast\parse_file;

class AstLoader
{
    /**
     * @param string $className
     * @throws ClassNotFoundException
     * @throws ReflectionException
     */
    public function loadByClass(string $className): ClassAst
    {
        // class path must be enabled to load
        if (!class_exists($className) && !interface_exists($className)) {
            throw new ClassNotFoundException("class or interface $className not found");
        }

        $fileAst = $this->loadFileAst($className);
        return $fileAst->parse();
    }

    /**
     * @param string $className
     * @return FileAst
     * @throws ReflectionException
     */
    public function loadFileAst(string $className): FileAst
    {
        $reflector = new ReflectionClass($className);
        $fileName = $reflector->getFileName();

        $rootNode = parse_file($fileName, 70);

        // should return ClassAst? who should parse namespace?
        return new FileAst($rootNode, $className);
    }
}
