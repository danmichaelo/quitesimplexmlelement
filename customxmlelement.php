<?php
/* 
 * (c) Dan Michael O. Heggø (2013)
 * 
 * CustomXMLElement is a wrapper around SimpleXMLElement to add a quite simple
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
 * Using CustomXMLElement instead;
 *
 *     $root = new CustomXMLElement($xml);
 *     $root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
 *     $a = $root->xpath('d:a');
 *     $b = $a->xpath('d:b');
 *     echo trim((string)$b[0]);
 * 
 * And while we're at it, we can add a few convenience methods...
 */

class XmlResultSet {
    public $xmlObjs = array();

    public function __construct(array $xmlFiles) {
        foreach ($xmlFiles as $file) {
            $this->xmlObjs[] = new XmlResult($file);
        }
    }
}

class XmlResult
{
    private $xmlObj;

    public function __construct($file)
    {
        try {
            $this->xmlObj = new SimpleXMLElement($file, 0, true);
        }
        catch (Exception $e) {
            throw new MyException("Invalid argument ($this)($file)(" . $e .
            ")", PHP_ERRORS);
        }
    }

    public function otherFunctions()
    {
        return $this->xmlObj->movie['name']; // whatever
    }
}

class CustomXMLElement {

    #
    # See https://github.com/draffter/FollowFunctionPHP/blob/master/_php/SimpleXML.php
    # for list of functions with arguments
    #
    function __construct($elem, $inherit_from=null) {
        /*
        $ns = array(
            'srw' => 'http://www.loc.gov/zing/srw/',
            'marc' => 'http://www.loc.gov/MARC21/slim'
        );*/
        $this->namespaces = array();
        if (gettype($elem) == 'string') {
            $this->el = new SimpleXMLElement($elem);
        } else {
            $this->el = $elem; // assume it's a SimpleXMLElement
        }
        if ($inherit_from != null) {
            foreach ($inherit_from->namespaces as $prefix => $uri) {
                $this->registerXPathNamespace($prefix, $uri);
            }
        }

        /*
        $this->el->registerXPathNamespace('srw', $ns['srw']);
        $this->el->registerXPathNamespace('marc', $ns['marc']);*/
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
        return (count($x) === 0) ? false : new CustomXMLElement($x[0], $this);
    }

    function el() {
        return $this->el;
    }

    function xpath($path) {
        $r = $this->el->xpath($path);
        if ($r === false) return false;
        $r2 = array();
        foreach ($r as $i) {
            $r2[] = new CustomXMLElement($i, $this);
        }
        return $r2;
    }

    function __toString() {
        return (string)$this->el;
    }

    function children() { return $this->el->children(); }
    function attributes() { return $this->el->attributes(); }

}
