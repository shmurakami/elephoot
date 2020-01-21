<?php

namespace shmurakami\Spice\Ast\Entity;

class Imports
{
    private $values;

    /**
     * ImportItems constructor.
     * @param Import[] $values
     */
    public function __construct($values)
    {
        $this->values = $values;
    }

    public function resolve(string $className): ?string
    {
        foreach ($this->values as $import) {
            $imported = $import->className();
            $className = str_replace('\\', '\\\\', $className);
            $pattern = "/${className}$/";
            if (preg_match($pattern, $imported)) {
                return $imported;
            }
        }
        return null;
    }

    public function values()
    {
        return $this->values;
    }

}
