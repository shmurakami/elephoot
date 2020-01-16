<?php

namespace shmurakami\Spice\Output;

interface Tree
{
    public function getRootNodeName(): string;

    /**
     * @return Tree[]
     */
    public function getChildTrees(): array;

}
