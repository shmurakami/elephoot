<?php

namespace shmurakami\Spice\Output;

class ClassTree
{
    /**
     * @var ClassTreeNode
     */
    private $rootNode;
    /**
     * @var ClassTree[]
     */
    private $childTree = [];

    /**
     * ClassTree constructor.
     */
    public function __construct(ClassTreeNode $rootNode)
    {
        $this->rootNode = $rootNode;
    }

    public function add(ClassTree $classTree)
    {
        $this->childTree[] = $classTree;
    }

    /**
     * @TODO delete it. this is just for test
     * @return string[]
     */
    public function toArray(array $nodes = []): array
    {
        $childNodes = [];
        foreach ($this->childTree as $childNode) {
            $childNodes[] = $childNode->toArray($nodes);
        }
        return [
            'className'  => $this->rootNode->getClassName(),
            'childNodes' => $childNodes,
        ];
    }
}
