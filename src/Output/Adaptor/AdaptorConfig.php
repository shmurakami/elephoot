<?php

namespace shmurakami\Spice\Output\Adaptor;

class AdaptorConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * AdaptorConfig constructor.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    private function default(): array
    {
        return [
            // TODO decicde something
        ];
    }

    public function getOutputDirectory(): string
    {
        return sys_get_temp_dir();
    }
}
