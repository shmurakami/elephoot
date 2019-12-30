<?php

namespace shmurakami\Spice\Example;

class SomeProperty
{
    /**
     * @var Application | ExtendApplication some comments
     */
    private $application;
    /**
     * @var \shmurakami\Spice\Example\Application
     */
    private $application2;
    /**
     * @var ExtendApplication
     */
    private $extendApplication;
    /**
     * @var NotExistingClass
     */
    private $wrong;
    /**
     * does not have var annotation
     */
    private $client;

}
