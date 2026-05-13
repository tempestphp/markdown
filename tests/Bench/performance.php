<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$contents = file_get_contents(__DIR__ . '/Fixtures/02-large.md');

// ## SETUP
$markdown = new Tempest\Markdown\Markdown();
//$markdown = new \League\CommonMark\CommonMarkConverter();
// ##

$start = microtime(true);

// ## RUN
$html = $markdown->parse($contents);
//$html = $markdown->convert($contents)->getContent();
// ##

if (! str_contains($html, '<h2>')) {
    throw new Exception('Failed to parse markdown');
}

$end = microtime(true);

echo ($end - $start) . PHP_EOL;
