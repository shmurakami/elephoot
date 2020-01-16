<?php

namespace shmurakami\Spice\Output\Adaptor;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\GraphViz\GraphViz;
use shmurakami\Spice\Exception\FileNotCreatedException;
use shmurakami\Spice\Output\Adaptor;
use shmurakami\Spice\Output\Tree;

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
    public function createDest(Tree $tree): string
    {
        $filepath = $this->convert($tree);
        $destPath = $this->adaptorConfig->getOutputDirectory() . '/spice.png';
        $created = @copy($filepath, $destPath);
        if (!$created) {
            throw new FileNotCreatedException("failed to copy image to $destPath");
        }
        return $filepath;
    }

    private function convert(Tree $tree): string
    {
//        $graphviz = new GraphViz();
//        $graphviz->display($this->buildGraph($classTree));
        return (new GraphViz())->createImageFile($this->buildGraph($tree));
    }

    public function buildGraph(Tree $tree): Graph
    {
        $graph = new Graph();
        $graph->setAttribute('graphviz.node.shape', 'rectangle');

        return $this->createNodeAndEdge($graph, $tree);
    }

    private function createNodeAndEdge(Graph $graph, Tree $tree, Vertex $parentNode = null): Graph
    {
        $nodeName = $tree->getRootNodeName();
        $graphNode = $this->retrieveNode($graph, $nodeName);

        if ($parentNode) {
            // connect parent to self
            $parentNode->createEdgeTo($graphNode);
        }

        foreach ($tree->getChildTrees() as $childTree) {
            $graph = $this->createNodeAndEdge($graph, $childTree, $graphNode);
        }
        return $graph;
    }

    private function retrieveNode(Graph $graph, string $nodeName): Vertex
    {
        if ($graph->hasVertex($nodeName)) {
            return $graph->getVertex($nodeName);
        }
        return $graph->createVertex($nodeName);
    }

}
