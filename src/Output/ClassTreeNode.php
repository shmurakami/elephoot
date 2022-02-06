<?php

namespace shmurakami\Elephoot\Output;

use shmurakami\Elephoot\Ast\Context\Context;

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
