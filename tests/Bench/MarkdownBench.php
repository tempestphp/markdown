<?php

namespace Tempest\Markdown\Tests\Bench;

use Generator;
use League\CommonMark\CommonMarkConverter;
use Michelf\Markdown as Michelf;
use ParsedownExtra;
use PhpBench\Attributes as Bench;
use Tempest\Markdown\Parser;

// Each iteration is a fresh subprocess, so JIT must warm up inside Warmup.
// phpbench.json drops opcache.jit_hot_func/loop to 2 so JIT settles within a
// handful of calls; Warmup(15) covers it, Revs(15)/Iterations(3) keeps stddev
// tight without blowing up wall time.
#[Bench\Warmup(15)]
#[Bench\RetryThreshold(2)]
#[Bench\OutputTimeUnit('milliseconds', 3)]
#[Bench\Iterations(3)]
#[Bench\Revs(15)]
#[Bench\ParamProviders('provideFiles')]
final readonly class MarkdownBench
{
    private Parser $tempest;
    private Parser $tempestWithHighlight;
    private CommonMarkConverter $league;
    private ParsedownExtra $erusev;

    public function __construct()
    {
        $this->tempest = new Parser(highlighter: null);
        $this->tempestWithHighlight = new Parser();
        $this->league = new CommonMarkConverter();
        $this->erusev = new ParsedownExtra();
    }

    public function benchTempest(array $params): void
    {
        $this->tempest->parse($params['contents']);
    }

    public function benchTempestWithHighlight(array $params): void
    {
        $this->tempestWithHighlight->parse($params['contents']);
    }

    public function benchLeague(array $params): void
    {
        $this->league->convert($params['contents']);
    }

    public function benchMichelf(array $params): void
    {
        Michelf::defaultTransform($params['contents']);
    }

    public function benchErusev(array $params): void
    {
        $this->erusev->text($params['contents']);
    }

    public function provideFiles(): Generator
    {
        $files = glob(__DIR__ . '/Fixtures/*.md') ?: [];

        foreach ($files as $path) {
            yield pathinfo($path, PATHINFO_FILENAME) => ['contents' => file_get_contents($path)];
        }
    }
}
