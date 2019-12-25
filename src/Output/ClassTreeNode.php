<?php

namespace shmurakami\Spice\Output;

class ClassTreeNode
{
    /**
     * @var string
     */
    private $className;

    /**
     * ClassTreeNode constructor.
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
