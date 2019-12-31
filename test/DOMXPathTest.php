<?php

/**
 * @see       https://github.com/laminas/laminas-dom for the canonical source repository
 * @copyright https://github.com/laminas/laminas-dom/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-dom/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Dom;

use DOMDocument;
use DOMNodeList;
use ErrorException;
use Laminas\Dom\DOMXPath;
use PHPUnit_Framework_TestCase as TestCase;

class DOMXPathTest extends TestCase
{
    /** @var DOMDocument */
    private $document;

    protected function setUp()
    {
        $this->document = new DOMDocument('<any></any>');
    }

    public function testQueryWithErrorExceptionSuccess()
    {
        $domXPath = new DOMXPath($this->document);

        $result = $domXPath->queryWithErrorException('any');

        $this->assertInstanceOf(DOMNodeList::class, $result);
    }

    public function testQueryWithErrorExceptionThrowExceptionWhenQueryExpresionIsInvalid()
    {
        $domXPath = new DOMXPath($this->document);

        $this->setExpectedException(ErrorException::class, 'Invalid expression');
        $domXPath->queryWithErrorException('any#any');
    }
}
