<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast;

use ReflectionClass;
use ReflectionException;
use shmurakami\Elephoot\Ast\Context\Context;
use shmurakami\Elephoot\Ast\Entity\ClassAst;
use shmurakami\Elephoot\Ast\Entity\FileAst;
use shmurakami\Elephoot\Ast\Parser\AstParser;
use shmurakami\Elephoot\Ast\Parser\ContextParser;
use shmurakami\Elephoot\Ast\Resolver\ClassAstResolver;
use shmurakami\Elephoot\Exception\ClassNotFoundException;
use function ast\parse_file;

class AstLoader
{

    public function __construct(private ClassMap $classMap)
    {
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

        return $this->loadFileAst($context)->toClassAst();
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

        /** @psalm-suppress ArgumentTypeCoercion */
        $reflector = new ReflectionClass($className);
        $fileName = $reflector->getFileName();
        if (!$fileName) {
            throw new ClassNotFoundException("class or interface or trait $className not found");
        }

        $rootNode = parse_file($fileName, 70);

        // should return ClassAst? who should parse namespace?
        return new FileAst($rootNode, $context, new ContextParser($this->classMap), new AstParser(new ClassAstResolver($this->classMap)));
    }

    private function loadFromFilepath(Context $context, string $filepath): FileAst
    {
        $rootNode = parse_file($filepath, 70);
        return new FileAst($rootNode, $context, new ContextParser($this->classMap), new AstParser(new ClassAstResolver($this->classMap)));
    }
}
