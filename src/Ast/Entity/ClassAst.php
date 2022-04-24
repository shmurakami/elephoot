<?php

declare(strict_types=1);

namespace shmurakami\Elephoot\Ast\Entity;

use ast\Node;
use Generator;
use shmurakami\Elephoot\Ast\Context\ClassContext;
use shmurakami\Elephoot\Ast\Context\Context;
use shmurakami\Elephoot\Ast\Entity\Node\MethodNode;
use shmurakami\Elephoot\Ast\Parser\AstParser;
use shmurakami\Elephoot\Ast\Parser\ContextParser;
use shmurakami\Elephoot\Ast\Resolver\ClassAstResolver;
use shmurakami\Elephoot\Exception\MethodNotFoundException;
use shmurakami\Elephoot\Output\ClassTreeNode;
use shmurakami\Elephoot\Stub\Kind;

class ClassAst
{
    /**
     * @var ClassProperty[]
     */
    private array $properties = [];

    private string $namespace;
//    private string $className;

    public function __construct(
        private Node $classRootNode,
        private Context $context,
        private ContextParser $contextParser,
        private AstParser $astParser
    )
    {
        $this->namespace = $context->extractNamespace();
        $this->parseProperties($context);
    }

    private function parseProperties(Context $context): void
    {
        foreach ($this->astParser->propertyGroupNodes($this->classRootNode) as $propertyGroupNode) {
            $this->properties[] = new ClassProperty($this->contextParser, $context, $propertyGroupNode);
        }
    }

    public function parseMethod(string $method): MethodAst
    {
//        foreach ($this->extractNodes(Kind::AST_METHOD) as $methodNode) {
//            $rootMethod = $methodNode->children['name'];
//            if ($rootMethod === $method) {
//                $fqcn = $this->namespace . '\\' . $this->className;
//                return new MethodAst($methodNode, new MethodContext($fqcn, $method), $this->properties, $this->astParser);
//            }
//        }
        throw new MethodNotFoundException();
    }

    /**
     * @return ClassAst[]
     */
    public function relatedClasses(ClassAstResolver $classAstResolver): array
    {
        $dependentClassAstResolver = $this->dependentClassAstResolver($classAstResolver);
        $resolver = function (array $contexts) use ($dependentClassAstResolver): void {
            foreach ($contexts as $context) {
                $dependentClassAstResolver->send($context);
            }
        };

        // using trait
        $resolver($this->extractUsingTrait());

        // property, only need to parse doc comment
        foreach ($this->properties as $classProperty) {
            $resolver($classProperty->classContextListFromDocComment());
        }

        // parse method
        $resolver($this->extractContextListFromMethodNodes());

        // parse new statements
        $resolver($this->parseNewStatement($this->classRootNode));

        // send null to call generator return
        $dependentClassAstResolver->next();
        return $dependentClassAstResolver->getReturn();
    }

    /**
     * @return Generator
     */
    private function dependentClassAstResolver(ClassAstResolver $classAstResolver): Generator
    {
        // to not search same wrong name class is given sometime
        $resolved = [];
        $dependencies = [];

        $currentContextFqcn = $this->context->fqcn();

        while (true) {
            /** @var ?Context $context */
            $context = yield;
            // give null to finish
            if ($context === null) {
                return $dependencies;
            }

            $targetFqcn = $context->fqcn();

            if (isset($resolved[$targetFqcn])) {
                continue;
            }

            // fqcn is same with current target class, skip to avoid infinite loop
            if ($currentContextFqcn === $targetFqcn) {
                $resolved[$targetFqcn] = true;
                continue;
            }

            $classAst = $classAstResolver->resolve($context->fqcn());
            if ($classAst) {
                $dependencies[$targetFqcn] = $classAst;
            }

            $resolved[$targetFqcn] = true;
        }
    }

    /**
     * @return Context[]
     */
    private function extractContextListFromMethodNodes(): array
    {
        $dependencyContexts = [];
        foreach ($this->astParser->extractMethodNodes($this->classRootNode) as $methodNode) {
            $methodNode = new MethodNode($this->contextParser, $this->context, $methodNode);
            foreach ($methodNode->parseMethodAttributeToContexts() as $context) {
                $fqcn = $context->fqcn();
                if (isset($dependencyContexts[$fqcn])) {
                    continue;
                }
                $dependencyContexts[$fqcn] = $context;
            }
        }

        return array_values($dependencyContexts);
    }

