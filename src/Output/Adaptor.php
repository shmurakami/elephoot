<?php

namespace shmurakami\Spice\Output;

interface Adaptor
{
    /**
     * @param Tree $tree
     * @return string
     */
    public function createDest(Tree $tree): string;

}
