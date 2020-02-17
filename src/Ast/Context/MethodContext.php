<?php

namespace shmurakami\Spice\Ast\Context;

use ast\Node;
use shmurakami\Spice\Ast\Parser\FqcnParser;

/**
 * maybe deprecated?
 */
class MethodContext
{
    use FqcnParser;

    /**
     * @var Context
     */
    private $context;
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
     * @param string $methodName
     * @param Node[] $argumentNodes
     */
    public function __construct(Context $context, string $methodName, array $argumentNodes)
    {
        $this->context = $context;
        $this->methodName = $methodName;
        $this->argumentNodes = $argumentNodes;
    }

    public function fqcn(): string
    {
        return $this->context->fqcn();
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function context()
    {
        return $this->context;
    }
}