    /**
     * digging class statement
     * TODO refactoring
     *
     * @param Node $rootNode
     * @param Context[] $contextList
     * @return Context[]
     */
    private function parseNewStatement(Node $rootNode, array $contextList = []): array
    {
        $kind = $rootNode->kind;

        // has child statements
        if (in_array($kind, [Kind::AST_CLASS, Kind::AST_METHOD, Kind::AST_CLOSURE, Kind::AST_ARROW_FUNC], true)) {
            $statementNodes = $this->statementNodes($rootNode);
            foreach ($statementNodes as $statementNode) {
                $contextList = $this->parseNewStatement($statementNode, $contextList);
            }
            return $contextList;
        }

        // see right statement
        if (in_array($kind, [Kind::AST_ASSIGN, Kind::AST_RETURN])) {
            $rightStatementNode = $rootNode->children['expr'];
            if (!($rightStatementNode instanceof Node)) {
                return $contextList;
            }
            if ($kind === Kind::AST_RETURN) {
                return $this->parseNewStatement($rightStatementNode, $contextList);
            }
            if (in_array($rightStatementNode->kind, [Kind::AST_NEW, Kind::AST_ARROW_FUNC], true)) {
                return $this->parseNewStatement($rightStatementNode, $contextList);
            }
            return $contextList;
        }

        // method call
        if (in_array($kind, [Kind::AST_METHOD_CALL, Kind::AST_STATIC_CALL])) {
            // if call method without variable assigning? like (new hogehoge())->foobar()
            // need expr for call instance method? if so, call self recursively
            $exprNode = $rootNode->children['expr'] ?? null;
            // to make method tree
            $method = $rootNode->children['method'];
            // for static method call
            $staticMethodClassNode = $rootNode->children['class'] ?? null;
            if ($staticMethodClassNode) {
                $newClassName = $staticMethodClassNode->children['name'];
                $context = $this->contextParser->toContext($this->namespace, $newClassName);
                if ($context) {
                    $contextList[] = $context;
                }
            }

            $argumentNodes = $rootNode->children['args']->children ?? [];
            foreach ($argumentNodes as $argumentNode) {
                if ($argumentNode instanceof Node) {
                    $contextList = $this->parseNewStatement($argumentNode, $contextList);
                }
            }
            return $contextList;
        }

        // not assigning new, e.g. in argument
        if ($kind === Kind::AST_NEW) {
            return $this->parseNewStatementContextList($rootNode, $contextList);
        }

        // enum
        // enum is same as const value
        if ($kind === Kind::AST_CLASS_CONST) {
            $context = $this->contextParser->toContext($this->namespace, $rootNode->children['class']?->children['name'] ?? '');
            if ($context) {
                $contextList[] = $context;
            }
            return $contextList;
        }

        // nothing to do
        if (in_array($kind, [Kind::AST_PROP_GROUP])) {
            return $contextList;
        }

        return $contextList;
    }

    /**
     * @return Context[]
     */
    private function parseNewStatementContextList(Node $node, array $contextList = [])
    {
        $fqcnList = $this->parseNewStatementFqcnList($node, []);

        foreach ($this->contextParser->toContextList($this->namespace, $fqcnList) as $context) {
            $contextList[] = $context;
        }
        return $contextList;
    }

    /**
     * new statement(constructor) argument can be another new statement
     * and it's nestable
     */
    private function parseNewStatementFqcnList(Node $node, array $list = []): array
    {
        $arguments = $node->children['args']->children ?? [];
        foreach ($arguments as $argumentNode) {
            if ($argumentNode->kind === Kind::AST_NEW) {
                foreach ($this->parseNewStatementFqcnList($argumentNode, $list) as $fqcn) {
                    $list[] = $fqcn;
                }
            }
        }

        // if class name by assigned to variable?
        $newClassName = $node->children['class']->children['name'];
        $list[] = $newClassName;
        return $list;
    }

    /**
     * @return Context[]
     */
    private function extractUsingTrait(): array
    {
        return array_map(
            fn(Node $traitNode) => new ClassContext(fqcn: $traitNode->children['name']),
            $this->astParser->extractUsingTrait($this->classRootNode));
    }

    public function treeNode(): ClassTreeNode
    {
        return new ClassTreeNode($this->context);
    }

    public function fqcn(): string
    {
        // TODO weird
        return $this->context->fqcn();
    }

    /**
     * @return Node[]
     */
    private function statementNodes(Node $rootNode = null): array
    {
        if (!$rootNode) {
            $rootNode = $this->classRootNode;
        }
        return $rootNode->children['stmts']->children ?? [];
    }

}
