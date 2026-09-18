<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Factory;

use EXME\Template\Lexer\Contract\TokenCollectionInterface;
use EXME\Template\Lexer\TokenType as Type;
use EXME\Template\Parser\Node\Component;
use EXME\Template\Parser\Node\Contract\NodeFactoryInterface;
use EXME\Template\Parser\Node\Contract\NodeInterface;
use EXME\Template\Parser\Node\Fragment;
use EXME\Template\Parser\Node\Raw;
use EXME\Template\Parser\Node\Text;
use RuntimeException;

use function sprintf;

final class NodeFactory implements NodeFactoryInterface
{
    public function create(TokenCollectionInterface $tokens): NodeInterface
    {        
        $result = [];

        while (!$tokens->isEmpty()) {
            $token = $tokens->dequeue();

            if ($token->type === Type::COMPONENT_OPEN) {                
                $result[] = $this->processComponent($tokens);
                continue;  
            }

            if ($token->type === Type::COMPONENT_CLOSING_TAG) {
                if ($tokens->isEmpty()) {
                    return new Fragment(...$result);
                }
            }

            if ($tokens->isEmpty()) {
                break;
            }

            if ($token->type === Type::TEXT) {
                $result[] = new Text($token->text);
                continue;
            }

            if ($token->type === Type::HTML || $token->type === Type::PHP) {
                $result[] = new Raw($token->text);
                continue;
            }
        }

        return new Fragment(...$result);

    }

    private function processComponent(TokenCollectionInterface $tokens): NodeInterface
    {
        $cParams = [];
        $tokenName = $tokens->dequeue();

        if ($tokenName->type !== Type::COMPONENT_NAME) {
            throw new RuntimeException(sprintf('Component name token expected but got %s', $tokenName->type));
        }

        $this->checkTokenExhaustion($tokens);

        $token = $tokens->dequeue();

        while ($token !== Type::COMPONENT_SELF_CLOSE
            || $token !== Type::COMPONENT_CLOSE
        ){

            if ($token->type === Type::IDENTIFIER) {
                $paramName = trim($token->text);
                
                $this->checkTokenExhaustion($tokens, 'Equals token expected but got end of template');

                $equalsToken = $tokens->dequeue();
                if ($equalsToken->type !== Type::EQUALS) {
                    throw new RuntimeException('Equals token expected. Invalid token received');
                }

                $this->checkTokenExhaustion($tokens, 'Parameter value token expected but got end of template');

                $valueToken = $tokens->dequeue();
                if ($valueToken->type !== Type::TEXT) {
                    throw new RuntimeException(sprintf('Parameter value token expected but got %s', Type::TEXT));
                }

                $cParams[$paramName] = $valueToken;    
            }

            $this->checkTokenExhaustion($tokens, 'Close Component token expected but got end of template');
            
            $token = $tokens->dequeue();
        }

        $cName = trim($tokenName->text);

        if ($token->type === Type::COMPONENT_SELF_CLOSE) {
            return new Component($cName, $cParams);
        }

        $slot = $this->create($tokens);

        return new Component($cName, $cParams, $slot);
    }

    private function checkTokenExhaustion(TokenCollectionInterface $tokens, ?string $customMessage = null): void
    {
        if ($tokens->isEmpty()) {
            $message = 'Unexpected token exhaustion.';
            
            if ($customMessage) {
                $message = sprintf('%s %s', $message, $customMessage);
            }
            
            throw new RuntimeException($message);
        }
    }
}