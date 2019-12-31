<?php

/**
 * @see       https://github.com/laminas/laminas-dom for the canonical source repository
 * @copyright https://github.com/laminas/laminas-dom/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-dom/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Dom;

use Laminas\Dom\NodeList;

/**
 * @category   Laminas
 * @package    Laminas_Dom
 * @subpackage UnitTests
 * @group      Laminas_Dom
 */
class NodeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group Laminas-4631
     */
    public function testEmptyResultDoesNotReturnIteratorValidTrue()
    {
        $dom = new \DOMDocument();
        $emptyNodeList = $dom->getElementsByTagName("a");
        $result = new NodeList("", "", $dom, $emptyNodeList);

        $this->assertFalse($result->valid());
    }
}
