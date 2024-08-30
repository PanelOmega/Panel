<?php

declare(strict_types=1);

use Epifrin\RectorCustomRules\Helpers\StringHelper;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Php\ReservedKeywordAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConvertLocalVariablesNameToCamelCaseRector extends AbstractRector
{
    /** @var array<string> */
    private array $properties = [];

    public function __construct(
        private ReservedKeywordAnalyzer $reservedKeywordAnalyzer,
    ) {
    }

    public function getNodeTypes(): array
    {
        return [Class_::class, Variable::class];
    }

    /**
     * @param Variable|ClassLike $node
     * @return Node|null
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassLike) {
            // Get properties declared in the class body
            foreach ($node->getProperties() as $property) {
                if (isset($property->props[0])) {
                    $this->properties[] = $property->props[0]->name->toString();
                }
            }

            // Get properties declared in the constructor
            $constructor = $node->getMethod('__construct');
            if ($constructor) {
                foreach ($constructor->getParams() as $param) {
                    if (
                        $param->flags !== 0 &&
                        $param->var instanceof Node\Expr\Variable &&
                        is_string($param->var->name)
                    ) {
                        $this->properties[] = $param->var->name;
                    }
                }
            }

            return null;
        }

        return $this->processVariable($node);
    }

    private function processVariable(Variable $node): ?Node
    {
        $currentName = $node->name;

        if ($currentName instanceof Node\Expr) {
            return null;
        }

        if ($this->reservedKeywordAnalyzer->isNativeVariable($currentName)) {
            return null;
        }

        if ($currentName === 'this') {
            return null;
        }

        // Skip the variable if its name corresponds to a property name, as this could potentially lead to the construction of property promotion.
        if (in_array($currentName, $this->properties, true)) {
            return null;
        }

        $newName = StringHelper::toCamelCase($currentName);

        if ($newName === '') {
            return null;
        }

        // Skip if the name is already in camelCase
        if ($currentName === $newName) {
            return null;
        }

        $node->name = $newName;

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Converts the names of local variables to camelCase', [
            new CodeSample(
            // code before
                '$my_variable = 1;',
                // code after
                '$myVariable = 1;'
            ),
        ]);
    }
}
