<?php

namespace Tempest\Markdown\Tests\Bench\Extension;

use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Printer\BareValuePrinter;
use PhpBench\Registry\Config;
use PhpBench\Report\Model\Builder\ReportBuilder;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Model\TableRow;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class MarkdownRendererTest extends TestCase
{
    #[Test]
    public function test_it_renders_a_compact_aggregate_report_table(): void
    {
        $output = new BufferedOutput();
        $renderer = new MarkdownRenderer($output, new BareValuePrinter());
        $reports = $this->reports([
            [
                'benchmark' => 'MarkdownBench',
                'subject' => 'benchTempest',
                'set' => '01-small',
                'revs' => 3,
                'its' => 5,
                'mem_peak' => '1.001mb 0.00%',
                'mode' => '1.234ms +0.17%',
                'rstdev' => '±2.05% +108.06%',
            ],
        ], 'Benchmark Results');

        $renderer->render($reports, new Config('markdown', ['file' => null]));

        $this->assertSame(<<<'MARKDOWN'
        ## Benchmark Results

        | Benchmark | Set | Mem. Peak | Time | Variability |
        | --------- | --- | --------- | ---- | ----------- |
        | MarkdownBench(benchTempest) | 01-small | 1.001mb 0.00% | 1.234ms +0.17% | ±2.05% +108.06% |


        MARKDOWN, $output->fetch());
    }

    #[Test]
    public function test_it_keeps_non_aggregate_table_columns_unchanged(): void
    {
        $output = new BufferedOutput();
        $renderer = new MarkdownRenderer($output, new BareValuePrinter());
        $reports = $this->reports([
            [
                'name' => 'Example',
                'value' => '123',
            ],
        ]);

        $renderer->render($reports, new Config('markdown', ['file' => null]));

        $this->assertSame(<<<'MARKDOWN'
        | name | value |
        | ---- | ----- |
        | Example | 123 |


        MARKDOWN, $output->fetch());
    }

    #[Test]
    public function test_it_can_filter_compact_rows_by_minimum_time_difference(): void
    {
        $output = new BufferedOutput();
        $renderer = new MarkdownRenderer($output, new BareValuePrinter());
        $reports = $this->reports([
            [
                'benchmark' => 'MarkdownBench',
                'subject' => 'benchTempest',
                'set' => '01-small',
                'revs' => 3,
                'its' => 5,
                'mem_peak' => '1.001mb 0.00%',
                'mode' => '1.234ms +0.17%',
                'rstdev' => '±2.05% +1.00%',
            ],
            [
                'benchmark' => 'MarkdownBench',
                'subject' => 'benchLeague',
                'set' => '02-large',
                'revs' => 3,
                'its' => 5,
                'mem_peak' => '2.002mb +0.10%',
                'mode' => '5.678ms +1.55%',
                'rstdev' => '±0.69% +0.50%',
            ],
        ], 'Benchmark Results');

        $renderer->render($reports, new Config('markdown', ['file' => null, 'outlier_min_diff' => 1.0]));

        $this->assertSame(<<<'MARKDOWN'
        ## Benchmark Results

        | Benchmark | Set | Mem. Peak | Time | Variability |
        | --------- | --- | --------- | ---- | ----------- |
        | MarkdownBench(benchLeague) | 02-large | 2.002mb +0.10% | 5.678ms +1.55% | ±0.69% +0.50% |


        MARKDOWN, $output->fetch());
    }

    #[Test]
    public function test_it_shows_an_informative_message_when_no_outliers_match(): void
    {
        $output = new BufferedOutput();
        $renderer = new MarkdownRenderer($output, new BareValuePrinter());
        $reports = $this->reports([
            [
                'benchmark' => 'MarkdownBench',
                'subject' => 'benchTempest',
                'set' => '01-small',
                'revs' => 3,
                'its' => 5,
                'mem_peak' => '1.001mb 0.00%',
                'mode' => '1.234ms +0.17%',
                'rstdev' => '±2.05% +1.00%',
            ],
            [
                'benchmark' => 'MarkdownBench',
                'subject' => 'benchLeague',
                'set' => '02-large',
                'revs' => 3,
                'its' => 5,
                'mem_peak' => '2.002mb +0.10%',
                'mode' => '5.678ms +1.55%',
                'rstdev' => '±0.69% +0.50%',
            ],
        ], 'Benchmark Results');

        $renderer->render($reports, new Config('markdown', ['file' => null, 'outlier_min_diff' => 5.0]));

        $this->assertSame(<<<'MARKDOWN'
        ## Benchmark Results

        _No benchmark changes above ±5%._


        MARKDOWN, $output->fetch());
    }

    /**
     * @param list<array<string, int|string>> $rows
     */
    private function reports(array $rows, ?string $title = null): Reports
    {
        $tableRows = array_map(function (array $row): TableRow {
            $cells = array_map(
                PhpValueFactory::fromValue(...),
                $row,
            );

            return TableRow::fromArray($cells);
        }, $rows);

        $table = new Table($tableRows, headers: null, title: $title);

        return Reports::fromReport(ReportBuilder::create()->addObject($table)->build());
    }
}
