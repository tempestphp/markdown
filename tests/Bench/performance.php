<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$contents = file_get_contents(__DIR__ . '/Fixtures/02-large.md');

// ## SETUP
$markdown = new Tempest\Markdown\Markdown();
//$markdown = new \League\CommonMark\CommonMarkConverter();
// ##

$start = microtime(true);

// ## RUN
$html = $markdown->parse($contents)->html;
//$html = $markdown->convert($contents)->getContent();

echo $html;

echo PHP_EOL . PHP_EOL . '###################' . PHP_EOL;

$end = microtime(true);

echo ' ' . ($end - $start);

echo PHP_EOL . '###################' . PHP_EOL;

file_put_contents(__DIR__ . '/Fixtures/02-large.html', $html);