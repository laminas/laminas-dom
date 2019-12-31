<?php

/**
 * @see       https://github.com/laminas/laminas-dom for the canonical source repository
 * @copyright https://github.com/laminas/laminas-dom/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-dom/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Dom;

/**
 * Transform CSS selectors to XPath
 *
 * @deprecated
 * @see Document\Query
 */
class Css2Xpath
{
    /**
     * Transform CSS expression to XPath
     *
     * @deprecated
     * @see Document\Query
     * @param  string $path
     * @return string
     */
    public static function transform($path)
    {
        trigger_error(
            sprintf(
                '%s is deprecated; please use %s\Document\Query::cssToXpath instead',
                __METHOD__,
                __NAMESPACE__
            ),
            E_USER_DEPRECATED
        );
        return Document\Query::cssToXpath($path);
    }
}
