<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast;

class ClassMap
{

    public function __construct(private array $classMap)
    {
    }

    public function registered(string $classFqcn): bool
    {
        return isset($this->classMap[$classFqcn]);
    }

    public function filepathByFqcn(string $classFqcn): string
    {
        return $this->classMap[$classFqcn] ?? '';
    }
}
