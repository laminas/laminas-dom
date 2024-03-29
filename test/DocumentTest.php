<?php

declare(strict_types=1);

namespace LaminasTest\Dom;

use DOMDocument;
use Error;
use ErrorException;
use Exception;
use Laminas\Dom\Document;
use Laminas\Dom\Exception\ExceptionInterface as DOMException;
use Laminas\Dom\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function restore_error_handler;
use function set_error_handler;
use function strtolower;

use const PHP_VERSION_ID;

/**
 * @covers Laminas\Dom\Document
 * @covers Laminas\Dom\Document\Query::execute
 */
class DocumentTest extends TestCase
{
    /** @var null|string */
    protected $html;

    /** @var Document */
    protected $document;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp(): void
    {
        $this->document = new Document();
    }

    private function getHtml(): string
    {
        if (null === $this->html) {
            $this->html = file_get_contents(__DIR__ . '/_files/sample.xhtml');
        }
        return $this->html;
    }

    private function loadHtml()
    {
        $this->document = new Document($this->getHtml());
    }

    public function handleError(string $msg, int $code = 0)
    {
    }

    public function testConstructorShouldNotRequireArguments()
    {
        $document = new Document();
        $this->assertInstanceOf(Document::class, $document);
    }

    public function testConstructorShouldAcceptDocumentString()
    {
        $html     = $this->getHtml();
        $document = new Document($html);
        $this->assertSame($html, $document->getStringDocument());
    }

    public function testDocShouldBeNullByDefault()
    {
        $this->assertNull($this->document->getStringDocument());
    }

