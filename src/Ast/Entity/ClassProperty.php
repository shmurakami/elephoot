<?php

namespace shmurakami\Spice\Ast\Entity;

use ast\Node;

class ClassProperty
{
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var string
     */
    private $className;
    /**
     * @var Node
     */
    private $propertyNode;
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var string
     */
    private $docComment;

    public function __construct(string $namespace, string $className, Node $propertyNode)
    {
        $this->namespace = $namespace;
        $this->className = $className;
        $this->propertyNode = $propertyNode;

        // retrieve doc comment

        /** @var Node $propDeclaration */
        $propDeclaration = $propertyNode->children['props'];
        /** @var Node $propElement */
        $propElement = $propDeclaration->children[0];
        $this->propertyName = $propElement->children['name'];
        $this->docComment = $propElement->children['docComment'] ?? '';
    }

    /**
     * TODO check it's callable
     */
    public function parse(): ?ClassAst
    {

    }

    public function isCallable(): bool
    {
        return true;
    }

    /**
     * parse doc comment
     * return AstEntity if this property is class instance
     *
     * @return string[]
     */
    public function classFqcnListFromDocComment(): array
    {
        if ($this->docComment === '') {
            return [];
        }

        $classTypeLine = '';

        foreach (explode("\n", $this->docComment) as $commentLine) {
            if (strpos($commentLine, '@var ') !== false) {
                $classTypeLine = $commentLine;
                break;
            }
        }

        if (!$classTypeLine) {
            return [];
        }

        $dependentClassFqcnList = [];

        // should be this? ^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$
        // https://www.php.net/manual/en/language.variables.basics.php
        preg_match('/@var (.+)/', $commentLine, $matches);
        // can be multiple
        $classFqcnList = explode('|', $matches[1]);

        for ($i = 0, $count = count($classFqcnList); $i < $count; $i++) {
            $classFqcn = trim($classFqcnList[$i]);
            // end parts may have additional comment
            if ($i === $count - 1) {
                $parts = explode(' ', $classFqcn);
                // FQCN has \\ prefix in doc comment but it's not needed
                // trim space and backslash
                $classFqcn = trim($parts[0], " \t\n\r \v\\");
            }

            if ($this->isNotSupportedPhpBaseType($classFqcn)) {
                continue;
            }

            // if \ is included it means fqcn. no need to touch
            if (strpos($classFqcn, '\\') === false) {
                // global space or same namespace instance
                // if class has namespace, assume as same namespace
                // otherwise assume as global namespace
                $baseNamespace = $this->namespace ?? '';
                $classFqcn = $baseNamespace . '\\' . $classFqcn;
            }

            $dependentClassFqcnList[] = $classFqcn;
        }

        return $dependentClassFqcnList;
    }

    private function isNotSupportedPhpBaseType(string $classType): bool
    {
        return in_array($classType, [
            'int', 'integer',
            'string',
            'bool', 'boolean',
            'float',
            'double',
            'object',
            'array', // array can be callable...
            'callable',
            'iterable',
            'mixed',
            'number',
            'void',
        ], true);
    }

}
