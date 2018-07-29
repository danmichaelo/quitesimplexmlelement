<?php
/**
 * This file contains additional helper methods.
 */
namespace Danmichaelo\QuiteSimpleXMLElement;

class QuiteSimpleXMLElement extends SimpleXMLElementWrapper
{
    /**
     * Get a node attribute value. Namespace prefixes are supported.
     *
     * @param string $attribute
     * @return string
     */
    public function attr($attribute)
    {
        if (strpos($attribute, ':') !== false) {
            list($ns, $attribute) = explode(':', $attribute, 2);

            return trim((string) $this->el->attributes($ns, true)->{$attribute});
        }
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

    /**
     * Get the text of the first node matching an XPath query. By default,
     * the text will be trimmed, but if you want the untrimmed text, set
     * the second parameter to False.
     *
     * @param string $path
     * @param bool   $trim
     * @return string
     */
    public function text($path='.', $trim=true)
    {
        $text = strval($this->first($path));

        return $trim ? trim($text) : $text;
    }
}
