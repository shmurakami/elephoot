<?php

namespace shmurakami\Spice\Ast;

class Request
{
    const MODE_CLASS = 'CLASS';
    const MODE_METHOD = 'METHOD';

    /**
     * @var string
     */
    private $mode;
    /**
     * @var string
     */
    private $target;
    /**
     * @var string
     */
    private $configure;
    /**
     * @var string
     */
    private $output;

    /**
     * Request constructor.
     * @param string $mode
     * @param string $target
     * @param string $output
     * @param string $configFile
     */
    public function __construct(string $mode, string $target, string $output, string $configFile)
    {
        $this->mode = $mode;
        $this->target = $target;
        $this->output = $output;
        $this->configure = $this->parseConfigFile($configFile);
    }

    public function getOutputDirectory()
    {
        if ($this->output) {
            return $this->output;
        }

        $configOutput = $this->configure['output'] ?? '';
        if ($configOutput) {
            return $configOutput;
        }

        $default = sys_get_temp_dir();
        return $default;
    }

    public function getTarget(): array
    {
        $target = $this->configure['target'] ?? '';
        if ($this->target) {
            $target = $this->target;
        }

        $parts = explode('@', $target);
        $class = $parts[0];
        $method = $parts[1] ?? '';
        return [$class, $method];
    }

    public function isClassMode(): bool
    {
        $mode = $this->configure['mode'] ?? self::MODE_CLASS;
        if ($this->mode) {
            $mode = $this->mode;
        }
        return strtoupper($mode) === self::MODE_CLASS;
    }

    private function parseConfigFile(string $filepath): array
    {
        if ($filepath && file_exists($filepath)) {
            return json_decode(file_get_contents($filepath), true);
        }
        return [];
    }

}
