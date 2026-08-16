<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

enum TokenType: string
{
    case TAG_OPEN = 'tag_open';
    case TAG_SELF_CLOSE = 'tag_self_close';

    case IDENTIFIER = 'identifier';
    case EQUALS = 'equals';
    case STRING = 'string';
}