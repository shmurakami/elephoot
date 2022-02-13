<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Context;

interface Context
{
    public function fqcn(): string;

    public function fullName(): string;

    public function hasNamespace(): bool;

    /**
     * should avoid leaking field
     */
    public function extractNamespace(): string;
    public function extractClassName(): string;

}
