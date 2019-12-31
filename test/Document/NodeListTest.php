<?php

/**
 * @see       https://github.com/laminas/laminas-dom for the canonical source repository
 * @copyright https://github.com/laminas/laminas-dom/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-dom/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Dom\Document;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Laminas\Dom\Document\NodeList;
use Laminas\Dom\Exception\BadMethodCallException;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Dom\Document\NodeList
 */
class NodeListTest extends TestCase
{
    /** @var DOMNodeList */
    protected $domNodeList;

    /** @var NodeList|DOMNode[] */
    private $nodeList;

    protected function setUp()
    {
        $document = new DOMDocument();
        $document->loadHTML('<html><body><a></a><b></b></body></html>');
        $this->domNodeList = $domNodeList = $document->getElementsByTagName('*');
        $this->nodeList = new NodeList($domNodeList);
    }

    /**
     * @group Laminas-4631
     */
    public function testEmptyResultDoesNotReturnIteratorValidTrue()
    {
        $dom = new DOMDocument();
        $emptyNodeList = $dom->getElementsByTagName('a');
        $result = new NodeList($emptyNodeList);

        $this->assertFalse($result->valid());
    }

    public function testIsCountable()
    {
        $this->assertCount($this->domNodeList->length, $this->nodeList);
    }

    public function testIterable()
    {
        $extractedNodes = [];
        foreach ($this->nodeList as $key => $node) {
            $extractedNodes[$key] = $node;
        }

        $this->assertEquals(iterator_to_array($this->domNodeList), $extractedNodes);
    }

    public function testArrayHasKey()
    {
        $this->assertArrayNotHasKey(-1, $this->nodeList);
        $this->assertArrayHasKey(0, $this->nodeList);
        $this->assertArrayHasKey(1, $this->nodeList);
        $this->assertArrayHasKey(3, $this->nodeList);
        $this->assertArrayNotHasKey(4, $this->nodeList);
        $this->assertArrayNotHasKey(5, $this->nodeList);
    }

    public function testRetrieveElement()
    {
        $node = $this->nodeList[2];

        $this->assertEquals('a', $node->localName);
    }

    public function testItsNotPossibleAddElements()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to write to a read-only list');
        $this->nodeList[0] = '<foobar />';
    }

    public function testOffsetUnset()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to unset on a read-only list');
        unset($this->nodeList[0]);
    }
}
