<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast;

use InvalidArgumentException;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Context\Context;
use shmurakami\Elephoot\Ast\Context\MethodContext;

class Request
{
    const MODE_CLASS = 'CLASS';
    const MODE_METHOD = 'METHOD';

    private array $configure;

    public function __construct(
        private string $mode,
        private string $target,
        private string $output,
        string $configFile
    )
    {
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

        $default = getcwd();
        return $default;
    }

    public function getTarget(): Context
    {
        $target = $this->configure['target'] ?? '';
        if ($this->target) {
            $target = $this->target;
        }

        $parts = explode('@', $target);
        $class = $parts[0];
        if (!$class) {
            throw new InvalidArgumentException();
        }

        $method = $parts[1] ?? '';
        if ($method) {
            return new MethodContext($class, $method);
        }
        return new ClassContext($class);
    }

    private function parseConfigFile(string $filepath): array
    {
        if ($filepath && file_exists($filepath)) {
            return json_decode(file_get_contents($filepath), true);
        }
        return [];
    }

    public function isValid()
    {
        $output = $this->getOutputDirectory();
        try {
            $this->getTarget();
        } catch (InvalidArgumentException $e) {
            return false;
        }
        return $output !== '' && $output !== null;
    }

    public function getClassMap(): ClassMap
    {
        return new ClassMap($this->configure['classMap'] ?? []);
    }

}
