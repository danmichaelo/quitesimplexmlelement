<?php
/*
 * (c) Dan Michael O. Heggø (2013)
 *
 * QuiteSimpleXMLElement is a wrapper around SimpleXMLElement to add a quite simple
 * feature not present in SimpleXMLElement: inheritance of namespaces.
 *
 * My first attempt was to extend the original SimpleXMLElement class, but
 * unfortunately the constructor is static and cannot be overriden!
 *
 * It's easier to understand with a simple example:
 *
 *     $xml = '<root xmlns:dc="http://purl.org/dc/elements/1.1/">
 *         <dc:a>
 *           <dc:b >
 *             1.9
 *           </dc:a>
 *         </dc:b>
 *       </root>';
 *
 *     $root = new SimpleXMLElement($xml);
 *     $root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
 *     $a = $root->xpath('d:a');
 *     $a[0]->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
 *     $b = $a[0]->xpath('d:b');
 *     echo trim((string)$b[0]);
 *
 * Since namespaces are not inherited, we have to register them over and over again.
 * Using QuiteSimpleXMLElement instead;
 *
 *     $root = new QuiteSimpleXMLElement($xml);
 *     $root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
 *     $a = $root->xpath('d:a');
 *     $b = $a->xpath('d:b');
 *     echo trim((string)$b[0]);
 *
 * And while we're at it, we can add a few convenience methods.
 *
 * SimpleXmlElement reference:
 * https://github.com/draffter/FollowFunctionPHP/blob/master/_php/SimpleXML.php
 */

namespace Danmichaelo\QuiteSimpleXMLElement;

use Exception;
use InvalidArgumentException;
use SimpleXMLElement;

abstract class SimpleXMLElementWrapper
{
    /**
     * @var array
     */
    public $namespaces;

    /**
     * @var SimpleXMLElement
     */
    public $el;

    /**
     * QuiteSimpleXMLElement constructor.
     *
     * @param string|SimpleXMLElement|QuiteSimpleXMLElement    $elem
     * @param QuiteSimpleXMLElement                            $inherit_from
     * @throws InvalidXMLException
     * @throws InvalidArgumentException
     */
    public function __construct($elem, QuiteSimpleXMLElement $inherit_from = null)
    {
        $this->namespaces = [];
        $this->el = $this->getSimpleXMLElement($elem);

        if (is_null($this->el)) {
            throw new InvalidArgumentException('QuiteSimpleXMLElement expects a string or a QuiteSimpleXMLElement/SimpleXMLElement object.');
        }

        if (is_null($inherit_from)) {
            $this->namespaces = $this->el->getNamespaces(true);
        } else {
            foreach ($inherit_from->namespaces as $prefix => $uri) {
                $this->registerXPathNamespace($prefix, $uri);
            }
        }
    }

    /**
     * Internal helper method to get a SimpleXMLElement from either a string
     * or a SimpleXMLElement/QuiteSimpleXMLElement object.
     *
     * @param string|SimpleXMLElement|QuiteSimpleXMLElement $elem
     * @return SimpleXMLElement
     * @throws InvalidXMLException
     * @throws InvalidArgumentException
     */
    private function getSimpleXMLElement($elem)
    {
        if (gettype($elem) == 'string') {
            return $this->initFromString($elem);
        }

        if (gettype($elem) == 'object') {
            return $this->initFromObject($elem);
        }
    }

    /**
     * Internal helper method to parse content from string.
     *
     * @param string $content
     * @return SimpleXMLElement
     */
    private function initFromString($content)
    {
        try {
            return new SimpleXMLElement($content);
        } catch (Exception $e) {
            throw new InvalidXMLException('Invalid XML encountered: ' . $content);
        }
    }

    /**
     * Internal helper method to parse content from string.
     *
     * @param QuiteSimpleXMLElement|SimpleXMLElement $elem
     * @return SimpleXMLElement
     */
    private function initFromObject($elem)
    {
        switch (get_class($elem)) {
            case 'Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement':
                return $elem->el();
            case 'SimpleXMLElement':
                return $elem;
        }
    }

    /**
     * Register a new xpath namespace.
     *
     * @param string $prefix
     * @param string $uri
     */
    public function registerXPathNamespace($prefix, $uri)
    {
        $this->el->registerXPathNamespace($prefix, $uri);
        $this->namespaces[$prefix] = $uri;
    }

    /**
     * Register an array of new xpath namespaces.
     *
     * @param array $namespaces
     */
    public function registerXPathNamespaces($namespaces)
    {
        // Convenience method to add multiple namespaces at once
        foreach ($namespaces as $prefix => $uri) {
            $this->registerXPathNamespace($prefix, $uri);
        }
    }

    /**
     * Get the wrapped SimpleXMLElement object.
     *
     * @return SimpleXMLElement
     */
    public function el()
    {
        return $this->el;
    }

    /**
     * Returns the *untrimmed* text content of the node.
     * @return string
     */
    public function __toString()
    {
        return (string) $this->el;
    }

    /**
     * Returns child elements.
     *
     * Note: By default, only children without namespace will be returned! You can
     * specify a namespace prefix to get children with that namespace prefix.
     *
     * If you want all child elements, namespaced or not, use
     * `$record->all('child::*')` instead.
     *
     * @param null $ns
     * @return QuiteSimpleXMLElement[]
     */
    public function children($ns=null)
    {
        $ch = is_null($ns)
            ? $this->el->children()
            : $this->el->children($this->namespaces[$ns]);

        $o = [];
        foreach ($ch as $c) {
            $o[] = new static($c, $this);
        }

        return $o;
    }

    /**
     * Returns the number of child elements.
     *
     * Note: By default, only children without namespace will be counted! You can
     * specify a namespace prefix to count children with that namespace prefix.
     *
     * If you want all child elements, namespaced or not, use
     * `count($record->all('child::*'))` instead.
     *
     * @param null $ns
     * @return int
     */
    public function count($ns = null)
    {
        return count($this->children($ns));
    }

    /**
     * Returns and element's attributes.
     *
     * @param string $ns
     * @param bool $is_prefix
     * @return SimpleXMLElement a `SimpleXMLElement` object that can be
     * iterated over to loop through the attributes on the tag.
     */
    public function attributes($ns = null, $is_prefix = false)
    {
        return $this->el->attributes($ns, $is_prefix);
    }

    /**
     * Returns the XML as text.
     *
     * @return string
     */
    public function asXML()
    {
        return $this->el->asXML();
    }

    /**
     * Get the element name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->el->getName();
    }

    /**
     * Return a namespace array.
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Set the node value.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->el[0] = $value;
    }

    /**
     * Return the current object as DOMElement.
     *
     * @return \DOMElement
     */
    public function asDOMElement()
    {
        return dom_import_simplexml($this->el);
    }

    /**
     * Replaces the current node. Thanks to @hakre
     * <http://stackoverflow.com/questions/17661167/how-to-replace-xml-node-with-simplexmlelement-php>.
     *
     * @param QuiteSimpleXMLElement $element
     */
    public function replace(QuiteSimpleXMLElement $element)
    {
        $oldNode = $this->asDOMElement();
        $newNode = $oldNode->ownerDocument->importNode(
            $element->asDOMElement(),
            true
        );
        $oldNode->parentNode->replaceChild($newNode, $oldNode);
    }

    /**
     * Static helper method to make initialization easier.
     *
     * @param       $input
     * @param array $ns
     * @return QuiteSimpleXMLElement
     */
    public static function make($input, $ns = [])
    {
        $elem = new static($input);
        $elem->registerXPathNamespaces($ns);

        return $elem;
    }
}
