<?php

namespace Kjos\Command\Concerns\Helpers;

use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

class MethodExtractor extends NodeVisitorAbstract
{
    public array $methods = [];
    public array $fullMethods = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            // Métadonnées simples
            $this->methods[] = [
                'name' => $node->name->toString(),
                'visibility' => $this->getVisibility($node),
                'return' => $this->resolveReturnType($node->returnType),
            ];

            // Code complet de la méthode
            $printer = new Standard();
            $this->fullMethods[] = $printer->prettyPrint([$node]);
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
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        return $this->methods;
    }

    public function extractFullMethodsFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        // Réinitialisation avant nouveau traitement
        $this->methods = [];
        $this->fullMethods = [];

        $code = file_get_contents($filePath);
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        return $this->fullMethods;
    }

    private function getVisibility(Node\Stmt\ClassMethod $node): string
    {
        if ($node->isPublic()) return 'public';
        if ($node->isProtected()) return 'protected';
        if ($node->isPrivate()) return 'private';
        return 'public';
    }

    private function resolveReturnType($type): string
    {
        if ($type instanceof Node\NullableType) {
            return '?' . $this->resolveReturnType($type->type);
        }

        if ($type instanceof Node\UnionType) {
            return implode('|', array_map([$this, 'resolveReturnType'], $type->types));
        }

        if ($type instanceof Node\IntersectionType) {
            return implode('&', array_map([$this, 'resolveReturnType'], $type->types));
        }

        return $type ? $type->toString() : 'void';
    }
}
