<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Exceptions\ImageSourceWasMissing;
use Tempest\Markdown\Exceptions\ImageSourceWasNotClosed;
use Tempest\Markdown\InlineDestination;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ImageToken;
use Tempest\Markdown\Tokens\TextToken;

final class ImageRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    private(set) string $firstChar = '!';
    private(set) string $stopChar = '!';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('![', 2);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeIncluding('![');
        $alt = $parser->consumeUntil(']');
        $parser->consumeIncluding(']');

        if (! $parser->comesNext('(', 1)) {
            throw new ImageSourceWasMissing($parser);
        }

        $destination = InlineDestination::scan(
            $parser->content,
            $parser->position,
        );

        if ($destination === null) {
            if (! $this->closesOnThisLine($parser)) {
                throw new ImageSourceWasNotClosed($parser);
            }

            // A malformed source is not an image: the label and everything
            // after it stay literal text.
            return new TextToken('![' . $alt . ']');
        }

        $parser->consume($destination->length);

        return new ImageToken(
            $destination->destination,
            $alt ?: null,
            $destination->title,
        );
    }

    private function closesOnThisLine(Parser $parser): bool
    {
        $offset = strcspn(
            $parser->content,
            ')' . Parser::NEW_LINE,
            $parser->position,
        );

        return ($parser->content[$parser->position + $offset] ?? null) === ')';
    }
}
