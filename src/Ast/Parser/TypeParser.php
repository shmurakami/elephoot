<?php

namespace shmurakami\Spice\Ast\Parser;

trait TypeParser
{
    /**
     * @param string $namespace
     * @param string $className
     * @return string|null
     */
    private function parseType(string $namespace, $className)
    {
        $className = trim($className);

        if ($this->isNotSupportedPhpBaseType($className)) {
            return null;
        }

        // if namespace is blank, namespaced dependencies must have fqcn
        if ($namespace === '') {
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
