<?php

namespace shmurakami\Spice\Example;

use shmurakami\Spice\Example\MagicMethod\Constructor;
use shmurakami\Spice\Example\MagicMethod\ConstructorArgument;
use shmurakami\Spice\Example\StaticMethod\StaticMethodCall;
use shmurakami\Spice\Example\StaticMethod\StaticMethodCallArgument;
use shmurakami\Spice\Example\Traits\UsingTrait;

class MethodCallClient extends Client
{
    use UsingTrait;

    /**
     * @var Application
     */
    private $application;

    public function endpoint(string $s = '')
    {
        // this instance method call
        $this->thisMethodCall();
        // self class method call
        self::selfStaticMethodCall();

        // external static method call with FQCN
        $staticArgument = new StaticMethodCallArgument();
        \shmurakami\Spice\Example\StaticMethod\StaticMethodCall::byStaticMethodCall($staticArgument);
        // with alias
        StaticMethodCall::byStaticMethodCall($staticArgument);

        $constructor = new Constructor(new ConstructorArgument());

        // closure
        $closure = function () {
            // this from closure
            $this->thisMethodCall();
        };
        $closure();

        // property method call
        $this->application->doNothing();

        // trait method
        $this->traitMethod();
    }

    private function thisMethodCall()
    {
    }

    private static function selfStaticMethodCall()
    {
    }

}
