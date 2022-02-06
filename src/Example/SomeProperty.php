<?php

namespace shmurakami\Elephoot\Example;

class SomeProperty
{
    /**
     * @var Application | ExtendApplication some comments
     */
    private $application;
    /**
     * @var \shmurakami\Elephoot\Example\Application
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
