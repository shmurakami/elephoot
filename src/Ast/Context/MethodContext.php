<?php

namespace shmurakami\Spice\Ast\Context;

use ast\Node;
use shmurakami\Spice\Ast\Entity\ClassProperty;

class MethodContext
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var array
     */
    private $classProperties;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var array
     */
    private $argumentNodes;

    /**
     * MethodContext constructor.
     * @param Context $context
     * @param ClassProperty[] $classProperties
     * @param string $methodName
     * @param Node[] $argumentNodes
     */
    public function __construct(Context $context, array $classProperties, string $methodName, array $argumentNodes)
    {
        $this->context = $context;
        $this->classProperties = $classProperties;
        $this->methodName = $methodName;
        $this->argumentNodes = $argumentNodes;
    }

    public function fqcn(): string
    {
        return $this->context->fqcn();
    }

    public function classContext(): Context
    {
        return $this->context;
    }

}
