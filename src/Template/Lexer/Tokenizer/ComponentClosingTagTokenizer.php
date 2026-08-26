<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

/** Tokenizes the closing tag of an uppercase EXME component. */
final class ComponentClosingTagTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        $componentNameFirstLetter = $context->peek(2);

        return $context->mode === LexerMode::TEMPLATE
            && $context->startsWith('</')
            && $componentNameFirstLetter !== null
            && ctype_upper($componentNameFirstLetter);
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;
        $context->moveNext(2);

        while (($current = $context->current()) !== null && $this->isIdentifierPart($current)) {
            $context->moveNext();
        }

        $context->skipWhitespace();

        if ($context->current() !== '>') {
            throw new \RuntimeException(
                sprintf('Expected ">" to close component at position %d', $context->position),
            );
        }

        $context->moveNext();

        return new Token(
            type: TokenType::COMPONENT_CLOSE,
            text: substr($context->source, $position, $context->position - $position),
            position: $position,
        );
    }

    private function isIdentifierPart(string $char): bool
    {
        return ctype_alnum($char) || $char === '_' || $char === '-';
    }
}
