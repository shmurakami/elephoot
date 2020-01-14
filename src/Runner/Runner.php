<?php

namespace shmurakami\Spice\Runner;

use shmurakami\Spice\Ast\Request;
use shmurakami\Spice\Parser;

class Runner
{
    public function run()
    {
        /**
         * how to use
         * ./spice -c /path/to/config_path -o /path/to/check_file_path
         */
        $args = getopt('m:t:c::o::', ['mode:', 'target:', 'configure::', 'output::']);

        $mode = $args['m'] ?? $args['mode'] ?? Request::MODE_CLASS;
        $target = $args['t'] ?? $args['target'] ?? '';
        $configure = $args['c'] ?? $args['configure'] ?? '';
        $output = $args['o'] ?? $args['output'] ?? '';

        $request = new Request($mode, $target, $output, $configure);

        // make config instance to parse file
        // and another option? needs to parse arguments
        // ah no no, config should treat them all.

        // prior command option than config file

        $parser = new Parser($request);
        $parser->parse();
    }

}
