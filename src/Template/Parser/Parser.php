<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;

final class Parser
{
    /**
     * @var list<Token>
     */
    private readonly array $tokens;

    private int $position = 0;

    /**
     * @param list<Token> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function parse(): ComponentNode
    {
        // $this->position = 0;

        // $this->expect(TokenType::TAG_OPEN);

        // $name = $this->expect(TokenType::IDENTIFIER)->value;
        // $classDefinition = $this->runtimeDefinition->getClassDefinition($name);

        // $attributes = [];

        // while (!$this->isAtEnd() && $this->current()->type !== TokenType::TAG_SELF_CLOSE) {
        //     $attribute = $this->expect(TokenType::IDENTIFIER);

        //     if (array_key_exists($attribute->value, $attributes)) {
        //         throw new \RuntimeException(
        //             sprintf(
        //                 'Duplicate attribute "%s" at position %d',
        //                 $attribute->value,
        //                 $attribute->position,
        //             ),
        //         );
        //     }

        //     $this->expect(TokenType::EQUALS);

        //     $attributeValue = $this->expect(TokenType::STRING)->value;

        //     $attributes[$attribute->value] = $attributeValue;
        // }

        // $this->expect(TokenType::TAG_SELF_CLOSE);

        // if (!$this->isAtEnd()) {
        //     throw new \RuntimeException(
        //         sprintf(
        //             'Unexpected token "%s" at position %d',
        //             $this->current()->value,
        //             $this->current()->position,
        //         ),
        //     );
        // }

        // return new ComponentNode(
        //     name: $name,
        //     attributes: $attributes,
        // );
    }

    private function expect(TokenType $type): Token
    {
        if ($this->isAtEnd()) {
            throw new \RuntimeException(
                sprintf(
                    'Expected token "%s", but reached end of input',
                    $type->value,
                ),
            );
        }

        $token = $this->current();

        if ($token->type !== $type) {
            throw new \RuntimeException(
                sprintf(
                    'Expected token "%s", got "%s" at position %d',
                    $type->value,
                    $token->type->value,
                    $token->position,
                ),
            );
        }

        $this->position++;

        return $token;
    }

    private function current(): Token
    {
        return $this->tokens[$this->position];
    }

    private function isAtEnd(): bool
    {
        return $this->position >= count($this->tokens);
    }
}
