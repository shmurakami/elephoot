<?php

namespace shmurakami\Spice\Ast\Parser;

use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Context\Context;

trait ContextParser
{
    private function isFqcn(string $className): bool
    {
        return strpos($className, '\\') !== false;
    }

    private function toContext(string $contextNamespace, string $className): ?Context
    {
        if ($this->isNotSupportedPhpBaseType($className)) {
            return null;
        }

        if ($this->isFqcn($className)) {
            return new ClassContext($className);
        }
        return new ClassContext($contextNamespace . '\\' . $className);
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
