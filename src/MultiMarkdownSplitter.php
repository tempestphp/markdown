<?php

namespace Tempest\Markdown;

final class MultiMarkdownSplitter
{
    /**
     * @return list<array{name: ?string, content: string}>
     */
    public function split(string $content, ?string $baseName = null, string $keyword = 'next'): array
    {
        $matches = [];
        preg_match_all($this->markerPattern($keyword), $content, $matches, PREG_OFFSET_CAPTURE);

        $markers = [];

        // @mago-expect analysis:invalid-destructuring-source
        foreach ($matches[0] as $i => [$fullMatch, $offset]) {
            $fullMatch = (string) $fullMatch;
            $offset = (int) $offset;

            $markers[] = [
                'rawName' => trim($matches[1][$i][0] ?? ''),
                'start' => $offset,
                'end' => $offset + strlen($fullMatch),
            ];
        }

        if ($markers === []) {
            return [['name' => null, 'content' => trim($content)]];
        }

        $chunks = [];
        $position = 0;

        $leading = trim(substr($content, 0, $markers[0]['start']));

        if ($leading !== '') {
            $position++;
            $chunks[] = ['name' => $this->resolveMarkerName('', $baseName, $position), 'content' => $leading];
        }

        foreach ($markers as $index => $marker) {
            $isAuto = $marker['rawName'] === '' || $marker['rawName'] === '*';

            $end = $markers[$index + 1]['start'] ?? strlen($content);
            $chunk = trim(substr($content, $marker['end'], $end - $marker['end']));

            if ($isAuto && $chunk === '') {
                continue;
            }

            $position++;
            $chunks[] = ['name' => $this->resolveMarkerName($marker['rawName'], $baseName, $position), 'content' => $chunk];
        }

        return $chunks;
    }

    private function markerPattern(string $keyword): string
    {
        $escaped = preg_quote($keyword, '/');

        return "/^[ \\t]*<!--\\s*{$escaped}(?:\\s*:\\s*(.*?))?\\s*-->[ \\t]*\\r?\\n?/m";
    }

    private function resolveMarkerName(string $rawName, ?string $baseName, int $position): string
    {
        if ($rawName !== '' && $rawName !== '*') {
            return $rawName;
        }

        return $baseName === null ? "chunk-{$position}" : "{$baseName}-{$position}";
    }
}
