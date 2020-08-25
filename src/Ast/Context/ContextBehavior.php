<?php

namespace shmurakami\Spice\Ast\Context;

trait ContextBehavior
{
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var string
     */
    private $className;

    private function extractNamespaceAndClass(string $fqcn)
    {
        $namespaceParts = [];
        $parts = explode('\\', $fqcn);
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $namespaceParts[] = $parts[$i];
        }

        $this->namespace = implode('\\', $namespaceParts);
        $this->className = end($parts);
    }

}
