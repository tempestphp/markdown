<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\Tokens\ImageToken;

class ImageRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new ImageRule()])->lex('![alt](href)')[0];

        $this->assertEquals(new ImageToken('href', 'alt'), $token);
    }

    #[Test]
    public function test_lex_without_alt(): void
    {
        $token = new Lexer([new ImageRule()])->lex('![](href)')[0];

        $this->assertEquals(new ImageToken('href', null), $token);
    }
}
