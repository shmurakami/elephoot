<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Example;


class Client
{
    /**
     * @var \shmurakami\Elephoot\Example\Application
     */
    private $application;

    /**
     * Client constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function callApplication(): string
    {
        return $this->application->sampleMethod('Alice');
    }

    public function callByClosure(): string
    {
        return $this->application->callByClosure('Bob');
    }

    public function callExtendApplication(): array
    {
        $calls = [];
        $app = new ExtendApplication();
        $calls[] = $app->sampleMethod('Charlie');
        $calls[] = $this->application->sampleMethod('Dave');

        return $calls;
    }

    private function circularReference()
    {
        return new \shmurakami\Elephoot\Example\CircularReference\CircularReference1();
    }
}
