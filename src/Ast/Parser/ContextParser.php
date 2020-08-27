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
        if ($this->isFqcn($className)) {
            return new ClassContext($className);
        }
        return new ClassContext($contextNamespace . '\\' . $className);
    }
}
