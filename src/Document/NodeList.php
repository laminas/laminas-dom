<?php

namespace Laminas\Dom\Document;

use ArrayAccess;
use Countable;
use DOMNode;
use DOMNodeList;
use Iterator;
use Laminas\Dom\Exception;

/**
 * DOMNodeList wrapper for Laminas\Dom\Document\Query results
 */
class NodeList implements Iterator, Countable, ArrayAccess
{
    /** @var DOMNodeList */
    protected $list;

    /**
     * Current iterator position
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Constructor
     */
    public function __construct(DOMNodeList $list)
    {
        $this->list = $list;
    }

    /**
     * Iterator: rewind to first element
     *
     * @return DOMNode
     */
    public function rewind()
    {
        $this->position = 0;

        return $this->list->item(0);
    }

    /**
     * Iterator: is current position valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->offsetExists($this->position);
    }

    /**
     * Iterator: return current element
     *
     * @return DOMNode
     */
    public function current()
    {
        return $this->list->item($this->position);
    }

    /**
     * Iterator: return key of current element
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator: move to next element
     *
     * @return DOMNode
     */
    public function next()
    {
        ++$this->position;

        return $this->list->item($this->position);
    }

    /**
     * Countable: get count
     *
     * @return int
     */
    public function count()
    {
        return $this->list->length;
    }

    /**
     * ArrayAccess: offset exists
     *
     * @param int $key
     * @return bool
     */
    public function offsetExists($key)
    {
        // DOMNodeList return `null` if item not exists.
        return null !== $this->list->item($key);
    }

    /**
     * ArrayAccess: get offset
     *
     * @param int $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->list->item($key);
    }

    /**
     * ArrayAccess: set offset
     *
     * @param  mixed $key
     * @param  mixed $value
     * @throws Exception\BadMethodCallException When attempting to write to a read-only item.
     */
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
    public function offsetUnset($key)
    {
        throw new Exception\BadMethodCallException('Attempting to unset on a read-only list');
    }
}
