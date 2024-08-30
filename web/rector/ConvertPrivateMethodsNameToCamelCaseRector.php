<?php

declare(strict_types=1);

use Epifrin\RectorCustomRules\Helpers\StringHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConvertPrivateMethodsNameToCamelCaseRector extends AbstractRector
{
    /** @var array<string> */
    private array $privateMethods = [];

    public function getNodeTypes(): array
    {
        return [Class_::class, ClassMethod::class, StaticCall::class, MethodCall::class];
    }

    /**
     * @param ClassMethod|StaticCall|MethodCall|ClassLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorMethodDeclaration($node);
        }

        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }

        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }

        if ($node instanceof ClassLike) {
            foreach ($node->getMethods() as $method) {
                if ($method->isPrivate()) {
                    $this->privateMethods[] = $method->name->toString();
                }
            }
        }

        return null;
    }

    private function refactorMethodDeclaration(ClassMethod $node): ?Node
    {
        if (!$node->isPrivate()) {
            return null;
        }

        $oldName = $node->name->toString();
        $newName = StringHelper::toCamelCase($oldName);
        $newName = $this->addUnderscore($newName);

        if ($oldName === $newName) {
            return null; // Skip if the name is already in camelCase
        }

        $node->name = new Node\Identifier($newName);

        return $node;
    }

    private function refactorMethodCall(MethodCall $node): ?Node
    {
        if (!($node->var instanceof Node\Expr\Variable) || $node->var->name !== 'this') {
            return null;
        }

        $methodCallName = $this->getName($node->name);

        if ($methodCallName === null) {
            return null;
        }

        if (!in_array($methodCallName, $this->privateMethods, true)) {
            return null;
        }

        $newMethodCallName = StringHelper::toCamelCase($methodCallName);

        if ($methodCallName === $newMethodCallName) {
            return null; // Skip if the name is already in camelCase
        }

        $node->name = new Node\Identifier($newMethodCallName);

        return $node;
    }

    private function refactorStaticCall(StaticCall $node): ?Node
    {
        if (!($node->class instanceof Node\Name) || $node->class->toString() !== 'self') {
            return null;
        }

        $methodCallName = $this->getName($node->name);

        if (!in_array($methodCallName, $this->privateMethods, true)) {
            return null;
        }

        $newMethodCallName = StringHelper::toCamelCase($methodCallName);
        $newMethodCallName = $this->addUnderscore($newMethodCallName);

        if ($methodCallName === $newMethodCallName) {
            return null; // Skip if the name is already in camelCase
        }

        $node->name = new Node\Identifier($newMethodCallName);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Converts the names of private methods to camelCase', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class MyClass
{
    public function do()
    {
        return $this->is_snake_case();
    }

    private function is_snake_case()
    {
        // Some code here
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class MyClass
{
    public function do()
    {
        return $this->isSnakeCase();
    }

    private function isSnakeCase()
    {
        // Some code here
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function addUnderscore($string): string
    {
        if (strpos($string, '_') !== false) {
            return $string;
        }

        return '_' . $string;
    }
}
