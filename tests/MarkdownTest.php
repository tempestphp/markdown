<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Markdown;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Rules\HeadingRule;
use Tempest\Markdown\Token;

final class MarkdownTest extends ParserTestCase
{
    private Markdown $markdown;

    #[Before]
    public function setupMarkdown(): void
    {
        $this->markdown = new Markdown();
    }

    #[Test]
    public function test_parse(): void
    {
        $parsed = $this->markdown->parse('**Hello**');

        $this->assertSame('<p><strong>Hello</strong></p>', $parsed->html);
    }

    #[Test]
    public function test_prepend_rules_takes_priority_over_default_rules(): void
    {
        $customRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return $parser->comesNext('#');
            }

            public function parse(Parser $parser): Token
            {
                $content = $parser->consumeUntil(Parser::NEW_LINE);

                return new class($content) implements Token {
                    public function __construct(
                        private string $content,
                    ) {}

                    public function parse(Parser $parser): string
                    {
                        return "<custom>{$this->content}</custom>";
                    }
                };
            }
        };

        $parsed = $this->markdown->prependRules($customRule)->parse('# Hello');

        $this->assertSame('<custom># Hello</custom>', $parsed->html);
    }

    #[Test]
    public function test_prepend_rules_can_be_chained(): void
    {
        $customRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return $parser->comesNext('#');
            }

            public function parse(Parser $parser): Token
            {
                $content = $parser->consumeUntil(Parser::NEW_LINE);

                return new class($content) implements Token {
                    public function __construct(
                        private string $content,
                    ) {}

                    public function parse(Parser $parser): string
                    {
                        return "<custom>{$this->content}</custom>";
                    }
                };
            }
        };

        $neverMatchRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return false;
            }

            public function parse(Parser $parser): ?Token
            {
                return null;
            }
        };

        $parsed = $this->markdown
            ->prependRules($customRule)
            ->appendRules($neverMatchRule)
            ->parse('# Hello');

        $this->assertSame('<custom># Hello</custom>', $parsed->html);
    }

    #[Test]
    public function test_with_rules_replaces_all_default_rules(): void
    {
        $customRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return $parser->comesNext('#');
            }

            public function parse(Parser $parser): Token
            {
                $content = $parser->consumeUntil(Parser::NEW_LINE);

                return new class($content) implements Token {
                    public function __construct(
                        private string $content,
                    ) {}

                    public function parse(Parser $parser): string
                    {
                        return "<custom>{$this->content}</custom>";
                    }
                };
            }
        };

        $parsed = $this->markdown->withRules($customRule)->parse('# Hello');

        $this->assertSame('<custom># Hello</custom>', $parsed->html);
    }

    #[Test]
    public function test_with_rules_returns_same_instance(): void
    {
        $customRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return false;
            }

            public function parse(Parser $parser): ?Token
            {
                return null;
            }
        };

        $result = $this->markdown->withRules($customRule);

        $this->assertSame($this->markdown, $result);
    }

    #[Test]
    public function test_append_rules_matches_after_with_rules_removes_catch_all(): void
    {
        $headingRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return $parser->comesNext('#');
            }

            public function parse(Parser $parser): Token
            {
                $content = $parser->consumeUntil(Parser::NEW_LINE);

                return new class($content) implements Token {
                    public function __construct(
                        private string $content,
                    ) {}

                    public function parse(Parser $parser): string
                    {
                        return "<custom>{$this->content}</custom>";
                    }
                };
            }
        };

        $parsed = $this->markdown
            ->withRules() // remove all default rules including catch-all ParagraphRule
            ->appendRules($headingRule)
            ->parse('# Hello');

        $this->assertSame('<custom># Hello</custom>', $parsed->html);
    }

    #[Test]
    public function test_append_rules_loses_to_default_rules_when_both_match(): void
    {
        $customRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return $parser->comesNext('#');
            }

            public function parse(Parser $parser): Token
            {
                $content = $parser->consumeUntil(Parser::NEW_LINE);

                return new class($content) implements Token {
                    public function __construct(
                        private string $content,
                    ) {}

                    public function parse(Parser $parser): string
                    {
                        return "<custom>{$this->content}</custom>";
                    }
                };
            }
        };

        $parsed = $this->markdown->appendRules($customRule)->parse('# Hello');

        // HeadingRule (default) runs before the appended rule, so heading wins
        $this->assertSame('<h1 id="hello">Hello</h1>', $parsed->html);
    }

    #[Test]
    public function test_prepend_rules_returns_same_instance(): void
    {
        $customRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return false;
            }

            public function parse(Parser $parser): ?Token
            {
                return null;
            }
        };

        $result = $this->markdown->prependRules($customRule);

        $this->assertSame($this->markdown, $result);
    }

    #[Test]
    public function test_append_rules_returns_same_instance(): void
    {
        $customRule = new class implements Rule {
            public function shouldParse(Parser $parser): bool
            {
                return false;
            }

            public function parse(Parser $parser): ?Token
            {
                return null;
            }
        };

        $result = $this->markdown->appendRules($customRule);

        $this->assertSame($this->markdown, $result);
    }

    #[Test]
    public function test_remove_rules_removes_rule(): void
    {
        $parsed = $this->markdown
            ->removeRules(HeadingRule::class)
            ->parse('# Not a heading');

        $this->assertStringNotContainsString('<h1', $parsed->html);
    }

    #[Test]
    public function test_raw(): void
    {
        $parsed = (string) $this->markdown
            ->parse(<<<'MD'
            ## Hello

            @@
            ### World
            @@

            _hi_
            MD);

        $this->assertSame(<<<HTML
        <h2 id="hello">Hello</h2>


        ### World


        <p><em>hi</em></p>
        HTML, $parsed);
    }
}
