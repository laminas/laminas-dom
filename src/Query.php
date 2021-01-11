<?php

/**
 * @see       https://github.com/laminas/laminas-dom for the canonical source repository
 * @copyright https://github.com/laminas/laminas-dom/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-dom/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Dom;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use ErrorException;

use function libxml_clear_errors;
use function libxml_disable_entity_loader;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function preg_match;
use function sprintf;
use function strlen;
use function strstr;
use function substr;
use function trim;

use const LIBXML_VERSION;
use const XML_DOCUMENT_TYPE_NODE;

/**
 * Query DOM structures based on CSS selectors and/or XPath
 *
 * @deprecated
 *
 * @see \Laminas\Dom\Document\Query
 */
class Query
{
    /**#@+
     * Document types
     */
    public const DOC_XML   = 'docXml';
    public const DOC_HTML  = 'docHtml';
    public const DOC_XHTML = 'docXhtml';
    /**#@-*/

    /** @var string */
    protected $document;

    /**
     * DOMDocument errors, if any
     *
     * @var false|array
     */
    protected $documentErrors = false;

    /**
     * Document type
     *
     * @var string
     */
    protected $docType;

    /**
     * Document encoding
     *
     * @var null|string
     */
    protected $encoding;

    /**
     * XPath namespaces
     *
     * @var array
     */
    protected $xpathNamespaces = [];

    /**
     * XPath PHP Functions
     *
     * @var mixed
     */
    protected $xpathPhpFunctions;

    /**
     * Constructor
     *
     * @param null|string $document
     * @param null|string $encoding
     */
    public function __construct($document = null, $encoding = null)
    {
        $this->setEncoding($encoding);
        $this->setDocument($document);
    }

    /**
     * Set document encoding
     *
     * @param  string $encoding
     * @return Query
     */
    public function setEncoding($encoding)
    {
        $this->encoding = null === $encoding ? null : (string) $encoding;
        return $this;
    }

    /**
     * Get document encoding
     *
     * @return null|string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set document to query
     *
     * @param  string $document
     * @param  null|string $encoding Document encoding
     * @return Query
     */
    public function setDocument($document, $encoding = null)
    {
        if (0 === strlen($document)) {
            return $this;
        }
        // breaking XML declaration to make syntax highlighting work
        if ('<' . '?xml' === substr(trim($document), 0, 5)) {
            if (preg_match('/<html[^>]*xmlns="([^"]+)"[^>]*>/i', $document, $matches)) {
                $this->xpathNamespaces[] = $matches[1];
                return $this->setDocumentXhtml($document, $encoding);
            }
            return $this->setDocumentXml($document, $encoding);
        }
        if (strstr($document, 'DTD XHTML')) {
            return $this->setDocumentXhtml($document, $encoding);
        }
        return $this->setDocumentHtml($document, $encoding);
    }

    /**
     * Register HTML document
     *
     * @param  string $document
     * @param  null|string $encoding Document encoding
     * @return Query
     */
    public function setDocumentHtml($document, $encoding = null)
    {
        $this->document = (string) $document;
        $this->docType  = self::DOC_HTML;
        if (null !== $encoding) {
            $this->setEncoding($encoding);
        }
        return $this;
    }

    /**
     * Register XHTML document
     *
     * @param  string $document
     * @param  null|string $encoding Document encoding
     * @return Query
     */
    public function setDocumentXhtml($document, $encoding = null)
    {
        $this->document = (string) $document;
        $this->docType  = self::DOC_XHTML;
        if (null !== $encoding) {
            $this->setEncoding($encoding);
        }
        return $this;
    }

    /**
     * Register XML document
     *
     * @param  string $document
     * @param  null|string $encoding Document encoding
     * @return Query
     */
    public function setDocumentXml($document, $encoding = null)
    {
        $this->document = (string) $document;
        $this->docType  = self::DOC_XML;
        if (null !== $encoding) {
            $this->setEncoding($encoding);
        }
        return $this;
    }

    /**
     * Retrieve current document
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Get document type
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->docType;
    }

    /**
     * Get any DOMDocument errors found
     *
     * @return false|array
     */
    public function getDocumentErrors()
    {
        return $this->documentErrors;
    }

