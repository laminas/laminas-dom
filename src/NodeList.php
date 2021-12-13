<?php

declare(strict_types=1);

namespace Laminas\Dom;

use ArrayAccess;
use Countable;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use Iterator;
use ReturnTypeWillChange;

use function in_array;
use function range;

/**
 * Nodelist for DOM XPath query
 *
 * @deprecated
 *
 * @see \Laminas\Dom\Document\NodeList
 */
class NodeList implements Iterator, Countable, ArrayAccess
{
    /**
     * CSS Selector query
     *
     * @var string
     */
    protected $cssQuery;

    /** @var DOMDocument */
    protected $document;

    /** @var DOMNodeList */
    protected $nodeList;

    /**
     * Current iterator position
     *
     * @var int
     */
    protected $position = 0;

    /**
     * XPath query
     *
     * @var string
     */
    protected $xpathQuery;

    /** @var DOMNode|null */
    protected $contextNode;

    /**
     * Constructor
     *
     * @param string       $cssQuery
     * @param string|array $xpathQuery
     */
    public function __construct(
        $cssQuery,
        $xpathQuery,
        DOMDocument $document,
        DOMNodeList $nodeList,
        ?DOMNode $contextNode = null
    ) {
        $this->cssQuery    = $cssQuery;
        $this->xpathQuery  = $xpathQuery;
        $this->document    = $document;
        $this->nodeList    = $nodeList;
        $this->contextNode = $contextNode;
    }

    /**
     * Retrieve CSS Query
     *
     * @return string
     */
    public function getCssQuery()
    {
        return $this->cssQuery;
    }

    /**
     * Retrieve XPath query
     *
     * @return string
     */
    public function getXpathQuery()
    {
        return $this->xpathQuery;
    }

    /**
     * Retrieve DOMDocument
     *
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Retrieve context node
     *
     * @return DOMNode
     */
    public function getContextNode()
    {
        return $this->contextNode;
    }

    /**
     * Iterator: rewind to first element
     *
     * @return DOMNode
     */
    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = 0;

        return $this->nodeList->item(0);
    }

    /**
     * Iterator: is current position valid?
     *
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function valid()
    {
        if (in_array($this->position, range(0, $this->nodeList->length - 1)) && $this->nodeList->length > 0) {
            return true;
        }

        return false;
    }

    /**
     * Iterator: return current element
     *
     * @return DOMNode
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->nodeList->item($this->position);
    }

    /**
     * Iterator: return key of current element
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator: move to next element
     *
     * @return DOMNode
     */
    #[ReturnTypeWillChange]
    public function next()
    {
        ++$this->position;

        return $this->nodeList->item($this->position);
    }

    /**
     * Countable: get count
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return $this->nodeList->length;
    }

    /**
     * ArrayAccess: offset exists
     *
     * @param int $key
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function offsetExists($key)
    {
        if (in_array($key, range(0, $this->nodeList->length - 1)) && $this->nodeList->length > 0) {
            return true;
        }
        return false;
    }

    /**
     * ArrayAccess: get offset
     *
     * @param int $key
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->nodeList->item($key);
    }

    /**
     * ArrayAccess: set offset
     *
     * @param  mixed $key
     * @param  mixed $value
     * @throws Exception\BadMethodCallException When attempting to write to a read-only item.
     */
    #[ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        throw new Exception\BadMethodCallException('Attempting to write to a read-only list');
    }

    /**
     * ArrayAccess: unset offset
     *
     * @param  mixed $key
     * @throws Exception\BadMethodCallException When attempting to unset a read-only item.
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        throw new Exception\BadMethodCallException('Attempting to unset on a read-only list');
    }
}
