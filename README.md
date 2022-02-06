# Elephoot

From Elephant footprint.

# About

Statically visualizing PHP source code dependencies by class tree.

# Dependency

- Graphviz

Install from https://www.graphviz.org/

# How to run

Any of below lines.

```sh
/path/to/bin/elephoot -m=class -o=$(pwd) -t=shmurakami\\Elephoot\\Example\\ExtendApplication

# help
Usage:

-m Mode: Parse mode. Value can be "class" or "method".
-t Target class or method name. Class name must be FQCN. Use @ as separator to specify method name like "shmurakami\Elephoot\Runner\Runner::showHelp".
-o Output directory path. If not passed, use system temporary directory.
-c Configuration file path. JSON format is supported. See README for more detail.
-h Show this message.
```

See below section for configuration file.


## For composer based project

WIP about autoload


## Using Docker

WIP command


# Configuration

All values follow syntax of command argument. See help message or [How to run](#how-to-run) for syntax.

See [sample configuration file](shmurakami/elephoot/.config_class.json) for full example.

```json
{
  "mode": "class",
  "target": "shmurakami\\Elephoot\\Ast\\Request",
  "output": "/tmp/foobar",
  "classMap": {
    "BreakingPsr": "/path/to/src/Example/other/BreakingPsr.php"
  }
}
```

`mode`, `target`, `outoput` can be overwritten by command argument. `classMap` is enabled to be defined in config file.

`classMap` is map like `{FQCN: filepath}`. FQCN can't have `\\` prefix.

# Output

For now dependency tree is generated as PNG file to output directory file named `elephoot.png`. In default output directory is system temporary diirectory like `/tmp`.