    /**
     * Perform a CSS selector query
     *
     * @param  string $query
     * @return NodeList
     */
    public function execute($query, ?DOMNode $contextNode = null)
    {
        $xpathQuery = Document\Query::cssToXpath($query);
        return $this->queryXpath($xpathQuery, $query, $contextNode);
    }

    /**
     * Perform an XPath query
     *
     * @param  string|array $xpathQuery
     * @param  string|null  $query      CSS selector query
     * @param  DOMNode $contextNode $contextNode
     * @throws Exception\RuntimeException
     * @return NodeList
     */
    public function queryXpath($xpathQuery, $query = null, ?DOMNode $contextNode = null)
    {
        if (null === ($document = $this->getDocument())) {
            throw new Exception\RuntimeException('Cannot query; no document registered');
        }

        $encoding = $this->getEncoding();
        libxml_use_internal_errors(true);
        $disableEntityLoaderFlag = self::disableEntityLoader();
        if (null === $encoding) {
            $domDoc = new DOMDocument('1.0');
        } else {
            $domDoc = new DOMDocument('1.0', $encoding);
        }
        $type = $this->getDocumentType();
        switch ($type) {
            case self::DOC_XML:
                $success = $domDoc->loadXML($document);
                foreach ($domDoc->childNodes as $child) {
                    if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                        throw new Exception\RuntimeException(
                            'Invalid XML: Detected use of illegal DOCTYPE'
                        );
                    }
                }
                break;
            case self::DOC_HTML:
            case self::DOC_XHTML:
            default:
                $success = $domDoc->loadHTML($document);
                break;
        }
        $errors = libxml_get_errors();
        if (! empty($errors)) {
            $this->documentErrors = $errors;
            libxml_clear_errors();
        }
        self::disableEntityLoader($disableEntityLoaderFlag);
        libxml_use_internal_errors(false);

        if (! $success) {
            throw new Exception\RuntimeException(sprintf('Error parsing document (type == %s)', $type));
        }

        $nodeList = $this->getNodeList($domDoc, $xpathQuery, $contextNode);
        return new NodeList($query, $xpathQuery, $domDoc, $nodeList, $contextNode);
    }

    /**
     * Register XPath namespaces
     *
     * @param  array $xpathNamespaces
     * @return void
     */
    public function registerXpathNamespaces($xpathNamespaces)
    {
        $this->xpathNamespaces = $xpathNamespaces;
    }

    /**
     * Register PHP Functions to use in internal DOMXPath
     *
     * @param  bool $xpathPhpFunctions
     * @return void
     */
    public function registerXpathPhpFunctions($xpathPhpFunctions = true)
    {
        $this->xpathPhpFunctions = $xpathPhpFunctions;
    }

    /**
     * Prepare node list
     *
     * @param  DOMDocument $document
     * @param  string|array $xpathQuery
     * @return DOMNodeList
     * @throws ErrorException If query cannot be executed.
     */
    protected function getNodeList($document, $xpathQuery, ?DOMNode $contextNode = null)
    {
        $xpath = new DOMXPath($document);
        foreach ($this->xpathNamespaces as $prefix => $namespaceUri) {
            $xpath->registerNamespace($prefix, $namespaceUri);
        }
        if ($this->xpathPhpFunctions) {
            $xpath->registerNamespace("php", "http://php.net/xpath");
            $this->xpathPhpFunctions === true ?
                $xpath->registerPhpFunctions()
                : $xpath->registerPhpFunctions($this->xpathPhpFunctions);
        }
        $xpathQuery = (string) $xpathQuery;

        return $xpath->queryWithErrorException($xpathQuery, $contextNode);
    }

    /**
     * Disable the ability to load external XML entities based on libxml version
     *
     * If we are using libxml < 2.9, unsafe XML entity loading must be
     * disabled with a flag.
     *
     * If we are using libxml >= 2.9, XML entity loading is disabled by default.
     *
     * @return bool
     */
    private static function disableEntityLoader(bool $flag = true)
    {
        if (LIBXML_VERSION < 20900) {
            return libxml_disable_entity_loader($flag);
        }
        return $flag;
    }
}
