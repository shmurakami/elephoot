<?php

namespace shmurakami\Spice\Runner;

use shmurakami\Spice\Ast\Request;
use shmurakami\Spice\Output\Adaptor\AdaptorConfig;
use shmurakami\Spice\Output\Adaptor\GraphpAdaptor;
use shmurakami\Spice\Output\Drawer;
use shmurakami\Spice\Parser;

class Runner
{
    public function run()
    {
        /**
         * how to use
         * ./spice -c /path/to/config_path -o /path/to/check_file_path
         */
        $args = getopt('m:t:c:o:h::', ['mode:', 'target:', 'configure:', 'output:', 'help::']);

        $mode = $args['m'] ?? $args['mode'] ?? Request::MODE_CLASS;
        $target = $args['t'] ?? $args['target'] ?? '';
        $configure = $args['c'] ?? $args['configure'] ?? '';
        $output = $args['o'] ?? $args['output'] ?? '';
        $help = (bool)($args['h'] ?? $args['help'] ?? false);
        if ($help) {
            $this->showHelp();
            return;
        }

        $request = new Request($mode, $target, $output, $configure);
        if (!$request->isValid()) {
            $this->showHelp();
            return;
        }

        $parser = new Parser($request);
        $objectRelationTree = $parser->parse();
        $graphpAdaptor = new GraphpAdaptor(new AdaptorConfig($request->getOutputDirectory()));
        $drawer = new Drawer($graphpAdaptor);
        $filepath = $drawer->draw($objectRelationTree);

        echo "file outputted to $filepath\n";
    }

    private function showHelp()
    {
        $currentMethod = __METHOD__;

        $usage = <<<EOF
Usage:

-m Mode: Parse mode. Value can be "class" or "method".
-t Target class or method name. Class name must be FQCN. Use @ as separator to specify method name like "${currentMethod}".
-o Output directory path. If not passed, use system temporary directory.
-c Configuration file path. JSON format is supported. See README for more detail.
-h Show this message.
EOF;
        echo $usage . PHP_EOL;
    }

}
