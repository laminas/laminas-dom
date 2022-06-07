<?php

declare(strict_types=1);

namespace LaminasTest\Dom;

use Laminas\Dom\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testXHTMLNamespaceRegistration()
    {
        $xhtml   = '<?xml version="1.0"?><html xmlns="http://www.w3.org/1999/xhtml"></html>';
        $query   = new Query($xhtml);
        $results = $query->queryXpath('/*');
        $this->assertCount(1, $results);
        $this->assertEquals('html', $results[0]->tagName);
    }
}
