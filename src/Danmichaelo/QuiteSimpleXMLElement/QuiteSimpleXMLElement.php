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
 * And while we're at it, we can add a few convenience methods...
 */

namespace Danmichaelo\QuiteSimpleXMLElement;

class InvalidXMLException extends \Exception
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

    #
    # See https://github.com/draffter/FollowFunctionPHP/blob/master/_php/SimpleXML.php
    # for list of functions with arguments
    #
    public function __construct($elem, $inherit_from=null)
    {
        $this->namespaces = array();

        if (gettype($elem) == 'string') {
            try {
                $this->el = new \SimpleXMLElement($elem);
            } catch (\Exception $e) {
                throw new InvalidXMLException("Invalid XML encountered: " . $elem);
            }
        } elseif (gettype($elem) == 'object') {
            if (in_array(get_class($elem), array('Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement', 'SimpleXMLElement'))) {
                $this->el = $elem; // assume it's a SimpleXMLElement
            } else {
                throw new \InvalidArgumentException('Unknown object given to QuiteSimpleXMLElement. Expected SimpleXMLElement or QuiteSimpleXMLElement.');
            }
        } else {
            throw new \InvalidArgumentException('QuiteSimpleXMLElement expects a string or a QuiteSimpleXMLElement/SimpleXMLElement object.');
        }

        if ($inherit_from != null) {
            foreach ($inherit_from->namespaces as $prefix => $uri) {
                $this->registerXPathNamespace($prefix, $uri);
            }
        } else {
            $this->namespaces = $this->el->getNamespaces(true);
        }
    }

    public function registerXPathNamespace($prefix, $uri)
    {
        $this->el->registerXPathNamespace($prefix, $uri);
        $this->namespaces[$prefix] = $uri;
    }

    public function registerXPathNamespaces($namespaces)
    {
        # Convenience method to add multiple namespaces at once
        foreach ($namespaces as $prefix => $uri) {
            $this->registerXPathNamespace($prefix, $uri);
        }
    }

    /*
     * Convenience method for getting the text of the first
     * node matching an xpath. The text is trimmed by default,
     * but setting the second argument to false will return
     * the untrimmed text.
     */
    public function text($path = '.', $trim=true)
    {
        $text = strval($this->first($path));

        return $trim ? trim($text) : $text;
    }

    /*
     * Convenience method for getting an attribute of a node
     */
    public function attr($attribute)
    {
        return trim((string) $this->el->attributes()->{$attribute});
    }

    public function first($path)
    {
        # Convenience method
        $x = $this->xpath($path);

        return count($x) ? $x[0] : null;
    }

    /*
     * Convenience method for checking if a node exists
     */
    public function has($path)
    {
        $x = $this->xpath($path);

        return count($x) ? true : false;
    }

    public function el()
    {
        return $this->el;
    }

    /**
     * @param $path
     * @return bool|QuiteSimpleXMLElement[]
     */
    public function xpath($path)
    {
        $r = $this->el->xpath($path);

        $r2 = array();
        foreach ($r as $i) {
            $r2[] = new QuiteSimpleXMLElement($i, $this);
        }

        return $r2;
    }

    /**
     * Wrapper method for xpath() that *always* returns an array.
     *
     * @param $path
     * @return QuiteSimpleXMLElement[]
     */
    public function all($path)
    {
        $r = $this->xpath($path);
        return (!is_array($r)) ? array() : $r;
    }

    /**
     * Returns the *untrimmed* text content of the node
     */
    public function __toString()
    {
        return (string) $this->el;
    }

    /* The original children and count methods are quite flawed. The count() method
       only return the count of children _with no namespace_. The children() method
       can take namespace prefix as argument, but doesn't use the document's prefixes,
       not the registered ones.

       And it returns a "pseudo array" instead of a real iterator... making it quite hard
       to work with, there's no next() method for instance...
       We're returning a real array instead, even though that might not be what you want
       in _all_ situations.

       An alternative could be to use xpath('child::node()')
    */
    public function children($ns = null)
    {
        $ch = $ns
            ? $this->el->children($this->namespaces[$ns])
            : $this->el->children();
        $o = array();
        foreach ($ch as $c) {
            $o[] = new QuiteSimpleXMLElement($c, $this);
        }

        return $o;
    }
    public function count($ns = null)
    {
        return $ns
            ? count($this->el->children($this->namespaces[$ns]))
            : count($this->el->children());
    }

    public function attributes()
    {
        return $this->el->attributes();
    }
    public function asXML()
    {
        return $this->el->asXML();
    }
    public function getName()
    {
        return $this->el->getName();
    }
    public function getNamespaces($recursive = false)
    {
        return $this->el->getNamespaces($recursive);
    }

    /**
     * Set the node value
     */
    public function setValue($value)
    {
        $this->el[0] = $value;
    }

    public function asDOMElement()
    {
        return dom_import_simplexml($this->el);
    }

    /**
     * Replaces the current node. Thanks to @hakre
     * <http://stackoverflow.com/questions/17661167/how-to-replace-xml-node-with-simplexmlelement-php>
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

    public static function make($input, $ns = array())
    {
        $elem = new QuiteSimpleXMLElement($input);
        $elem->registerXPathNamespaces($ns);
        return $elem;
    }
}
