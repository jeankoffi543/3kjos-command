<?php

namespace Kjos\Command\Concerns\Helpers;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitorAbstract;

class TraitExtractor extends NodeVisitorAbstract
{
    public array $traits = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\TraitUse) {
            foreach ($node->traits as $trait) {
                $this->traits[] = $trait->toString();
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
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        return $this->traits;
    }
}
