<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class ComponentNameTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return $context->mode === LexerMode::COMPONENT
            && $this->isComponentNameStart($context);
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;
        $start = $position;

        while (!$context->isAtEnd()) {
            $current = $context->current();
            $shouldBreak = ctype_space($current) || $current === '/' || $current === '>'; 

            if ($shouldBreak) {
                break;
            }
            
            $context->moveNext();
        }

        return new Token(
            type: TokenType::COMPONENT_NAME,
            text: substr(
                $context->source,
                $start,
                $context->position - $start,
            ),
            position: $position,
        );
    }

    private function isComponentNameStart(LexerContext $context): bool
    {
        return ctype_upper($context->current()) && $context->peek(-1) === '<';
    }
}