    public function testDomDocShouldRaiseExceptionByDefault()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no document');
        $this->document->getDomDocument();
    }

    public function testDocShouldBeNullByEmptyStringConstructor()
    {
        $emptyStr       = '';
        $this->document = new Document($emptyStr);
        $this->assertNull($this->document->getStringDocument());
    }

    public function testDocTypeShouldBeNullByDefault()
    {
        $this->assertNull($this->document->getType());
    }

    public function testDocEncodingShouldBeNullByDefault()
    {
        $this->assertNull($this->document->getEncoding());
    }

    public function testShouldAllowSettingDocument()
    {
        $this->testDocShouldBeNullByDefault();
        $this->loadHtml();
        $this->assertEquals($this->getHtml(), $this->document->getStringDocument());
    }

    public function testDocumentTypeShouldBeAutomaticallyDiscovered()
    {
        $this->loadHtml();
        $this->assertEquals(Document::DOC_XHTML, $this->document->getType());
        $this->document = new Document('<?xml version="1.0"?><root></root>');
        $this->assertEquals(Document::DOC_XML, $this->document->getType());
        $this->document = new Document('<html><body></body></html>');
        $this->assertEquals(Document::DOC_HTML, $this->document->getType());
    }

    public function testQueryingWithoutRegisteringDocumentShouldThrowException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no document');
        Document\Query::execute('.foo', $this->document, Document\Query::TYPE_CSS);
    }

    public function testQueryingInvalidDocumentShouldThrowException()
    {
        set_error_handler([$this, 'handleError']);
        $this->document = new Document('some bogus string', Document::DOC_XML);
        $this->expectException(DOMException::class);
        $this->expectExceptionMessage('Error parsing');
        try {
            Document\Query::execute('.foo', $this->document, Document\Query::TYPE_CSS);
        } finally {
            restore_error_handler();
        }
    }

    public function testGetDomMethodShouldReturnDomDocumentWithStringDocumentInConstructor()
    {
        $html     = $this->getHtml();
        $document = new Document($html);
        $this->assertInstanceOf(DOMDocument::class, $document->getDomDocument());
    }

    public function testGetDomMethodShouldReturnDomDocumentWithStringDocumentSetFromMethod()
    {
        $this->loadHtml();
        $this->assertInstanceOf(DOMDocument::class, $this->document->getDomDocument());
    }

    public function testQueryShouldReturnResultObject()
    {
        $this->loadHtml();
        $result = Document\Query::execute('.foo', $this->document, Document\Query::TYPE_CSS);
        $this->assertInstanceOf(Document\NodeList::class, $result);
    }

    public function testResultShouldIndicateNumberOfFoundNodes()
    {
        $this->loadHtml();
        $result = Document\Query::execute('.foo', $this->document, Document\Query::TYPE_CSS);
        $this->assertCount(3, $result);
    }

    public function testQueryShouldFindNodesWithMultipleClasses()
    {
        $this->loadHtml();
        $result = Document\Query::execute('.footerblock .last', $this->document, Document\Query::TYPE_CSS);
        $this->assertCount(1, $result);
    }

    public function testQueryShouldFindNodesWithArbitraryAttributeSelectorsExactly()
    {
        $this->loadHtml();
        $result = Document\Query::execute('div[dojoType="FilteringSelect"]', $this->document, Document\Query::TYPE_CSS);
        $this->assertCount(1, $result);
    }

    public function testQueryShouldFindNodesWithArbitraryAttributeSelectorsThatIncludeSpaces()
    {
        $this->loadHtml();
        $result = Document\Query::execute('div[data-attr="foo bar baz"]', $this->document, Document\Query::TYPE_CSS);
        $this->assertCount(1, $result);
    }

    public function testQueryShouldFindNodesWithArbitraryAttributeSelectorsAsDiscreteWords()
    {
        $this->loadHtml();
        $result = Document\Query::execute('li[dojoType~="bar"]', $this->document, Document\Query::TYPE_CSS);
        $this->assertCount(2, $result);
    }

    public function testQueryShouldFindNodesWithArbitraryAttributeSelectorsAndAttributeValue()
    {
        $this->loadHtml();
        $result = Document\Query::execute('li[dojoType*="bar"]', $this->document, Document\Query::TYPE_CSS);
        $this->assertCount(2, $result);
    }

    public function testQueryXpathShouldAllowQueryingArbitraryUsingXpath()
    {
        $this->loadHtml();
        $result = Document\Query::execute('//li[contains(@dojotype, "bar")]', $this->document);
        $this->assertCount(2, $result);
    }

    public function testXpathPhpFunctionsShouldBeDisabledByDefault()
    {
        $this->loadHtml();
        try {
            Document\Query::execute(
                '//meta[php:functionString("strtolower", @http-equiv) = "content-type"]',
                $this->document
            );
        } catch (Exception $e) {
            return;
        }
        $this->fail('XPath PHPFunctions should be disabled by default');
    }

    public function testXpathPhpFunctionsShouldBeEnabledWithoutParameter()
    {
        $this->loadHtml();
        $this->document->registerXpathPhpFunctions();
        $result = Document\Query::execute(
            '//meta[php:functionString("strtolower", @http-equiv) = "content-type"]',
            $this->document
        );
        $this->assertEquals(
            'content-type',
            strtolower($result->current()->getAttribute('http-equiv'))
        );
    }

    public function testXpathPhpFunctionsShouldBeNotCalledWhenSpecifiedFunction()
    {
        $this->loadHtml();
        $this->document->registerXpathPhpFunctions('stripos');

        $this->expectException(PHP_VERSION_ID >= 80000 ? Error::class : ErrorException::class);
        $this->expectExceptionMessageMatches('/Not allowed to call handler .strtolower()/');

        Document\Query::execute(
            '//meta[php:functionString("strtolower", @http-equiv) = "content-type"]',
            $this->document
        );
    }

    /**
     * @group Laminas-9243
     */
    public function testLoadingDocumentWithErrorsShouldNotRaisePhpErrors()
    {
        $file           = file_get_contents(__DIR__ . '/_files/bad-sample.html');
        $this->document = new Document($file);
        $result         = Document\Query::execute('p', $this->document, Document\Query::TYPE_CSS);
        $errors         = $this->document->getErrors();
        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    /**
     * @group Laminas-9765
     */
    public function testCssSelectorShouldFindNodesWhenMatchingMultipleAttributes()
    {
        $html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<body>
  <form action="#" method="get">
    <input type="hidden" name="foo" value="1" id="foo"/>
    <input type="hidden" name="bar" value="0" id="bar"/>
    <input type="hidden" name="baz" value="1" id="baz"/>
  </form>
</body>
</html>
HTML;

        $this->document = new Document($html);
        $result         = Document\Query::execute(
            'input[type="hidden"][value="1"]',
            $this->document,
            Document\Query::TYPE_CSS
        );
        $this->assertCount(2, $result);
        $result = Document\Query::execute(
            'input[value="1"][type~="hidden"]',
            $this->document,
            Document\Query::TYPE_CSS
        );
        $this->assertCount(2, $result);
        $result = Document\Query::execute(
            'input[type="hidden"][value="0"]',
            $this->document,
            Document\Query::TYPE_CSS
        );
        $this->assertCount(1, $result);
    }

    /**
     * @group Laminas-3938
     */
    public function testAllowsSpecifyingEncodingAtConstruction()
    {
        $doc = new Document($this->getHtml(), null, 'iso-8859-1');
        $this->assertEquals('iso-8859-1', $doc->getEncoding());
    }

    /**
     * @group Laminas-3938
     */
    public function testAllowsSpecifyingEncodingWhenSettingDocument()
    {
        $this->document = new Document($this->getHtml(), null, 'iso-8859-1');
        $this->assertEquals('iso-8859-1', $this->document->getEncoding());
    }

    /**
     * @group Laminas-3938
     */
    public function testAllowsSpecifyingEncodingViaSetter()
    {
        $this->document->setEncoding('iso-8859-1');
        $this->assertEquals('iso-8859-1', $this->document->getEncoding());
    }

    /**
     * @group Laminas-3938
     */
    public function testSpecifyingEncodingSetsEncodingOnDomDocument()
    {
        $this->document = new Document($this->getHtml(), null, 'utf-8');
        $result         = Document\Query::execute('.foo', $this->document, Document\Query::TYPE_CSS);
        $this->assertInstanceOf(Document\NodeList::class, $result);
        $this->assertInstanceOf(DOMDocument::class, $this->document->getDomDocument());
        $this->assertEquals('utf-8', $this->document->getEncoding());
    }

    /**
     * @group Laminas-11376
     */
    public function testXhtmlDocumentWithXmlDeclaration()
    {
        $xhtmlWithXmlDecl = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head><title /></head>
    <body><p>Test paragraph.</p></body>
</html>
XML;
        $this->document   = new Document($xhtmlWithXmlDecl, null, 'utf-8');
        $result           = Document\Query::execute('//p', $this->document, Document\Query::TYPE_CSS);
        $this->assertEquals(1, $result->count());
    }

    /**
     * @group Laminas-12106
     */
    public function testXhtmlDocumentWithXmlAndDoctypeDeclaration()
    {
        $xhtmlWithXmlDecl = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Virtual Library</title>
  </head>
  <body>
    <p>Moved to <a href="http://example.org/">example.org</a>.</p>
  </body>
</html>
XML;
        $this->document   = new Document($xhtmlWithXmlDecl, null, 'utf-8');
        $result           = Document\Query::execute('//p', $this->document, Document\Query::TYPE_CSS);
        $this->assertEquals(1, $result->count());
    }

    public function testLoadingXmlContainingDoctypeShouldFailToPreventXxeAndXeeAttacks()
    {
        $xml            = <<<XML
<?xml version="1.0"?>
<!DOCTYPE results [<!ENTITY harmless "completely harmless">]>
<results>
    <result>This result is &harmless;</result>
</results>
XML;
        $this->document = new Document($xml);
        $this->expectException(RuntimeException::class);
        Document\Query::execute('/', $this->document);
    }

    public function testContextNode()
    {
        $this->loadHtml();
        $results     = Document\Query::execute('//div[@id="subnav"]', $this->document, Document\Query::TYPE_XPATH);
        $contextNode = $results[0];
        $results     = Document\Query::execute('.//li', $this->document, Document\Query::TYPE_XPATH, $contextNode);
        $this->assertSame('Item 1', $results[0]->nodeValue);
    }
}
