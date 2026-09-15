<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Exceptions\SocialHandlePlatformWasUnknown;
use Tempest\Markdown\Exceptions\SocialHandleWasInvalid;
use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\RuleContext;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\LinkToken;

/**
 * Adapted from the original handle parser by innocenzi.
 * https://github.com/tempestphp/tempestphp.com/blob/48b58184b43e80ab17f4375ac95affdf6d7a3bf2/src/Markdown/HandleParser.php
 */
final class SocialHandleRule implements
    Rule,
    ProvidesFirstChar,
    ProvidesStopChar
{
    use IsRule;

    private(set) string $firstChar = '{';
    private(set) string $stopChar = '{';

    public function __construct()
    {
        $this->contexts = [RuleContext::INLINE];

        $this->removeTokenSupport(LinkToken::class);
    }

    public function shouldParse(Parser $parser): bool
    {
        return (
            (
                $parser->comesNext('{x:', caseSensitive: false)
                || $parser->comesNext('{gh:', caseSensitive: false)
                || $parser->comesNext('{bsky:', caseSensitive: false)
                || $parser->comesNext('{github:', caseSensitive: false)
                || $parser->comesNext('{twitter:', caseSensitive: false)
                || $parser->comesNext('{bluesky:', caseSensitive: false)
            )
            && $parser->hasNext('}', "\r\n")
        );
    }

    public function parse(Parser $parser): Token
    {
        // Consume the opening bracket ("{").
        $parser->consume();

        // Consume until the ending bracket ("}").
        $content = $parser->consumeUntilString('}');

        // Consume the closing bracket ("}").
        $parser->consume();

        $matches = [];

        // Match the format: github:aidan-casey,Label
        // Where "Label" is optional.
        // This is the slow way to do it, but @brendt and @xHeaven made me do it.
        $matched = preg_match(
            pattern: '/\A(?<platform>twitter|x|bluesky|bsky|github|gh):(?<handle>[^,\r\n]+)(?:,(?<content>[^\r\n]*\S[^\r\n]*))?\z/i',
            subject: $content,
            matches: $matches,
            flags: PREG_UNMATCHED_AS_NULL,
        );

        if ($matched !== 1) {
            throw new SocialHandleWasInvalid($parser);
        }

        $platform = strtolower($matches['platform'] ?? '');
        $handle = $matches['handle'];
        $content = $matches['content'] ?? '@' . $handle;

        return new LinkToken(
            content: $content,
            href: $this->createSocialUrl($parser, $platform, $handle),
            parseContent: false,
        );
    }

    private function createSocialUrl(
        Parser $parser,
        string $platform,
        ?string $handle,
    ): string {
        return match ($platform) {
            'bluesky', 'bsky' => "https://bsky.app/profile/{$handle}",
            'gh', 'github' => "https://github.com/{$handle}",
            'x', 'twitter' => "https://x.com/{$handle}",
            // In theory, we should never reach here given the parsing rules.
            // But it doesn't hurt anyone either. We may use this later if we allow extensions.
            default => throw new SocialHandlePlatformWasUnknown(
                $parser,
                $platform,
            ),
        };
    }
}
