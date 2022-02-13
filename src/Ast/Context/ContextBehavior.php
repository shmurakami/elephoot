<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Context;

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

    private function extractNamespaceAndClass(string $fqcn): void
    {
        $namespaceParts = [];
        $parts = explode('\\', $fqcn);
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $namespaceParts[] = $parts[$i];
        }

        $this->namespace = implode('\\', $namespaceParts);
        $this->className = end($parts);
    }

    public function hasNamespace(): bool
    {
        return (bool)$this->namespace;
    }

    public function extractNamespace(): string
    {
        return $this->namespace;
    }

    public function extractClassName(): string
    {
        return $this->className;
    }

}
