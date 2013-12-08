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

    public function __construct($message = null, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}

class QuiteSimpleXMLElement {

    public $namespaces;
    public $el;

    #
    # See https://github.com/draffter/FollowFunctionPHP/blob/master/_php/SimpleXML.php
    # for list of functions with arguments
    #
    function __construct($elem, $inherit_from=null) {

        $this->namespaces = array();

        if (gettype($elem) == 'string') {
            try {
                $this->el = new \SimpleXMLElement($elem);
            } catch (\Exception $e) {
                throw new InvalidXMLException("Invalid XML encountered: " . $elem);
            }
        } else if (gettype($elem) == 'object') {
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
        }

    }

    function registerXPathNamespace($prefix, $uri) {
        $this->el->registerXPathNamespace($prefix, $uri);
        $this->namespaces[$prefix] = $uri;
    }

    function registerXPathNamespaces($namespaces) {
        # Convenience method to add multiple namespaces at once
        foreach ($namespaces as $prefix => $uri) {
            $this->registerXPathNamespace($prefix, $uri);            
        }
    }

    function text($path) {
        # Convenience method 
        $r = $this->el->xpath($path);
        if ($r === false) return '';    // in case of an error
        if (count($r) === 0) return ''; // no results
        return trim((string) $r[0]);
    }

    function first($path) {
        # Convenience method 
        $x = $this->xpath($path);
        return (count($x) === 0) 
            ? false 
            : $x[0];
    }

    function el() {
        return $this->el;
    }

    function xpath($path) {
        $r = $this->el->xpath($path);
        if ($r === false) return false;
        $r2 = array();
        foreach ($r as $i) {
            $r2[] = new QuiteSimpleXMLElement($i, $this);
        }
        return $r2;
    }

    function __toString() {
        return (string)$this->el;
    }

    function children() { return $this->el->children(); }
    function attributes() { return $this->el->attributes(); }

}