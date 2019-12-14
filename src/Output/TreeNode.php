<?php

namespace shmurakami\Spice\Output;

class TreeNode
{
    /**
     * @var string
     */
    private $className;
    private $methodName;

    /**
     * TreeNode constructor.
     */
    public function __construct(string $className, $methodName)
    {
        $this->className = $className;
        $this->methodName = $methodName;
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
