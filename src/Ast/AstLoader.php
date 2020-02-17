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
     * @var ClassMap
     */
    private $classMap;

    /**
     * AstLoader constructor.
     */
    public function __construct(ClassMap $classMap)
    {
        $this->classMap = $classMap;
    }

    /**
     * @param string $className
     * @return ClassAst
     * @throws ClassNotFoundException
     * @throws ReflectionException
     */
    public function loadByClass(string $className): ClassAst
    {
        if ($this->classMap->registered($className)) {
            // if class is not target of autoload, need to load file explicitly
            $filepath = $this->classMap->filepathByFqcn($className);
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }

        // class path must be enabled to load
        if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
            throw new ClassNotFoundException("class or interface or trait $className not found");
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
        $mappedFilepath = $this->classMap->filepathByFqcn($className);
        if ($mappedFilepath) {
            return $this->loadFromFilepath($className, $mappedFilepath);
        }

        $reflector = new ReflectionClass($className);
        $filepath = $reflector->getFileName();

        return $this->loadFromFilepath($className, $filepath);
    }

    private function loadFromFilepath(string $className, string $filepath): FileAst
    {
        $rootNode = parse_file($filepath, 70);
        return new FileAst($rootNode, $className);
    }
}
