<?php

require 'customxmlelement.php';

$xml = '<root xmlns:dc="http://purl.org/dc/elements/1.1/">
    <dc:a>
      <dc:b>
        1.9
      </dc:b>
    </dc:a>
  </root>';

$root = new SimpleXMLElement($xml);

$root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
$a = $root->xpath('d:a');
$a[0]->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
$b = $a[0]->xpath('d:b');
echo trim((string) $b[0]);
echo "\n";

$root = new CustomXMLElement($xml);
$root->registerXPathNamespace('d', 'http://purl.org/dc/elements/1.1/');
$a = $root->xpath('d:a');
$b = $a[0]->xpath('d:b');
echo trim((string) $b[0]);
echo "\n";
