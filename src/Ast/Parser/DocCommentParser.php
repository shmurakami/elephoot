<?php

namespace shmurakami\Spice\Ast\Parser;

use shmurakami\Spice\Ast\Context\ClassContext;
use shmurakami\Spice\Ast\Context\Context;

trait DocCommentParser
{
    /**
     * @param string $docComment
     * @param string $annotationName
     * @return string[]
     * TODO this method should return Context[]
     */
    private function parseDocComment(string $docComment, string $annotationName)
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

        return $classFqcnList;
    }

    private function isFqcn(string $className): bool
    {
        return strpos($className, '\\') !== false;
    }

    private function toContext(string $contextNamespace, string $className): Context
    {
        if ($this->isFqcn($className)) {
            return new ClassContext($className);
        }
        return new ClassContext($contextNamespace . '\\' . $className);
    }

}
