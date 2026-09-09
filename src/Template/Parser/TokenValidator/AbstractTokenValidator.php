<?php

declare(strict_types=1);

namespace EXME\Template\Parser\TokenValidator;

use EXME\Template\Lexer\TokenType;
use EXME\Template\Parser\Contract\ParserContextInterface;
use EXME\Template\Parser\Contract\TokenValidatorInterface;
use RuntimeException;

abstract class AbstractTokenValidator implements TokenValidatorInterface
{
    private ParserContextInterface $parserContext;

    public function setContext(ParserContextInterface $ctx): TokenValidatorInterface
    {
        $this->parserContext = $ctx;
        return $this;
    }
    
    abstract public function validate(): void;

    protected function getContext(): ParserContextInterface
    {
        if (!$this->parserContext) {
            throw new RuntimeException('No context to Validate');
        }

        return $this->parserContext;
    }

    protected function expect(TokenType|array $type): void
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                $this->expect($t);
            }
            return;
        }

        $currentToken = $this->getContext()->current();

        if ($currentToken->type !== $type) {
            throw new \RuntimeException(
                sprintf(
                    'Expected token "%s", got "%s" at position %d',
                    $type->value,
                    $currentToken->type->value,
                    $currentToken->position,
                ),
            );
        }
    }
}