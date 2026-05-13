<?php

namespace Tempest\Markdown\Tests\Bench;

use Generator;
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
#[Bench\ParamProviders('provideFiles')]
final readonly class MarkdownBench
{
    private Parser $tempest;
    private CommonMarkConverter $league;
    private ParsedownExtra $erusev;

    public function __construct()
    {
        $this->tempest = new Parser();
        $this->league = new CommonMarkConverter();
        $this->erusev = new ParsedownExtra();
    }

    public function benchTempest(array $params): void
    {
        $this->tempest->parse($params['contents']);
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
        foreach (glob(__DIR__ . '/Fixtures/*.md') as $path) {
            yield pathinfo($path, PATHINFO_FILENAME) => ['contents' => file_get_contents($path)];
        }
    }
}
