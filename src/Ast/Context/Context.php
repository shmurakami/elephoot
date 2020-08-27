<?php

namespace shmurakami\Spice\Ast\Context;

interface Context
{
    public function fqcn(): string;

    public function fullName(): string;

    public function hasNamespace();

    /**
     * should avoid leaking field
     */
    public function extractNamespace(): string;
    public function extractClassName(): string;

}
