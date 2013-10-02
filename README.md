QuiteSimpleXMLElement
===============

[![Build Status](https://travis-ci.org/danmichaelo/quitesimplexmlelement.png?branch=master)](https://travis-ci.org/danmichaelo/quitesimplexmlelement)
[![Coverage Status](https://coveralls.io/repos/danmichaelo/quitesimplexmlelement/badge.png?branch=master)](https://coveralls.io/r/danmichaelo/quitesimplexmlelement?branch=master)
[![Latest Stable Version](https://poser.pugx.org/danmichaelo/quitesimplexmlelement/version.png)](https://packagist.org/packages/danmichaelo/quitesimplexmlelement)
[![Total Downloads](https://poser.pugx.org/danmichaelo/quitesimplexmlelement/downloads.png)](https://packagist.org/packages/danmichaelo/quitesimplexmlelement)


The `QuiteSimpleXMLElement` class is a small wrapper around the `SimpleXMLElement` class. It was formerly known as `CustomXMLElement`. The main reason for developing the class was to let objects returned by the `xpath()`
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

QuiteSimpleXMLElement allows for slightly less typing:

    $root = new QuiteSimpleXMLElement($xml);
    $root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
    $a = $root->xpath('d:a');
    $b = $a->xpath('d:b');
    echo trim((string)$b[0]);

A note on the design: I would have preferred to extend the original SimpleXMLElement class, but the constructor is static, which is why I wrote a wrapper instead.

There's also a few new convenience methods added, such as `text()`.

    $root = new QuiteSimpleXMLElement($xml);
    $root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
    echo $root->text('d:a/d:b');
