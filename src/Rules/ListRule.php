<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\ListToken;

final class ListRule implements Rule, ProvidesFirstChar
{
    use IsRule;

    public string $firstChar = '-*+';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext(($parser->current ?? '') . ' ', 2);
    }

    public function parse(Parser $parser): ?Token
    {
        $parser->consume(2);
        $content = trim($parser->consumeUntil(Parser::NEW_LINE));
        $newlines = $parser->consumeWhile(Parser::NEW_LINE);

        $childContent = '';

        while ($parser->current !== null) {
            $indent = strspn($parser->content, ' ', $parser->position);

            if ($indent >= 2) {
                while (
                    $parser->current !== null
                    && strspn($parser->content, ' ', $parser->position)
                        >= $indent
                ) {
                    $parser->consume($indent);
                    $childContent .=
                        $parser->consumeUntil(Parser::NEW_LINE) . PHP_EOL;
                    $newlines = $parser->consumeWhile(Parser::NEW_LINE);
                }

                if (preg_match('/(?:^|\n)[-*+] /', $childContent)) {
                    break;
                }

                $content .=
                    ' '
                    . trim(preg_replace('/\s+/u', ' ', $childContent) ?? '');
                $childContent = '';
                continue;
            }

            if ($newlines !== "\n" && $newlines !== "\r\n") {
                break;
            }

            $nextLine = substr(
                $parser->content,
                $parser->position,
                strcspn($parser->content, Parser::NEW_LINE, $parser->position),
            );
            if (
                trim($nextLine) === ''
                || preg_match(
                    '/^(?:[-*+] |[0-9]+[.)] |#{1,6}[ \t]|>|`{3}|~{3}|-{3}|={3}|<|:{3})/',
                    $nextLine,
                )
            ) {
                break;
            }

            $content .= ' ' . trim($parser->consumeUntil(Parser::NEW_LINE));
            $newlines = $parser->consumeWhile(Parser::NEW_LINE);
        }

        $children = $childContent === ''
            ? null
            : $parser->withRules(new ListRule())->lex($childContent)[0];

        $item = new ListItem($content, $children);

        if ($parser->lastToken instanceof ListToken) {
            $parser->lastToken->items[] = $item;
            return null;
        }

        return new ListToken([$item]);
    }
}
