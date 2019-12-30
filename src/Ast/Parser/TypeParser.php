<?php

namespace shmurakami\Spice\Ast\Parser;

trait TypeParser
{
    /**
     * @param string $namespace
     * @param string $classFqcn
     * @return string|null
     */
    private function parseType(string $namespace, $classFqcn)
    {
        $classFqcn = trim($classFqcn);

        if ($this->isNotSupportedPhpBaseType($classFqcn)) {
            return null;
        }

        // if \ is included it means fqcn. no need to touch
        if (strpos($classFqcn, '\\') === false) {
            // global space or same namespace instance
            // if class has namespace, assume as same namespace
            // otherwise assume as global namespace
            $baseNamespace = $namespace ?? '';
            $classFqcn = $baseNamespace . '\\' . $classFqcn;
        }
        return $classFqcn;
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
        ], true);
    }
}
