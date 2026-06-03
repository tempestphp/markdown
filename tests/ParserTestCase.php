<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

abstract class ParserTestCase extends TestCase
{
    #[Before]
    public function resetParser(): void
    {
        Parser::reset();
    }
}
