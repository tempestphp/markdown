<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class LinkTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function link_with_href(): void
    {
        $this->assertSame(
            '<p><a href="#">click here</a></p>',
            $this->parser->parse('[click here](#)')->html,
        );
    }

    #[Test]
    public function link_without_href(): void
    {
        $this->assertSame(
            '<p><a href="">click here</a></p>',
            $this->parser->parse('[click here]')->html,
        );
    }

    #[Test]
    public function link_containing_bold(): void
    {
        $this->assertSame(
            '<p><a href="#">click <strong>here</strong></a></p>',
            $this->parser->parse('[click **here**](#)')->html,
        );
    }

    #[Test]
    public function link_containing_italic(): void
    {
        $this->assertSame(
            '<p><a href="#">click <em>here</em></a></p>',
            $this->parser->parse('[click __here__](#)')->html,
        );
    }

    #[Test]
    public function link_containing_strikethrough(): void
    {
        $this->assertSame(
            '<p><a href="#">click <s>here</s></a></p>',
            $this->parser->parse('[click ~~here~~](#)')->html,
        );
    }

    #[Test]
    public function asterisk_prefix_opens_new_tab(): void
    {
        $this->assertSame(
            '<p><a href="https://tempestphp.com" target="_blank" rel="noopener noreferrer">click here</a></p>',
            $this->parser->parse('[click here](*https://tempestphp.com)')->html,
        );
    }
}
