<?php

namespace shmurakami\Spice\Ast\Parser;

trait TypeParser
{
    /**
     * @param string $namespace
     * @param string[] $classFqcnList
     * @return string[]
     */
    private function parseType(string $namespace, $classFqcnList)
    {
        $dependentClassFqcnList = [];
        for ($i = 0, $count = count($classFqcnList); $i < $count; $i++) {
            $classFqcn = trim($classFqcnList[$i]);
            // end parts may have additional comment
            if ($i === $count - 1) {
                $parts = explode(' ', $classFqcn);
                // FQCN has \\ prefix in doc comment but it's not needed
                // trim space and backslash
                $classFqcn = trim($parts[0], " \t\n\r \v\\");
            }

            if ($this->isNotSupportedPhpBaseType($classFqcn)) {
                continue;
            }

            // if \ is included it means fqcn. no need to touch
            if (strpos($classFqcn, '\\') === false) {
                // global space or same namespace instance
                // if class has namespace, assume as same namespace
                // otherwise assume as global namespace
                $baseNamespace = $namespace ?? '';
                $classFqcn = $baseNamespace . '\\' . $classFqcn;
            }

            $dependentClassFqcnList[] = $classFqcn;
        }
        return $dependentClassFqcnList;
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
