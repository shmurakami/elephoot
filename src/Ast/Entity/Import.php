<?php

namespace shmurakami\Spice\Ast\Entity;

class Import
{
    /**
     * @var string
     */
    private $importName;
    /**
     * @var string
     */
    private $alias;

    public function __construct(string $importName, string $alias = ''/*, isFunction = false??*/)
    {
        $this->importName = $importName;
        $this->alias = $alias;
    }

    public function className(): string
    {
        return $this->importName;
    }
}
