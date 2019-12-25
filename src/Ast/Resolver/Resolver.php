<?php

namespace shmurakami\Spice\Ast\Resolver;

trait Resolver
{
    /**
     * @var Resolver
     */
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
