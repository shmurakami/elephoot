<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

class MethodTreeNode
{
    public function __construct(private string $className, private string $methodName)
    {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): mixed
    {
        return $this->methodName;
    }
}
