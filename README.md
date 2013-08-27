CustomXMLElement
===============

[![Build Status](https://travis-ci.org/danmichaelo/customxmlelement.png?branch=master)](https://travis-ci.org/danmichaelo/customxmlelement)
[![Coverage Status](https://coveralls.io/repos/danmichaelo/customxmlelement/badge.png?branch=master)](https://coveralls.io/r/danmichaelo/customxmlelement?branch=master)


The `CustomXMLElement` class is a small wrapper around the `SimpleXMLElement` class. The main reason for developing the class was to let objects returned by the `xpath()`
method inherit namespaces from the original object.

Taking an example document, 

    $xml = '<root xmlns:dc="http://purl.org/dc/elements/1.1/">
        <dc:a>
          <dc:b >
            1.9
          </dc:a>
        </dc:b>
      </root>';

Using SimpleXMLElement I found myself having to register namespaces over and over again:

    $root = new SimpleXMLElement($xml);
    $root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
    $a = $root->xpath('d:a');
    $a[0]->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
    $b = $a[0]->xpath('d:b');
    echo trim((string)$b[0]);

CustomXMLElement allows for slightly less typing:

    $root = new CustomXMLElement($xml);
    $root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
    $a = $root->xpath('d:a');
    $b = $a->xpath('d:b');
    echo trim((string)$b[0]);

A note on the design: I would have preferred to extend the original SimpleXMLElement class, but the constructor is static, which is why I wrote a wrapper instead.

There's also a few new convenience methods added, such as `text()`.

    $root = new CustomXMLElement($xml);
    $root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
    echo $root->text('d:a/d:b');
