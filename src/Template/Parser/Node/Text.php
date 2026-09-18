<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node;

use EXME\Template\Parser\Node\Contract\NodeInterface;

use function htmlspecialchars;

final readonly class Text implements NodeInterface
{
    use NodeTrait;

    public function __construct(
        public string $text,
    ){
    }

    public function render(): string
    {
        return htmlspecialchars($this->text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
    }
}