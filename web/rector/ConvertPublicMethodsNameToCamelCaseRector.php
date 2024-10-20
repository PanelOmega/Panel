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

final class ConvertPublicMethodsNameToCamelCaseRector extends AbstractRector
{
    /** @var array<string> */
    private array $publicMethods = [];

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
            return $this->_refactorMethodDeclaration($node);
        }

        if ($node instanceof MethodCall) {
            return $this->_refactorMethodCall($node);
        }

        if ($node instanceof StaticCall) {
            return $this->_refactorStaticCall($node);
        }

        if ($node instanceof ClassLike) {
            foreach ($node->getMethods() as $method) {
                if ($method->isPublic()) {
                    $this->publicMethods[] = $method->name->toString();
                }
            }
        }

        return null;
    }

    private function _refactorMethodDeclaration(ClassMethod $node): ?Node
    {
        if (!$node->isPublic()) {
            return null;
        }

        $oldName = $node->name->toString();
        if ($oldName === '__construct') {
            return null;
        }
        if ($oldName === '__destruct') {
            return null;
        }

        $newName = StringHelper::toCamelCase($oldName);

        if ($oldName === $newName) {
            return null; // Skip if the name is already in camelCase
        }

        $node->name = new Node\Identifier($newName);

        return $node;
    }

    private function _refactorMethodCall(MethodCall $node): ?Node
    {
        if (!($node->var instanceof Node\Expr\Variable) || $node->var->name !== 'this') {
            return null;
        }

        $methodCallName = $this->getName($node->name);

        if ($methodCallName === null) {
            return null;
        }

        if (!in_array($methodCallName, $this->publicMethods, true)) {
            return null;
        }

        $newMethodCallName = StringHelper::toCamelCase($methodCallName);

        if ($methodCallName === $newMethodCallName) {
            return null; // Skip if the name is already in camelCase
        }

        $node->name = new Node\Identifier($newMethodCallName);

        return $node;
    }

    private function _refactorStaticCall(StaticCall $node): ?Node
    {
        if (!($node->class instanceof Node\Name) || $node->class->toString() !== 'self') {
            return null;
        }

        $methodCallName = $this->getName($node->name);

        if (!in_array($methodCallName, $this->publicMethods, true)) {
            return null;
        }

        $newMethodCallName = StringHelper::toCamelCase($methodCallName);

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

    public function is_snake_case()
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
        return $this->_isSnakeCase();
    }

    public function isSnakeCase()
    {
        // Some code here
    }
}
CODE_SAMPLE
            ),
        ]);
    }
}
