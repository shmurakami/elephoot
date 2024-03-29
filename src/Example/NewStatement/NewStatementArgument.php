<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Example\NewStatement;

class NewStatementArgument
{
    /**
     * @var NewStatementArgumentArgument
     */
    private $self;

    /**
     * NewStatementArgument constructor.
     */
    public function __construct(NewStatementArgumentArgument $self)
    {
        $this->self = $self;
    }
}
