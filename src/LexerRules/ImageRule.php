<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Exceptions\ImageSourceWasMissing;
use Tempest\Markdown\Exceptions\ImageSourceWasNotClosed;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ImageToken;

final class ImageRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '!';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('![', 2);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeIncluding('![');
        $alt = $parser->consumeUntil(']') ?: null;
        $parser->consumeIncluding(']');

        if (! $parser->comesNext('(', 1)) {
            throw new ImageSourceWasMissing($parser);
        }

        $parser->consumeIncluding('(');
        $href = $parser->consumeUntil(')' . Parser::NEW_LINE);

        if (! $parser->comesNext(')')) {
            throw new ImageSourceWasNotClosed($parser);
        }

        $parser->consumeIncluding(')');

        return new ImageToken($href, $alt);
    }
}
