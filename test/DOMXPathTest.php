<?php

namespace LaminasTest\Dom;

use DOMDocument;
use DOMNodeList;
use ErrorException;
use Laminas\Dom\DOMXPath;
use PHPUnit\Framework\TestCase;

class DOMXPathTest extends TestCase
{
    /** @var DOMDocument */
    private $document;

    protected function setUp(): void
    {
        $this->document = new DOMDocument('<any></any>');
    }

    public function testQueryWithErrorExceptionSuccess()
    {
        $domXpath = new DOMXPath($this->document);

        $result = $domXpath->queryWithErrorException('any');

        $this->assertInstanceOf(DOMNodeList::class, $result);
    }

    public function testQueryWithErrorExceptionThrowExceptionWhenQueryExpresionIsInvalid()
    {
        $domXpath = new DOMXPath($this->document);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Invalid expression');
        $domXpath->queryWithErrorException('any#any');
    }
}
