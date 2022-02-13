<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Runner;

use shmurakami\Elephoot\Ast\Request;
use shmurakami\Elephoot\Output\Adaptor\AdaptorConfig;
use shmurakami\Elephoot\Output\Adaptor\GraphpAdaptor;
use shmurakami\Elephoot\Output\Drawer;
use shmurakami\Elephoot\Parser;

class Runner
{
    public function run(): void
    {
        /**
         * how to use
         * ./elephoot -c /path/to/config_path -o /path/to/check_file_path
         */
        $args = getopt('m:t:c:o:h::', ['mode:', 'target:', 'configure:', 'output:', 'help::']);

        $mode = $args['m'] ?? $args['mode'] ?? Request::MODE_CLASS;
        $target = $args['t'] ?? $args['target'] ?? '';
        $configure = $args['c'] ?? $args['configure'] ?? '';
        $output = $args['o'] ?? $args['output'] ?? getcwd();
        $help = (bool)($args['h'] ?? $args['help'] ?? false);
        if ($help) {
            $this->showHelp();
            return;
        }

        /** @psalm-suppress PossiblyInvalidArgument */
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

    private function showHelp(): void
    {
        $currentMethod = __METHOD__;

        $usage = <<<EOF
Usage:

-m Mode: Parse mode. Value can be "class" or "method".
-t Target class or method name. Class name must be FQCN. Use @ as separator to specify method name like "${currentMethod}".
-o Output directory path. If not passed, output to current directory.
-c Configuration file path. JSON format is supported. See README for more detail.
-h Show this message.
EOF;
        echo $usage . PHP_EOL;
    }

}
