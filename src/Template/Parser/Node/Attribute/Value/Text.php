<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Attribute\Value;

use EXME\Template\Parser\Node\Attribute\Contract\AttributeValueInterface;

final class Text implements AttributeValueInterface
{
    public function __construct(
        public readonly string $text,
    ) {}

    public function __toString(): string
    {
        $text = htmlspecialchars($this->text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
        return var_export($text, true);
    }
}