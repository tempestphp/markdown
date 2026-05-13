<?php

namespace Tempest\Markdown\Tests\Bench;

use League\CommonMark\CommonMarkConverter;
use Michelf\Markdown as Michelf;
use ParsedownExtra;
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
    private ParsedownExtra $erusev;

    public function __construct()
    {
        $this->tempest = new Parser();
        $this->league = new CommonMarkConverter();
        $this->erusev = new ParsedownExtra();
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

    public function benchErusev(): void
    {
        $this->erusev->text($this->contents);
    }
}
