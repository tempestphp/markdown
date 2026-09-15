<?php

namespace Rules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Exceptions\SocialHandleWasInvalid;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Rules\SocialHandleRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

final class SocialHandleRuleTest extends ParserTestCase
{
    #[Test]
    #[DataProvider('provideSocialData')]
    public function lex($content, $expectedResult): void
    {
        $content = (string) new Parser(highlighter: null)
            // We test with the paragraph rule here for now,
            // because there will be some oddities with test
            // output if we do not.
            ->prependRules(new ParagraphRule())
            ->parse($content);

        $this->assertSame($expectedResult, $content);
    }

    #[Test]
    public function social_handle_consumes_closing_brace_with_text_fallback(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new SocialHandleRule(),
            new TextRule(),
        ]);

        $html = $parser->parse('Hello {gh:alice}!')->html;

        $this->assertSame(
            'Hello <a href="https://github.com/alice">@alice</a>!',
            $html,
        );
    }

    #[Test]
    public function prepended_social_handle_rule_parses_handles_in_paragraphs(): void
    {
        $parser = new Parser(highlighter: null)->prependRules(
            new SocialHandleRule(),
        );

        $html = $parser->parse('Thoughts of {x:brendt_gd}.')->html;

        $this->assertSame(
            '<p>Thoughts of <a href="https://x.com/brendt_gd">@brendt_gd</a>.</p>',
            $html,
        );
    }

    #[Test]
    public function unclosed_social_handle_preserves_remaining_text(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new SocialHandleRule(),
            new TextRule(),
        ]);
        $content = 'Hello {gh:alice,Read this sentence.';

        $this->assertSame($content, $parser->parse($content)->html);
    }

    #[Test]
    public function social_handle_preserves_underscores_in_default_label(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new SocialHandleRule(),
            new TextRule(),
        ]);

        $html = $parser->parse('{x:my_test_account}')->html;

        $this->assertSame(
            '<a href="https://x.com/my_test_account">@my_test_account</a>',
            $html,
        );
    }

    #[Test]
    public function invalid_social_handle_throws_markdown_exception(): void
    {
        $parser = new Parser(highlighter: null)->prependRules(
            new SocialHandleRule(),
        );

        $this->expectException(SocialHandleWasInvalid::class);
        $this->expectExceptionMessage(
            "The provided social handle was invalid:\n\n01 > {gh:}\n",
        );

        $parser->parse('{gh:}');
    }

    #[Test]
    public function empty_handle_throws_markdown_exception(): void
    {
        $parser = new Parser(highlighter: null)->prependRules(
            new SocialHandleRule(),
        );

        $this->expectException(SocialHandleWasInvalid::class);
        $this->expectExceptionMessage(
            "The provided social handle was invalid:\n\n01 > {gh:,Testing}\n",
        );

        $parser->parse('{gh:,Testing}');
    }

    #[Test]
    public function empty_label_throws_markdown_exception(): void
    {
        $parser = new Parser(highlighter: null)->prependRules(
            new SocialHandleRule(),
        );

        $this->expectException(SocialHandleWasInvalid::class);
        $this->expectExceptionMessage(
            "The provided social handle was invalid:\n\n01 > {gh:aidan-casey,}\n",
        );

        $parser->parse('{gh:aidan-casey,}');
    }

    #[Test]
    public function whitespace_label_throws_markdown_exception(): void
    {
        $parser = new Parser(highlighter: null)->prependRules(
            new SocialHandleRule(),
        );

        $this->expectException(SocialHandleWasInvalid::class);
        $this->expectExceptionMessage(
            "The provided social handle was invalid:\n\n01 > {gh:aidan-casey,  }\n",
        );

        $parser->parse('{gh:aidan-casey,  }');
    }

    #[Test]
    public function social_handles_cannot_span_lines(): void
    {
        $parser = new Parser(highlighter: null)->prependRules(
            new SocialHandleRule(),
        );

        $content = (string) $parser->parse("{gh:\r\naidan-casey}");

        $this->assertSame("<p>{gh:\r\naidan-casey}</p>", $content);
    }

    public static function provideSocialData(): array
    {
        return [
            'github username' => [
                '{github:aidan-casey}',
                '<p><a href="https://github.com/aidan-casey">@aidan-casey</a></p>',
            ],

            'github username with a label' => [
                '{github:aidan-casey,Testing}',
                '<p><a href="https://github.com/aidan-casey">Testing</a></p>',
            ],

            'github username with shorthand' => [
                '{gh:aidan-casey}',
                '<p><a href="https://github.com/aidan-casey">@aidan-casey</a></p>',
            ],

            'uppercase github username' => [
                '{GITHUB:aidan-casey}',
                '<p><a href="https://github.com/aidan-casey">@aidan-casey</a></p>',
            ],

            'twitter username' => [
                '{twitter:JohnDoe}',
                '<p><a href="https://x.com/JohnDoe">@JohnDoe</a></p>',
            ],

            'twitter username with shorthand' => [
                '{x:JohnDoe,Hello John Doe}',
                '<p><a href="https://x.com/JohnDoe">Hello John Doe</a></p>',
            ],

            'bluesky username' => [
                '{bluesky:JohnDoe}',
                '<p><a href="https://bsky.app/profile/JohnDoe">@JohnDoe</a></p>',
            ],

            'bluesky username with shorthand' => [
                '{bsky:JohnDoe,Testing}',
                '<p><a href="https://bsky.app/profile/JohnDoe">Testing</a></p>',
            ],

            'username with whitespace' => [
                '{gh:aidan casey}',
                '<p><a href="https://github.com/aidan casey">@aidan casey</a></p>',
            ],

            'label with a comma' => [
                '{gh:aidan-casey,Tempest, Core Developer}',
                '<p><a href="https://github.com/aidan-casey">Tempest, Core Developer</a></p>',
            ],

            'invalid platform remains plaintext' => [
                '{gitlab:aidan-casey}',
                '<p>{gitlab:aidan-casey}</p>',
            ],

            'label with markdown' => [
                '{gh:aidan-casey,**Testing**}',
                '<p><a href="https://github.com/aidan-casey">**Testing**</a></p>',
            ],

            'multiple handles' => [
                '{gh:aidan-casey} and {gh:brendt,BDFL Himself}',
                '<p><a href="https://github.com/aidan-casey">@aidan-casey</a> and <a href="https://github.com/brendt">BDFL Himself</a></p>',
            ],
        ];
    }
}
