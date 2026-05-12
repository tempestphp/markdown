<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\Tokens\LinkToken;

class LinkRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new LinkRule()])->lex('[click here](#)')[0];

        $this->assertEquals(new LinkToken('click here', '#'), $token);
    }

    #[Test]
    public function test_lex_without_href(): void
    {
        $token = new Lexer([new LinkRule()])->lex('[click here]')[0];

        $this->assertEquals(new LinkToken('click here', null), $token);
    }
}
