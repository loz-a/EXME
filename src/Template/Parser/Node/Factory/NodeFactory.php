<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Factory;

use EXME\Template\Lexer\Contract\TokenStreamInterface;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType as Type;
use EXME\Template\Parser\Node\Component;
use EXME\Template\Parser\Node\Contract\NodeFactoryInterface;
use EXME\Template\Parser\Node\Contract\NodeInterface;
use EXME\Template\Parser\Node\Fragment;
use EXME\Template\Parser\Node\Html;
use EXME\Template\Parser\Node\Php;
use EXME\Template\Parser\Node\Text;
use RuntimeException;

use function sprintf;

final class NodeFactory implements NodeFactoryInterface
{
    public function create(TokenStreamInterface $tokens): NodeInterface
    {
        // $nodes = $this->parseNodes($tokens);

        $nodes = [];
        while (!$tokens->isEmpty()) {
            $nodes[] = $this->parseNode($tokens);
        }

        return count($nodes) === 1 ? $nodes[0] : new Fragment(...$nodes);
    }

    /**
     * Parse a sequence of nodes until the end of the stream
     * or until a component closing tag is encountered.
     *
     * The closing tag is intentionally NOT consumed here.
     *
     * @return array<int, NodeInterface>
     */
    private function parseChildren(TokenStreamInterface $tokens): array
    {
        $nodes = [];

        while (!$tokens->isEmpty()) {
            $token = $tokens->peek();

            if ($token->type === Type::COMPONENT_CLOSING_TAG) {
                break;
            }

            $nodes[] = $this->parseNode($tokens);
        }

        return $nodes;
    }

    private function parseNode(TokenStreamInterface $tokens): NodeInterface
    {
        $token = $tokens->peek();

        if (null === $token) {
            throw new RuntimeException('Unexpected end of token stream.');
        }

        return match ($token->type) {
            Type::COMPONENT_OPEN => $this->parseComponent($tokens),

            Type::TEXT => $this->createText($tokens),

            Type::HTML => $this->createHtml($tokens),

            Type::PHP => $this->createPhp($tokens),

            Type::COMPONENT_CLOSING_TAG => throw new RuntimeException(
                sprintf('Unexpected closing component </%s> at position %d.', 
                    $token->text, $token->position)),

            default => throw new RuntimeException(
                sprintf('Unexpected token %s at position %d.', $token->type->value, $token->position)),
        };
    }

    private function parseComponent(TokenStreamInterface $tokens): Component
    {
        $this->expect($tokens, Type::COMPONENT_OPEN);

        $nameToken = $this->expect($tokens, Type::COMPONENT_NAME);
        $attributes = $this->parseAttributes($tokens);
        $closingToken = $tokens->peek();

        if ($closingToken === null) {
            throw new RuntimeException(
                sprintf('Unexpected end of template. Expected closing tag for <%s>.', $nameToken->text));
        }

        if ($closingToken->type === Type::COMPONENT_SELF_CLOSE) {
            $tokens->dequeue();

            return new Component(
                name: trim($nameToken->text),
                attributes: $attributes,
            );
        }

        $this->expect($tokens, Type::COMPONENT_CLOSE);

        /*
         * Recursive descent.
         *
         * At this point we have:
         *
         * <User>
         *
         * parseNodes() will parse everything until:
         *
         * </User>
         */
        $children = $this->parseChildren($tokens);
        
        $componentName = trim($nameToken->text);
        $closingTag = $this->expect($tokens, Type::COMPONENT_CLOSING_TAG, $componentName);

        $closingName = trim($closingTag->text);

        if ($componentName !== $closingName) {
            throw new RuntimeException(
                sprintf('Expected closing component </%s>, got </%s> at position %d.',
                    $componentName,
                    $closingName,
                    $closingTag->position,
                ),
            );
        }

        return new Component(
            name: $componentName,
            attributes: $attributes,
            slot: new Fragment(...$children),
        );
    }

    /**
     * @return array<string, Token>
     */
    private function parseAttributes(TokenStreamInterface $tokens): array 
    {
        $attributes = [];

        while (!$tokens->isEmpty()) {
            $token = $tokens->peek();

            if ($token->type === Type::COMPONENT_CLOSE
                || $token->type === Type::COMPONENT_SELF_CLOSE
            ) {
                break;
            }

            $attributeToken = $this->expect($tokens, Type::IDENTIFIER);
            $attributeName = trim($attributeToken->text);
            $isDuplicationDetected = array_key_exists($attributeName, $attributes);

            if ($isDuplicationDetected) {
                throw new RuntimeException(
                    sprintf('Duplicate component attribute "%s" at position %d.',
                        $attributeName,
                        $attributeToken->position,
                    ),
                );
            }

            $this->expect($tokens, Type::EQUALS);

            $valueToken = $tokens->peek();

            if ($valueToken === null) {
                throw new RuntimeException(
                    sprintf('Expected value for component attribute "%s".', $attributeName));
            }

            $invalidValueType = $valueToken->type !== Type::TEXT && $valueToken->type !== Type::PHP;

            if ($invalidValueType) {
                throw new RuntimeException(
                    sprintf('Expected attribute value for "%s", got %s at position %d.',
                        $attributeName,
                        $valueToken->type->value,
                        $valueToken->position,
                    ),
                );
            }

            $attributes[$attributeName] = $tokens->dequeue()->text;
        }

        return $attributes;
    }

    private function createText(TokenStreamInterface $tokens): Text 
    {
        $token = $this->expect($tokens, Type::TEXT);
        return new Text($token->text);
    }

    private function createHtml(TokenStreamInterface $tokens): Html
    {
        $token = $this->expect($tokens, Type::HTML);
        return new Html($token->text);
    }

    private function createPhp(TokenStreamInterface $tokens): Php
    {
        $token = $this->expect($tokens, Type::PHP);
        return new Php($token->text);
    }

    private function expect(TokenStreamInterface $tokens, Type $expected, ?string $context = null): Token
    {
        if ($tokens->isEmpty()) {            
            if ($expected === Type::COMPONENT_CLOSING_TAG
                && null !== $context
            ) {
                $message = sprintf('Expected closing tag for <%s>, but reached end of template.', ucfirst($context));
            }

            throw new RuntimeException(
                $message ?? sprintf('Expected token %s, but reached end of template.', $expected->name));
        }

        $token = $tokens->dequeue();

        if ($token->type !== $expected) {
            if ($expected === Type::COMPONENT_CLOSING_TAG
                && null !== $context
            ) {
                $message = sprintf('Expected closing tag for <%s>, got %s at position %d.', 
                    ucfirst($context), 
                    $token->type->name, 
                    $token->position
                );
            }

            throw new RuntimeException(
                $message ?? sprintf('Expected token %s, got %s at position %d.',
                    $expected->name,
                    $token->type->name,
                    $token->position,
                ),
            );
        }

        return $token;
    }
}