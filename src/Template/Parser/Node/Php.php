<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node;

use EXME\Template\Parser\Node\Contract\NodeInterface;

final readonly class Php implements NodeInterface
{
    use NodeTrait;

    public function __construct(
        public string $phpCode,
    ){
    }

    public function render(): string
    {
        return $this->phpCode;
    }
}