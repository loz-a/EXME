<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use function strlen;

/**
 * A single lexical token produced by the Lexer.
 *
 * Lexical contract:
 *
 * - type
 *   Identifies the lexical category of the token.
 *
 * - text
 *   Contains the exact source text consumed by the token.
 *   The Lexer must not normalize, trim or otherwise modify it.
 *
 * - position
 *   Zero-based absolute position in the original template source
 *   where the token starts.
 *
 * - canTokenize
 *   Internal Lexer control flag indicating that the token's text
 *   must be processed recursively by another Lexer pass.
 *
 * The Parser must rely only on the lexical information exposed by
 * type, text and position. `canTokenize` is an implementation detail
 * of the Lexer.
 */

final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string $text,
        public int $position,
        public bool $canTokenize = false,
    ) {
    }

    public function isEmpty(): bool
    {
        return strlen($this->text) === 0;
    }
}