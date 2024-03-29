<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Parser;

use shmurakami\Elephoot\Ast\Context\Context;

trait DocCommentParser
{
    /**
     * @return Context[]
     */
    private function parseDocComment(ContextParser $contextParser, Context $context, string $docComment, string $annotationName): array
    {
        $classTypeLine = '';

        foreach (explode("\n", $docComment) as $commentLine) {
            // space is needed to declaration annotation
            if (strpos($commentLine, "$annotationName ") !== false) {
                $classTypeLine = $commentLine;
                break;
            }
        }

        if (!$classTypeLine) {
            return [];
        }

        // should be this? ^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$
        // https://www.php.net/manual/en/language.variables.basics.php
        preg_match("/$annotationName (.+)/", $commentLine, $matches);
        // can be multiple
        $classFqcnList = explode('|', $matches[1]);
        /** @psalm-suppress TypeDoesNotContainType */
        if (!$classFqcnList) {
            return [];
        }

        // FQCN has \\ prefix in doc comment but it's not needed
        // trim space and backslash
        for ($i = 0, $count = count($classFqcnList); $i < $count; $i++) {
            $classFqcn = trim($classFqcnList[$i], " \t\n\r \v\\");
            $classFqcnList[$i] = $classFqcn;
        }

        // end parts of doc comment line may have additional comment.
        // retrieve only type name
        $lastIndex = count($classFqcnList) - 1;
        $lastIndexParts = explode(' ', $classFqcnList[$lastIndex]);
        $classFqcnList[$lastIndex] = $lastIndexParts[0];

        $currentContextNamespace = $context->extractNamespace();

        return $contextParser->toContextList($currentContextNamespace, $classFqcnList);
    }

}
