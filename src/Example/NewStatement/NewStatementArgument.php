<?php

namespace shmurakami\Spice\Example\NewStatement;

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
