<?php

namespace Danmichaelo\QuiteSimpleXMLElement;

use SimpleXMLElement;

trait HelperMethods
{
    /**
     * @var SimpleXMLElement
     */
    public $el;

    /**
     * Get a node attribute value.
     *
     * @param string $attribute
     * @return string
     */
    public function attr($attribute)
    {
        return trim((string) $this->el->attributes()->{$attribute});
    }

    /**
     * Get the first node matching an XPath query, or null if no match.
     *
     * @param string $path
     * @return QuiteSimpleXMLElement
     */
    public function first($path)
    {
        // Convenience method
        $x = $this->xpath($path);

        return count($x) ? $x[0] : null;
    }

    /**
     * Check if the document has at least one node matching an XPath query.
     *
     * @param string $path
     * @return bool
     */
    public function has($path)
    {
        $x = $this->xpath($path);

        return count($x) ? true : false;
    }

    /**
     * Get all nodes matching an XPath query.
     *
     * @param string $path
     * @return QuiteSimpleXMLElement[]
     */
    public function xpath($path)
    {
        return array_map(function ($el) {
            return new QuiteSimpleXMLElement($el, $this);
        }, $this->el->xpath($path));
    }

    /**
     * Alias for `xpath()`.
     *
     * @param $path
     * @return QuiteSimpleXMLElement[]
     */
    public function all($path)
    {
        return $this->xpath($path);
    }


}