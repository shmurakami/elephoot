<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

class MethodTreeNode
{
    public function __construct(private string $className, private string $methodName)
    {
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return mixed
     */
    public function getMethodName()
    {
        return $this->methodName;
    }
}
