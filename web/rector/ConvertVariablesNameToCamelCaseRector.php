<?php

declare(strict_types=1);

use Epifrin\RectorCustomRules\Helpers\StringHelper;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Php\ReservedKeywordAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConvertVariablesNameToCamelCaseRector extends AbstractRector
{
    /** @var array<string> */
    private array $properties = [];

    public function __construct(
        private ReservedKeywordAnalyzer $reservedKeywordAnalyzer,
    ) {
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Variable|ClassLike $node
     * @return Node|null
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassLike) {
            // Get properties declared in the class body
            foreach ($node->getProperties() as &$property) {
                if (isset($property->props[0])) {
                    $propertyName = $property->props[0]->name->toString();
                    $newPropertyName = StringHelper::toCamelCase($propertyName);
                    $property->props[0]->name = new Node\VarLikeIdentifier($newPropertyName);
                }
            }
        }

        return $node;
    }


    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Converts the names of class variables to camelCase', [
            new CodeSample(
            // code before
                'public $my_variable = 1;',
                // code after
                'public $myVariable = 1;'
            ),
        ]);
    }
}
