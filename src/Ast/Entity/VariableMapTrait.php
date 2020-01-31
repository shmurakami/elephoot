<?php

namespace shmurakami\Spice\Ast\Entity;

use shmurakami\Spice\Ast\Context\Context;

trait VariableMapTrait
{
    /**
     * @var array name => Context
     */
    private $variableMap = [];

    public function get(string $name): ?Context
    {
        return $this->variableMap[$name] ?? null;
    }

    public function update(string $name, ?Context $context)
    {
        $this->variableMap[$name] = $context;
        return $this;
    }

}
