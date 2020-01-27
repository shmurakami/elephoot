<?php

namespace shmurakami\Spice\Ast\Resolver;

use ReflectionException;
use shmurakami\Spice\Ast\AstLoader;
use shmurakami\Spice\Ast\ClassMap;
use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Entity\Imports;
use shmurakami\Spice\Ast\Entity\MethodAst;
use shmurakami\Spice\Ast\Parser\FqcnParser;

class MethodAstResolver
{
    use FqcnParser;

    /**
     * @var ClassMap
     */
    private $classMap;
    /**
     * @var Imports
     */
    private $imports;
    /**
     * @var MethodAst[]
     */
    private $resolved = [];

    public function __construct(ClassMap $classMap, Imports $imports)
    {
        $this->classMap = $classMap;
        $this->imports = $imports;
    }

    public function resolve(string $className, string $methodName): ?MethodAst
    {
        $key = $this->classMethodName($className, $methodName);
        if (array_key_exists($key, $this->resolved)) {
            return $this->resolved[$key];
        }

        $methodAst = null;
        try {
            $fileAst = (new AstLoader($this->classMap))->loadFileAst($className);
            if ($fileAst) {
                $methodAst = $fileAst->parseMethod($methodName);
            }
        } catch (ReflectionException $e) {
            // should log error anyway
        }

        $this->resolved[$key] = $methodAst;
        return $methodAst;
    }

    public function resolveContext(string $className): Context
    {
        if ($this->isFqcn($className)) {
            return $this->parseFqcn($className);
        }

        $importedClassName = $this->imports->resolve($className);
        if (!$importedClassName) {
            return null;
        }
        return $this->parseFqcn($importedClassName);
    }

    private function classMethodName(string $className, string $methodName): string
    {
        return "${className}@${methodName}";
    }
}
