<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output\Adaptor;

class AdaptorConfig
{

    public function __construct(private string $outputDirectory)
    {
    }

    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }
}
