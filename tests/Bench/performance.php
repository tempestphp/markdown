<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$contents = file_get_contents(__DIR__ . '/Fixtures/large.md');

### SETUP
$markdown = new Tempest\Markdown\Markdown();
//$markdown = new \League\CommonMark\CommonMarkConverter();
###

$start = microtime(true);

### RUN
$html = $markdown->parse($contents);
//$html = $markdown->convert($contents)->getContent();
###

$end = microtime(true);


echo $end - $start . PHP_EOL;