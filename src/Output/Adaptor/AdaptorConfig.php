<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output\Adaptor;

class AdaptorConfig
{
    /**
     * @var string
     */
    private $outputDirectory;

    /**
     * AdaptorConfig constructor.
     */
    public function __construct(string $outputDirectory)
    {
        $this->outputDirectory = $outputDirectory;
    }

    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }
}
