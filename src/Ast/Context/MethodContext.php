<?php

namespace shmurakami\Spice\Ast\Context;

use ast\Node;
use shmurakami\Spice\Ast\Entity\ClassProperty;
use shmurakami\Spice\Ast\Entity\Imports;
use shmurakami\Spice\Ast\Parser\FqcnParser;
use shmurakami\Spice\Parser;

class MethodContext
{
    use FqcnParser;

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
     * @var Imports
     */
    private $imports;
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
    public function __construct(Context $context, array $classProperties, string $methodName, Imports $imports, array $argumentNodes)
    {
        $this->context = $context;
        $this->classProperties = $classProperties;
        $this->methodName = $methodName;
        $this->imports = $imports;
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

    public function resolveContextByClassName(string $className): ?Context
    {
        $importedClassName = $this->imports->resolve($className);
        if (!$importedClassName) {
            return null;
        }
        return $this->parseFqcn($importedClassName);
    }

}
