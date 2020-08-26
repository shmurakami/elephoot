<?php

namespace shmurakami\Spice\Ast;

use ReflectionClass;
use ReflectionException;
use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Context\Context;
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
     * @throws ClassNotFoundException
     * @throws ReflectionException
     */
    public function loadByClass(Context $context): ClassAst
    {
        $fqcn = $context->fqcn();
        if ($fqcn[0] === '\\') {
            // context adds \ prefix but not needed in class map
            $fqcn = substr($fqcn, 1);
        }
        if ($this->classMap->registered($fqcn)) {
            // if class is not target of autoload, need to load file explicitly
            $filepath = $this->classMap->filepathByFqcn($fqcn);
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }

        // class path must be enabled to load
        if (!class_exists($fqcn) && !interface_exists($fqcn) && !trait_exists($fqcn)) {
            throw new ClassNotFoundException("class or interface or trait $fqcn not found");
        }

        $fileAst = $this->loadFileAst($context);
        return $fileAst->parse();
    }

    /**
     * @throws ReflectionException
     */
    public function loadFileAst(Context $context): FileAst
    {
        $className = $context->fqcn();
        $mappedFilepath = $this->classMap->filepathByFqcn($className);
        if ($mappedFilepath) {
            return $this->loadFromFilepath($context, $mappedFilepath);
        }

        $reflector = new ReflectionClass($className);
        $fileName = $reflector->getFileName();

        $rootNode = parse_file($fileName, 70);

        // should return ClassAst? who should parse namespace?
        return new FileAst($rootNode, $context);
    }

    private function loadFromFilepath(Context $context, string $filepath): FileAst
    {
        $rootNode = parse_file($filepath, 70);
        return new FileAst($rootNode, $context);
    }
}
