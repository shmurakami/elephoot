<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Parser;

use shmurakami\Elephoot\Ast\ClassMap;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Context\Context;

class ContextParser
{
    public function __construct(private ClassMap $classMap)
    {
    }

    public function toContext(string $contextNamespace, string $className): ?Context
    {
        if ($this->isNotSupportedPhpBaseType($className)) {
            return null;
        }

        if ($this->isFqcn($className) || $this->contextIfValidClass($className)) {
            return new ClassContext($className);
        }
        return $this->contextIfValidClass($contextNamespace . '\\' . $className);
    }

    /**
     * @return Context[]
     */
    public function toContextList(string $contextNamespace, array $classNames): array
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
    public function unique(array $contexts): array
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
        return match (true) {
            class_exists($class) => new ClassContext($class),
            $this->classMap->registered($class) => new ClassContext($class),
            default => null,
        };
    }

    private function isFqcn(string $className): bool
    {
        return str_contains($className, '\\');
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
