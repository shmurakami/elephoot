<?php

namespace shmurakami\Spice\Ast\Parser;

use shmurakami\Spice\Ast\Context\Context;

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

}
