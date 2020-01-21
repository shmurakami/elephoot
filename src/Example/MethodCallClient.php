<?php

namespace shmurakami\Spice\Example;

use shmurakami\Spice\Example\StaticMethod\StaticMethodCall;
use shmurakami\Spice\Example\StaticMethod\StaticMethodCallArgument;

class MethodCallClient extends Client
{
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
    }

    private function thisMethodCall()
    {
    }

    private static function selfStaticMethodCall()
    {
    }

}
