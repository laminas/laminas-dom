<?php

namespace Laminas\Dom;

use function sprintf;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Transform CSS selectors to XPath
 *
 * @deprecated
 *
 * @see Document\Query
 */
class Css2Xpath
{
    /**
     * Transform CSS expression to XPath
     *
     * @deprecated
     *
     * @see Document\Query
     *
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
