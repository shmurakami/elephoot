<?php

namespace shmurakami\Spice\Output;

class MethodTreeNode implements Node
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

    public function getName(): string
    {
        return $this->className . '@' . $this->methodName;
    }
}
