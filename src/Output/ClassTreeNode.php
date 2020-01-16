<?php

namespace shmurakami\Spice\Output;

class ClassTreeNode implements Node
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

    public function getName(): string
    {
        return $this->className;
    }
}
