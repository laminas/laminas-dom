# Querying HTML and XML Documents

`Laminas\Dom\Query` provides mechanisms for querying XML and HTML documents
utilizing either XPath or CSS selectors. It was developed to aid with functional
testing of MVC applications, but could also be used for development of screen
scrapers.

CSS selector notation is provided as a simpler and more familiar notation for
web developers to utilize when querying documents with XML structures. The
notation should be familiar to anybody who has developed Cascading Style Sheets
or who utilizes javascript toolkits that provide functionality for selecting
nodes utilizing CSS selectors.  [Prototype's $$()](http://prototypejs.org/api/utility/dollar-dollar),
[Dojo's dojo.query](http://api.dojotoolkit.org/jsdoc/dojo/HEAD/dojo.query), and
[jQuery](https://jquery.com) were all inspirations for the component.

## Theory of Operation

To use `Laminas\Dom\Query`, you instantiate a `Laminas\Dom\Query` object, optionally
passing a document to query (a string). Once you have a document, you can use
either the `execute()` or `queryXpath()` methods; each method will return a
`Laminas\Dom\NodeList` object with any matching nodes.

The primary difference between `Laminas\Dom\Query` and using
[DOMDocument](http://php.net/domdocument) + [DOMXPath](http://php.net/domxpath)
is the ability to select against CSS + selectors. You can utilize any of the
following, in any combination:

- **element types**: provide an element type to match: `div`, `a`, `span`, `h2`, etc.
- **style attributes**: CSS style attributes to match: `.error`, `div.error`,
  `label.required`, etc. If an element defines more than one style, this will
  match as long as the named style is present anywhere in the style declaration.
- **id attributes**: element ID attributes to match: `#content`, `div#nav`, etc.
- **arbitrary attributes**: arbitrary element attributes to match. Three
  different types of matching are provided:
  - **exact match**: the attribute *exactly* matches the specified string.
    `div[bar="baz"]` would match a `div` element with a `bar` attribute that
    exactly matches the value `baz`.
    - **word match**: the attribute contains a *word* matching the string.
      `div[bar~="baz"]` would match a `div` element with a `bar` attribute that
      contains the word `baz`. `<div bar="foo baz">` would match, but
      `<div bar="foo bazbat">` would not.
    - **substring match**: the attribute contains the string specified, whether or
      not it is a complete word. `div[bar*="baz"]` would match a `div` element
      with a `bar` attribute that contains the string `baz` anywhere within it.
- **direct descendents**: utilize `>` between selectors to denote direct
  descendents. `div > span` would select only `span` elements that are direct
  descendents of a `div`. Can also be used with any of the selectors above.
- **descendents**: string together multiple selectors to indicate a hierarchy along which to search.
  `div .foo span #one` would select an element of id `one` that is a descendent
  of arbitrary depth beneath a `span` element, which is in turn a descendent of
  arbitrary depth beneath an element with a class of `foo`, that is an
  descendent of arbitrary depth beneath a `div` element. For example, it would
  match the link to the word 'One' in the listing below:

    ```html
    <div>
    <table>
        <tr>
            <td class="foo">
                <div>
                    Lorem ipsum <span class="bar">
                        <a href="/foo/bar" id="one">One</a>
                        <a href="/foo/baz" id="two">Two</a>
                        <a href="/foo/bat" id="three">Three</a>
                        <a href="/foo/bla" id="four">Four</a>
                    </span>
                </div>
            </td>
        </tr>
    </table>
    </div>
    ```

Once you've performed your query, you can then work with the result object to
determine information about the nodes, as well as to pull them and/or their
content directly for examination and manipulation. `Laminas\Dom\NodeList`
implements `Countable` and `Iterator`, and stores the results internally as a
[DOMDocument](http://php.net/domdocument) and [DOMNodeList](http://php.net/domnodelist).

As an example, consider the following call, that selects against the HTML above:

```php
use Laminas\Dom\Query;

$dom = new Query($html);
$results = $dom->execute('.foo .bar a');

$count = count($results); // get number of matches: 4
foreach ($results as $result) {
    // $result is a DOMElement
}
```

`Laminas\Dom\Query` also allows straight XPath queries utilizing the `queryXpath()`
method; you can pass any valid XPath query to this method, and it will return a
`Laminas\Dom\NodeList` object.

## Methods Available

Below is a listing of methods available in the various classes exposed by
laminas-dom.

### Laminas\\Dom\\Query

The following methods are available to `Laminas\Dom\Query`:

- `setDocumentXml($document, $encoding = null)`: specify an XML string to query against.
- `setDocumentXhtml($document, $encoding = null)`: specify an XHTML string to query against.
- `setDocumentHtml($document, $encoding = null)`: specify an HTML string to query against.
- `setDocument($document, $encoding = null)`: specify a string to query against;
  `Laminas\Dom\Query` will then attempt to autodetect the document type.
- `setEncoding($encoding)`: specify an encoding string to use. This encoding
  will be passed to [DOMDocument's constructor](http://php.net/domdocument.construct)
  if specified.
- `getDocument()`: retrieve the original document string provided to the object.
- `getDocumentType()`: retrieve the document type of the document provided to
  the object; will be one of the `DOC_XML`, `DOC_XHTML`, or `DOC_HTML` class
  constants.
- `getEncoding()`: retrieves the specified encoding.
- `execute($query)`: query the document using CSS selector notation.
- `queryXpath($xPathQuery)`: query the document using XPath notation.

### Laminas\\Dom\\NodeList

As mentioned previously, `Laminas\Dom\NodeList` implements both `Iterator` and
`Countable`, and as such can be used in a `foreach()` loop as well as with the
`count()` function. Additionally, it exposes the following methods:

- `getCssQuery()`: return the CSS selector query used to produce the result (if
  any).
- `getXpathQuery()`: return the XPath query used to produce the result.
  Internally, `Laminas\Dom\Query` converts CSS selector queries to XPath, so this
  value will always be populated.
- `getDocument()`: retrieve the DOMDocument the selection was made against.
