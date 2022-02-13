<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Output\Adaptor;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\GraphViz\GraphViz;
use shmurakami\Elephoot\Exception\FileNotCreatedException;
use shmurakami\Elephoot\Output\Adaptor;
use shmurakami\Elephoot\Output\ClassTree;
use shmurakami\Elephoot\Output\ObjectRelationTree;

class GraphpAdaptor implements Adaptor
{
    /**
     * cache as marker of edge already connected
     * @var array<string,array<int|string, true>>
     */
    private $related = [];

    public function __construct(private AdaptorConfig $adaptorConfig)
    {
    }

    /**
     * @inheritDoc
     */
    public function createDest(ObjectRelationTree $classTree): string
    {
        $filepath = $this->convert($classTree);
        $destPath = $this->adaptorConfig->getOutputDirectory() . '/elephoot.png';
        $created = @copy($filepath, $destPath);
        if (!$created) {
            throw new FileNotCreatedException("failed to copy image to $destPath");
        }
        return $destPath;
    }

    private function convert(ObjectRelationTree $classTree): string
    {
//        $graphviz = new GraphViz();
//        $graphviz->display($this->buildGraph($classTree));
        return (new GraphViz())->createImageFile($this->buildGraph($classTree));
    }

    public function buildGraph(ObjectRelationTree $classTree): Graph
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.node.shape', 'rectangle');

        return $this->createNodeAndEdge($graph, $classTree);
    }

    private function createNodeAndEdge(Graph $graph, ObjectRelationTree $classTree, Vertex $parentNode = null): Graph
    {
        $className = $classTree->getRootNodeClassName();
        $graphNode = $this->retrieveNode($graph, $className);

        if ($parentNode) {
            $graphNodeId = $graphNode->getId();
            if (!isset($this->related[$className][$graphNodeId])) {
                // connect parent to self
                $parentNode->createEdgeTo($graphNode);
                $this->related[$className][$graphNodeId] = true;
            }
        }

        foreach ($classTree->getChildTrees() as $childTree) {
            $graph = $this->createNodeAndEdge($graph, $childTree, $graphNode);
        }
        return $graph;
    }

    private function retrieveNode(Graph $graph, string $className): Vertex
    {
        if ($graph->hasVertex($className)) {
            return $graph->getVertex($className);
        }
        /** @psalm-suppress InvalidScalarArgument */
        return $graph->createVertex($className);
    }

}
