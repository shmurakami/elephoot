<?php

namespace shmurakami\Spice\Ast;

class ClassMap
{

    /**
     * @var array
     */
    private $classMap;

    public function __construct(array $classMap)
    {
        $this->classMap = $classMap;
    }

    public function registered(string $classFqcn): bool
    {
        return isset($this->classMap[$classFqcn]);
    }

    public function filepathByFqcn(string $classFqcn): ?string
    {
        return $this->classMap[$classFqcn] ?? null;
    }
}
