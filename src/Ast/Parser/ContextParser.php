<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Parser;

use shmurakami\Elephoot\Ast\ClassMap;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Context\Context;

class ContextParser
{
    /**
     * @var ClassMap
     */
    private $classMap;

    public function __construct(ClassMap $classMap)
    {
        $this->classMap = $classMap;
    }

    public function toContext(string $contextNamespace, string $className): ?Context
    {
        if ($this->isNotSupportedPhpBaseType($className)) {
            return null;
        }

        if ($this->isFqcn($className) || $context = $this->contextIfValidClass($className)) {
            return new ClassContext($className);
        }
        return $this->contextIfValidClass($contextNamespace . '\\' . $className);
    }

    /**
     * @return Context[]
     */
    public function toContextList(string $contextNamespace, array $classNames)
    {
        $contexts = [];
        foreach ($classNames as $className) {
            $context = $this->toContext($contextNamespace, $className);
            if ($context) {
                $contexts[] = $context;
            }
        }
        return $contexts;
    }

    /**
     * @return Context[]
     */
    public function unique(array $contexts)
    {
        $unique = [];
        foreach ($contexts as $context) {
            $fqcn = $context->fqcn();
            if (isset($unique[$fqcn])) {
                continue;
            }
            $unique[$fqcn] = $context;
        }
        return $unique;
    }

    private function contextIfValidClass(string $class): ?Context
    {
        if (class_exists($class)) {
            return new ClassContext($class);
        }
        if ($this->classMap->registered($class)) {
            return new ClassContext($class);
        }
        return null;
    }

    private function isFqcn(string $className): bool
    {
        return strpos($className, '\\') !== false;
    }

    private function isNotSupportedPhpBaseType(string $classType): bool
    {
        return in_array($classType, [
            'int', 'integer',
            'string',
            'bool', 'boolean',
            'float',
            'double',
            'object',
            'array', // array can be callable...
            'callable',
            'iterable',
            'mixed',
            'number',
            'void',
            'null',
        ], true);
    }
}
