<?php

namespace shmurakami\Elephoot\Example\Enum;

enum Animal
{
    case DOG;
    case CAT;

    public static function fromName(string $name): Animal
    {
        return match ($name) {
            'dog' => self::DOG,
            'cat' => self::CAT,
            default => self::DOG,
        };
    }
}