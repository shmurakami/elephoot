<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output;

use shmurakami\Elephoot\Ast\Context\Context;

class ClassTreeNode
{

    public function __construct(private Context $context)
    {
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->context->fqcn();
    }
}
