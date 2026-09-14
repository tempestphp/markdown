<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\InlineDestination;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\LinkToken;
use Tempest\Markdown\Tokens\TextToken;

final class LinkRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    private(set) string $firstChar = '[';
    private(set) string $stopChar = '[';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('[', 1);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeIncluding('[');
        $content = $this->consumeContent($parser);
        $parser->consumeIncluding(']');

        if (! $parser->comesNext('(', 1)) {
            return new LinkToken($content, null);
        }

        $destination = InlineDestination::scan(
            $parser->content,
            $parser->position,
        );

        // A malformed destination is not a link: the label and everything
        // after it stay literal text.
        if ($destination === null) {
            return new TextToken('[' . $content . ']');
        }

        $parser->consume($destination->length);

        return new LinkToken(
            $content,
            $destination->destination,
            $destination->title,
        );
    }

    private function consumeContent(Parser $parser): string
    {
        $content = '';
        $bracketDepth = 0;

        while ($parser->current !== null) {
            if ($parser->comesNext(']') && $bracketDepth === 0) {
                break;
            }

            if ($parser->comesNext('[')) {
                $bracketDepth += 1;
            } elseif ($parser->comesNext(']')) {
                $bracketDepth -= 1;
            }

            $content .= $parser->consume();
        }

        return $content;
    }
}
