<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Attribute;

use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Parser\Node\Attribute\Contract\AttributeFactoryInterface;
use EXME\Template\Parser\Node\Attribute\Contract\AttributeInterface;
use EXME\Template\Parser\Node\Attribute\Contract\AttributeValueInterface;
use EXME\Template\Parser\Node\Attribute\Value\Composite;
use EXME\Template\Parser\Node\Attribute\Value\PhpExpression as PhpExpressionValue;
use EXME\Template\Parser\Node\Attribute\Value\Text as TextValue;
use EXME\Template\Parser\Node\Attribute\Value\Numeric as NumValue;
use InvalidArgumentException;

final class AttributeFactory implements AttributeFactoryInterface
{
    public function create(string $name, Token ...$tokenValues): AttributeInterface
    {
        if (empty($tokenValues)) {
            throw new InvalidArgumentException('Attribute value must not be empty.');
        }

        if (count($tokenValues) === 1) {
            $attrValue = $this->createAttributeValue($tokenValues[0]);        
        }
        else {
            $acc = [];
            foreach ($tokenValues as $token) {
                $acc[] = $this->createAttributeValue($token);
            }

            $attrValue = new Composite(...$acc);
        }
        
        return new Attribute($name, $attrValue);
    }

    private function createAttributeValue(Token $token): AttributeValueInterface
    {
        return match ($token->type) {
            TokenType::TEXT => new TextValue($token->text),
            TokenType::PHP => new PhpExpressionValue($token->text),
            TokenType::NUM => new NumValue($token->text),
        };
    }
}