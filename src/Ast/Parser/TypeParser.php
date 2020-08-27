<?php

namespace shmurakami\Spice\Ast\Parser;

use shmurakami\Spice\Ast\Context\Context;

trait TypeParser
{
    /**
     * TODO what is this method for...?
     * @return string|null
     */
    private function parseType(Context $context): ?string
    {
        $className = $context->fqcn();

        if ($this->isNotSupportedPhpBaseType($className)) {
            return null;
        }

        // if namespace is blank, namespaced dependencies must have fqcn
        if (!$context->hasNamespace()) {
            return $className;
        }

        // if \ is included it means fqcn. no need to touch
        if (strpos($className, '\\') === false) {
            // global space or same namespace instance
            // if class has namespace, assume as same namespace
            // otherwise assume as global namespace
            $baseNamespace = $namespace ?? '';
            $className = $baseNamespace . '\\' . $className;
        }
        return $className;
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
