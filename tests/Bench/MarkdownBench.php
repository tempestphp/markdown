<?php

namespace Tempest\Markdown\Tests\Bench;

use League\CommonMark\CommonMarkConverter;
use Michelf\Markdown as Michelf;
use PhpBench\Attributes as Bench;
use Tempest\Markdown\Parser;

#[Bench\Warmup(1)]
#[Bench\RetryThreshold(5)]
#[Bench\OutputTimeUnit('microseconds')]
#[Bench\Iterations(3)]
#[Bench\Revs(1)]
final readonly class MarkdownBench
{
    private Parser $tempest;
    private CommonMarkConverter $league;
    private string $contents;

    public function __construct()
    {
        $this->tempest = new Parser();
        $this->league = new CommonMarkConverter();
        $this->contents = file_get_contents(__DIR__ . '/Fixtures/large.md');
    }

    public function benchTempest(): void
    {
        $this->tempest->parse($this->contents);
    }

    public function benchLeague(): void
    {
        $this->league->convert($this->contents);
    }

    public function benchMichelf(): void
    {
        Michelf::defaultTransform($this->contents);
    }
}
