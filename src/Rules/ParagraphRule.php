<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HeadingToken;
use Tempest\Markdown\Tokens\ParagraphToken;

final class ParagraphRule implements Rule
{
    use IsRule;

    public function shouldParse(Parser $parser): bool
    {
        return true;
    }

    public function parse(Parser $parser): Token
    {
        $content = '';

        while ($parser->current !== null) {
            $line = $parser->consumeUntil(Parser::NEW_LINE);

            // A blank line ends the paragraph and stays available to the newline rule.
            $endsParagraph =
                $parser->position >= $parser->length
                || $parser->comesNext("\n\n", 2)
                || $parser->comesNext("\r\n\r\n", 4)
                || $parser->comesNext("\n\r\n", 3)
                || $parser->comesNext("\r\n\n", 3);

            $newline = $endsParagraph
                ? ''
                : $parser->consumeWhile(Parser::NEW_LINE);

            $matches = [];
            if (
                $content !== ''
                && preg_match('/\A {0,3}(=+|-+)[ \t]*\z/', $line, $matches)
            ) {
                $heading = trim($content);
                $id = mb_strtolower($heading)
                    |> (fn (string $value) => trim(
                        preg_replace('/[^\p{L}\p{N}]+/u', '-', $value) ?? '',
                        '-',
                    ));

                return new HeadingToken(
                    $heading,
                    $matches[1][0] === '=' ? 1 : 2,
                    $id,
                );
            }

            $content .= $line . $newline;

            if ($endsParagraph) {
                break;
            }
        }

        return new ParagraphToken($content);
    }
}
