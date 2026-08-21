<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

enum LexerMode: string
{
    case TEMPLATE = 'template';
    case COMPONENT = 'component';
    case HTML = 'html';
}