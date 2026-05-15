<?php

namespace Tempest\Markdown;

trait RendersSnippet
{
    public function renderSnippet(Lexer $lexer): string
    {
        $margin = 1;

        $lines = explode(PHP_EOL, $lexer->content);

        $currentLine = substr_count(
            substr($lexer->content, 0, $lexer->position),
            PHP_EOL,
        );

        $errorLineNumber = max($currentLine - $margin, 0);

        $lines = array_slice(
            $lines,
            $errorLineNumber,
            ($margin * 2) + 1,
        );

        $rendered = '';

        $largestLineNumber = $currentLine - $margin + 3;
        $padLength = strlen((string) $largestLineNumber);

        if ($padLength <= 1) {
            $padLength += 1;
        }

        foreach ($lines as $i => $line) {
            $lineNumber = $errorLineNumber === 0
                ? $currentLine + $i + 1
                : $currentLine - $margin + $i;

            $isCurrentLine = $errorLineNumber === 0
                ? $errorLineNumber === $i
                : $errorLineNumber === ($i + 1);

            $paddedLineNumber = str_pad((string) $lineNumber, $padLength, '0', STR_PAD_LEFT);

            if ($isCurrentLine) {
                $rendered .= "{$paddedLineNumber} > {$line}" . PHP_EOL;
            } else {
                $rendered .= "{$paddedLineNumber} | {$line}" . PHP_EOL;
            }
        }

        return trim($rendered);
    }
}
