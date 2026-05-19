<?php

namespace Tempest\Markdown\ParserRules;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Tempest\Markdown\Exceptions\FrontMatterCouldNotBeParsed;
use Tempest\Markdown\Exceptions\FrontMatterShouldBeAnArray;
use Tempest\Markdown\Exceptions\FrontMatterWasNotProperlyClosed;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class FrontMatterRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        if ($parser->position !== 0) {
            return false;
        }

        if (! $parser->comesNext('---', 3)) {
            return false;
        }

        return true;
    }

    public function parse(Parser $parser): string
    {
        $originalPosition = $parser->position;
        $parser->consumeWhile('-');
        $parser->consumeWhile(Parser::NEW_LINE);
        $content = $parser->consumeUntilString('---');

        if (! $parser->comesNext('---', 3)) {
            throw new FrontMatterWasNotProperlyClosed($parser->withPosition($originalPosition));
        }

        $parser->consumeWhile('-');
        $parser->consumeWhile(Parser::NEW_LINE);

        try {
            $data = Yaml::parse($content);
        } catch (ParseException $cause) {
            throw new FrontMatterCouldNotBeParsed($parser->withPosition($originalPosition), $cause);
        }

        if (! is_array($data)) {
            throw new FrontMatterShouldBeAnArray($parser->withPosition($originalPosition));
        }

        $parser->frontMatter = [...$parser->frontMatter, ...$data];

        return '';
    }
}
