<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\IsRule;
use Tempest\Markdown\Markdown;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\RuleContext;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HeadingToken;
use Tempest\Markdown\Tokens\LinkToken;

/**
 * The parser is the single place rules are registered. Nested content is
 * parsed with the rules that are registered there and support the token,
 * never with a list rebuilt by the token.
 */
final class RuleRegistryTest extends ParserTestCase
{
    #[Test]
    public function a_removed_rule_does_not_reach_nested_content(): void
    {
        $markdown = new Markdown(highlighter: null)->removeRules(
            StrikethroughRule::class,
        );

        $this->assertSame(
            '<p>~~struck~~</p>',
            $markdown->parse('~~struck~~')->html,
        );
        $this->assertSame(
            '<ul><li>~~struck~~</li></ul>',
            $markdown->parse('- ~~struck~~')->html,
        );
        $this->assertSame(
            '<p><strong>~~struck~~</strong></p>',
            $markdown->parse('**~~struck~~**')->html,
        );
    }

    #[Test]
    public function an_added_inline_rule_reaches_nested_content(): void
    {
        $markdown = new Markdown(highlighter: null)->prependRules(
            new HighlightRule(),
        );

        $this->assertSame(
            '<p>look <mark>here</mark> now</p>',
            $markdown->parse('look ==here== now')->html,
        );
        $this->assertSame(
            '<ul><li><mark>here</mark></li></ul>',
            $markdown->parse('- ==here==')->html,
        );
        $this->assertSame(
            '<blockquote><mark>here</mark></blockquote>',
            $markdown->parse('> ==here==')->html,
        );
        $this->assertSame(
            '<p><strong><mark>here</mark></strong></p>',
            $markdown->parse('**==here==**')->html,
        );
    }

    #[Test]
    public function a_custom_token_gets_the_registered_inline_rules(): void
    {
        $markdown = new Markdown(highlighter: null)->prependRules(
            new AsideRule(),
        );

        $this->assertSame(
            '<aside><strong>bold</strong> and <s>struck</s></aside>',
            $markdown->parse('!! **bold** and ~~struck~~')->html,
        );
    }

    #[Test]
    public function token_support_can_be_added_on_a_registered_rule(): void
    {
        $markdown = new Markdown(highlighter: null);

        $this->assertSame(
            '<p><a href="/b">a [c](/d) e</a></p>',
            $markdown->parse('[a [c](/d) e](/b)')->html,
        );

        $markdown->getRule(LinkRule::class)?->addTokenSupport(LinkToken::class);

        $this->assertSame(
            '<p><a href="/b">a <a href="/d">c</a> e</a></p>',
            $markdown->parse('[a [c](/d) e](/b)')->html,
        );
    }

    #[Test]
    public function token_support_can_be_removed_on_a_registered_rule(): void
    {
        $markdown = new Markdown(highlighter: null);

        $markdown
            ->getRule(ImageRule::class)
            ?->removeTokenSupport(
                HeadingToken::class,
            );

        $this->assertSame(
            '<p><img src="i.png" alt="a"></p>',
            $markdown->parse('![a](i.png)')->html,
        );
        $this->assertSame(
            '<h1 id="a-i-png">!<a href="i.png">a</a></h1>',
            $markdown->parse('# ![a](i.png)')->html,
        );
    }

    #[Test]
    public function nested_rule_sets_keep_their_own_stop_chars(): void
    {
        $markdown = new Markdown(highlighter: null);

        // The second call reuses the sub-parser cached by the first one.
        $this->assertSame(
            '<p><strong>bold</strong></p>',
            $markdown->parse('**bold**')->html,
        );
        $this->assertSame(
            '<p><strong>bold</strong></p>',
            $markdown->parse('**bold**')->html,
        );
    }
}

final class HighlightRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    use IsRule;

    private(set) string $firstChar = '=';
    private(set) string $stopChar = '=';

    public function __construct()
    {
        $this->contexts = [RuleContext::INLINE];
    }

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('==', 2) && $parser->hasNext('==', "\n");
    }

    public function parse(Parser $parser): Token
    {
        $parser->consume(2);

        $content = $parser->consumeUntilString('==');

        $parser->consume(2);

        return new HighlightToken($content);
    }
}

final class HighlightToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        return "<mark>{$this->content}</mark>";
    }
}

final class AsideRule implements Rule, ProvidesFirstChar
{
    use IsRule;

    private(set) string $firstChar = '!';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('!! ', 3);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consume(3);

        return new AsideToken($parser->consumeUntil(Parser::NEW_LINE));
    }
}

final class AsideToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser->forToken($this)->parse($this->content);

        return "<aside>{$content}</aside>";
    }
}
