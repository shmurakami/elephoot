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

        // external static method call
//        StaticMethodCall::byStaticMethodCall(new StaticMethodCallArgument());
    }

    private function thisMethodCall()
    {
    }

    private static function selfStaticMethodCall()
    {
    }

}
