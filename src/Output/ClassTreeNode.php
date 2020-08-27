<?php

namespace shmurakami\Spice\Output;

use shmurakami\Spice\Ast\Context\Context;

class ClassTreeNode
{
    /**
     * @var Context
     */
    private $context;

    /**
     * ClassTreeNode constructor.
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->context->fqcn();
    }
}
