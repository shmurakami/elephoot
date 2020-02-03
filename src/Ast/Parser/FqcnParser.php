<?php

namespace shmurakami\Spice\Ast\Parser;

use shmurakami\Spice\Ast\Context\Context;
use shmurakami\Spice\Ast\Entity\Imports;

trait FqcnParser
{

    private function parseFqcn(string $className): Context
    {
        // remove \ prefix
        $parts = explode('\\', trim($className, '\\'));
        $className = end($parts);

        $namespace = '';
        for ($i = 0, $count = count($parts); $i < $count - 1; $i++) {
            $str = $parts[$i];
            $namespace .= '\\' . $str;
        }
        $namespace = trim($namespace, '\\');

        return new Context($namespace, $className);
    }

    private function isFqcn(string $className): bool
    {
        return strpos($className, '\\') !== false;
    }

    /**
     * TODO refactoring. this method should not be here
     */
    private function parseFqcnWithImports(string $className, Imports $imports): ?Context
    {
        if ($this->isFqcn($className)) {
            return $this->parseFqcn($className);
        }

        $imported = $imports->resolve($className);
        if ($imported) {
            return $this->parseFqcn($imported);
        }
        return null;
    }

}
