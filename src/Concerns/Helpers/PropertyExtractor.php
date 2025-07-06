<?php

namespace Kjos\Command\Concerns\Helpers;

use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class PropertyExtractor extends NodeVisitorAbstract
{
    public array $properties = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Property) {
            $visibility = $this->getVisibility($node);

            foreach ($node->props as $prop) {
                $this->properties[] = [
                    'name' => $prop->name->toString(),
                    'visibility' => $visibility,
                    'type' => $this->resolveType($node->type),
                    'default' => $this->resolveDefault($prop->default),
                    'static' => $node->isStatic(),
                    'readonly' => $node->isReadonly(),
                ];
            }
        }
    }

    public function extractFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $code = file_get_contents($filePath);
        return $this->extractFromCode($code);
    }

    public function extractFromCode(string $code): array
    {
        $this->properties = [];

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        return $this->properties;
    }

    private function getVisibility(Node\Stmt\Property $node): string
    {
        if ($node->isPublic()) return 'public';
        if ($node->isProtected()) return 'protected';
        if ($node->isPrivate()) return 'private';
        return 'public';
    }

    private function resolveType($type): ?string
    {
        if ($type instanceof Node\NullableType) {
            return '?' . $this->resolveType($type->type);
        }

        if ($type instanceof Node\UnionType) {
            return implode('|', array_map([$this, 'resolveType'], $type->types));
        }

        if ($type instanceof Node\IntersectionType) {
            return implode('&', array_map([$this, 'resolveType'], $type->types));
        }

        return $type ? $type->toString() : null;
    }

    private function resolveDefault(?Node\Expr $default): mixed
    {
        if ($default === null) return null;

        return match (true) {
            $default instanceof Node\Scalar\String_,
            $default instanceof Node\Scalar\LNumber,
            $default instanceof Node\Scalar\DNumber,
            $default instanceof Node\Scalar\MagicConst => $default->value,

            $default instanceof Node\Expr\ConstFetch => $default->name->toString(),

            $default instanceof Node\Expr\Array_ => 'array',

            default => 'expression',
        };
    }
}
