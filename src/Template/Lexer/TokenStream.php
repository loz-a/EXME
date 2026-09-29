<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Contract\TokenStreamInterface;
use EXME\Template\Lexer\Token;
use Override;
use SplQueue;

use function iterator_to_array;

final class TokenStream implements TokenStreamInterface
{
    private SplQueue $queue;

    public function __construct(Token ...$tokens)
    {
        $this->queue = new SplQueue();
        $this->enqueue($tokens);
    }
    
    private function enqueue(array $tokens) 
    {
        foreach ($tokens as $token) {
            $this->queue->enqueue($token);
        }
    }

    #[Override]
    public function peek(): ?Token
    {
        return $this->queue->isEmpty() ? null : $this->queue->offsetGet(0);
    }


    #[Override]
    public function dequeue(): Token
    {
        return $this->queue->dequeue();
    }
   

    #[Override]
    public function count(): int
    {
        return $this->queue->count();
    }

    #[Override]
    public function toArray(): array
    {
        return iterator_to_array($this->queue);
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }
}