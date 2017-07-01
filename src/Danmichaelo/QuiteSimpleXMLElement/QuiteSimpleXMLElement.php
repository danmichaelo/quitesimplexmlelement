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

class InvalidXMLException extends Exception
{
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class QuiteSimpleXMLElement
{
    public $namespaces;
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

        $this->el = $this->getElement($elem);

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
    protected function getElement($elem)
    {
        if (gettype($elem) == 'string') {
            try {
                return new SimpleXMLElement($elem);
            } catch (Exception $e) {
                throw new InvalidXMLException('Invalid XML encountered: ' . $elem);
            }
        }

        if (gettype($elem) == 'object') {
            if (in_array(get_class($elem),
                ['Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement', 'SimpleXMLElement'])) {
                return $elem; // assume it's a SimpleXMLElement
            } else {
                throw new InvalidArgumentException('Unknown object given to QuiteSimpleXMLElement. Expected SimpleXMLElement or QuiteSimpleXMLElement.');
            }
        }

        throw new InvalidArgumentException('QuiteSimpleXMLElement expects a string or a QuiteSimpleXMLElement/SimpleXMLElement object.');
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
     * Get the text of the first node matching an XPath query. By default,
     * the text will be trimmed, but if you want the untrimmed text, set
     * the second paramter to False.
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
     * Get the wrapped SimpleXMLElement object.
     *
     * @return SimpleXMLElement
     */
    public function el()
    {
        return $this->el;
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
     * Returns the *untrimmed* text content of the node.
     * @return string
     */
    public function __toString()
    {
        return (string) $this->el;
    }

    /**
     * Returns the child elements.
     *
     * Note: By default, only children without namespace will be returned. You can
     * specify a namespace prefix to get children with that namespace prefix.
     *
     * Tip: You could also use `xpath('child::node()')`.
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
            $o[] = new self($c, $this);
        }

        return $o;
    }

    /**
     * Returns the number of child elements.
     *
     * Note: By default, only children without namespace will be counted. You can
     * specify a namespace prefix to count children with that namespace prefix.
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
     * @return SimpleXMLElement a `SimpleXMLElement` object that can be
     * iterated over to loop through the attributes on the tag.
     */
    public function attributes()
    {
        return $this->el->attributes();
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
        $elem = new self($input);
        $elem->registerXPathNamespaces($ns);

        return $elem;
    }
}
