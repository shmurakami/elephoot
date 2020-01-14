<?php

namespace shmurakami\Spice\Output\Adaptor;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\GraphViz\GraphViz;
use shmurakami\Spice\Exception\FileNotCreatedException;
use shmurakami\Spice\Output\Adaptor;
use shmurakami\Spice\Output\ClassTree;

class GraphpAdaptor implements Adaptor
{
    /**
     * @var AdaptorConfig
     */
    private $adaptorConfig;

    public function __construct(AdaptorConfig $adaptorConfig)
    {
        $this->adaptorConfig = $adaptorConfig;
    }

    /**
     * @inheritDoc
     */
    public function createDest(ClassTree $classTree): string
    {
        $filepath = $this->convert($classTree);
        $outputDirectory = $this->adaptorConfig->getOutputDirectory();
        $created = copy($filepath, $outputDirectory);
        if (!$created) {
            throw new FileNotCreatedException("failed to copy image to $outputDirectory");
        }
        return $filepath;
    }

    private function convert(ClassTree $classTree): string
    {
        $graphviz = new GraphViz();
        $graphviz->display($this->buildGraph($classTree));
        return (new GraphViz())->createImageFile($this->buildGraph($classTree));
    }

    public function buildGraph(ClassTree $classTree): Graph
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.node.shape', 'rectangle');

        return $this->createNodeAndEdge($graph, $classTree);
    }

    private function createNodeAndEdge(Graph $graph, ClassTree $classTree, Vertex $parentNode = null): Graph
    {
        $className = $classTree->getRootNodeClassName();
        $graphNode = $graph->createVertex($className);

        if ($parentNode) {
            // connect parent to self
            $parentNode->createEdgeTo($graphNode);
        }

        foreach ($classTree->getChildTrees() as $childTree) {
            $graph = $this->createNodeAndEdge($graph, $childTree, $graphNode);
        }
        return $graph;
    }

}
