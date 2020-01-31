<?php

namespace shmurakami\Spice\Ast\Entity;

class ClassProperties
{

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
     * @return PropertyMap
     */
    public function propertyContextMap(): PropertyMap
    {
        $propertyMap = new PropertyMap();
        foreach ($this->values as $property) {
            // doc comment may has multi types. how to retrieve exact one?
            // must trace instance condition
            $context = $property->classFqcnListFromDocComment()[0] ?? null;
            $propertyMap->update($property->getPropertyName(), $context);
        }
        return $propertyMap;
    }
}
