<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\ImageToken;
use Tempest\ResponsiveImage\ResponsiveImageConfig;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

class ImageTokenTest extends ParserTestCase
{
    #[Before, After]
    public function cleanPublicDir(): void
    {
        $files = glob(__DIR__ . '/Fixtures/public/*.jpg');

        if (! $files) {
            return;
        }

        foreach ($files as $file) {
            unlink($file);
        }
    }

    #[Test]
    public function test_parse_with_alt(): void
    {
        $token = new ImageToken('https://example.com/img.png', 'a cat');

        $this->assertEquals('<img src="https://example.com/img.png" alt="a cat">', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_without_alt(): void
    {
        $token = new ImageToken('https://example.com/img.png', null);

        $this->assertEquals('<img src="https://example.com/img.png">', $token->parse(new Parser()));
    }

    #[Test]
    public function test_with_responsive_image(): void
    {
        $config = new ResponsiveImageConfig(
            srcPath: __DIR__ . '/../Fixtures/src',
            publicPath: __DIR__ . '/../Fixtures/public',
        );

        $parser = new Parser(
            imageFactory: new ResponsiveImageFactory(
                $config,
            ),
        );

        $parsed = $parser->parse('![A parrot](/parrot.jpg)');

        $this->assertSame(<<<'HTML'
        <p><img src="/parrot.jpg" alt="A parrot" srcset="/parrot-1920-1280.jpg 1920w, /parrot-1606-1070.jpg 1606w, /parrot-1214-809.jpg 1214w, /parrot-607-404.jpg 607w"></p>
        HTML, $parsed->html);

        $this->assertFileExists($config->makePublicPath('/parrot.jpg'));
        $this->assertFileExists($config->makePublicPath('/parrot-1920-1280.jpg'));
        $this->assertFileExists($config->makePublicPath('/parrot-1606-1070.jpg'));
        $this->assertFileExists($config->makePublicPath('/parrot-1214-809.jpg'));
        $this->assertFileExists($config->makePublicPath('/parrot-607-404.jpg'));
    }
}
