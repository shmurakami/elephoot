<?php

namespace shmurakami\Spice\Ast\Entity;

use shmurakami\Spice\Ast\Parser\FqcnParser;

class ClassProperties
{
    use FqcnParser;

    /**
     * @var ClassProperty[]
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return string[][]
     */
    public function classFqcnListFromDocComment(): array
    {
        return array_map(function (ClassProperty $property) {
            return $property->classFqcnListFromDocComment();
        }, $this->values);
    }

    /**
     * @param Imports $imports
     * @return PropertyMap
     */
    public function propertyContextMap(Imports $imports): PropertyMap
    {
        $propertyMap = new PropertyMap();
        foreach ($this->values as $property) {
            // doc comment may has multi types. how to retrieve exact one?
            // must trace instance condition
            $context = null;
            $className = $property->classFqcnListFromDocComment()[0] ?? null;
            if ($className) {
                $context = $this->parseFqcnWithImports($className, $imports);
            }
            $propertyMap->update($property->getPropertyName(), $context);
        }
        return $propertyMap;
    }
}
