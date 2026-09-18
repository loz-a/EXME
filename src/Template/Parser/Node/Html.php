<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node;

use EXME\Template\Parser\Node\Contract\NodeInterface;

final readonly class Html implements NodeInterface
{
    use NodeTrait;

    public function __construct(
        public string $html,
    ){
    }

    public function render(): string
    {
        return $this->html;
    }
}