<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node;

use EXME\Template\Parser\Node\Contract\NodeInterface;

final readonly class Fragment implements NodeInterface
{
    use NodeTrait;
    
    public readonly array $children;
    /**
     * @param array<int, NodeInterface> $children
     */
    public function __construct(NodeInterface ...$children)
    {
        $this->children = $children;
    }

    public function render(): string
    {
        $html = [];

        foreach ($this->children as $child) {
            $html[] = $child->render();
        }

        return implode('', $html);
    }
}