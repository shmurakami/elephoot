<?php

namespace shmurakami\Spice\Output;

interface Adaptor
{
    /**
     * @param ClassTree $classTree
     * @return string
     */
    public function createDest(ClassTree $classTree): string;

}
