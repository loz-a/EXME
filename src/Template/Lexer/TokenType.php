<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

/**
 * Lexical token types produced by the EXME Lexer.
 *
 * The Lexer is responsible only for recognizing lexical elements.
 * It does not determine the semantic structure of a template.
 *
 * Component tokens:
 * - COMPONENT_OPEN
 * - COMPONENT_CLOSE
 * - COMPONENT_SELF_CLOSE
 * - COMPONENT_NAME
 *
 * Component syntax tokens:
 * - IDENTIFIER
 * - EQUALS
 *
 * Content tokens:
 * - TEXT
 * - HTML
 *
 * PHP tokens:
 * - PHP
 */

enum TokenType
{
    case COMPONENT_OPEN;
    case COMPONENT_CLOSE;
    case COMPONENT_SELF_CLOSE;
    case COMPONENT_NAME;
    case COMPONENT_CLOSING_TAG;
    
    case IDENTIFIER;
    case EQUALS;
    case TEXT;
    case NUM;
    case BOOL;
    
    case HTML;
    case PHP;
}