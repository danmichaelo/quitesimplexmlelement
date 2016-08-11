<?php namespace Danmichaelo\QuiteSimpleXMLElement;

class QuiteSimpleXMLElementTest extends \PHPUnit_Framework_TestCase
{
    public function testExampleXmlWithNamespace()
    {
        $xml = '
	 	  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
	 	     <ns1:CheckOutItemResponse>
	 	        <ns1:ItemId>
	 	           <ns1:AgencyId>x</ns1:AgencyId>
	 	           <ns1:ItemIdentifierValue>zzzzzzz</ns1:ItemIdentifierValue>
	 	        </ns1:ItemId>
	 	        <ns1:UserId>
	 	           <ns1:AgencyId>x</ns1:AgencyId>
	 	           <ns1:UserIdentifierValue>xxxxxxxxxx</ns1:UserIdentifierValue>
	 	        </ns1:UserId>
	 	        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>
	 	        <ns1:ItemOptionalFields>
	 	           <ns1:BibliographicDescription>
	 	              <ns1:Author>DuCharme, Bob</ns1:Author>
	 	              <ns1:BibliographicRecordId>
	 	                 <ns1:BibliographicRecordIdentifier>11447981x</ns1:BibliographicRecordIdentifier>
	 	                 <ns1:BibliographicRecordIdentifierCode>Accession Number</ns1:BibliographicRecordIdentifierCode>
	 	              </ns1:BibliographicRecordId>
	 	              <ns1:Edition/>
	 	              <ns1:Pagination>XIII, 235 s., ill.</ns1:Pagination>
	 	              <ns1:PublicationDate>2011</ns1:PublicationDate>
	 	              <ns1:Publisher>O\'Reilly</ns1:Publisher>
	 	              <ns1:Title>Learning SPARQL : querying and updating with SPARQL 1.1</ns1:Title>
	 	              <ns1:Language>eng</ns1:Language>
	 	              <ns1:MediumType>Book</ns1:MediumType>
	 	           </ns1:BibliographicDescription>
	 	        </ns1:ItemOptionalFields>
	 	        <ns1:Ext>
	 	           <ns1:UserOptionalFields>
	 	              <ns1:UserLanguage>eng</ns1:UserLanguage>
	 	           </ns1:UserOptionalFields>
	 	        </ns1:Ext>
	 	     </ns1:CheckOutItemResponse>
	 	  </ns1:NCIPMessage>';
        $dom = new QuiteSimpleXMLElement($xml);
        $ns = array('n' => 'http://www.niso.org/2008/ncip');
        $dom->registerXPathNamespaces($ns);

        $this->assertInstanceOf('Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement', $dom);
        $this->assertEquals('2013-09-21T18:54:39.718+02:00', $dom->text('/n:NCIPMessage/n:CheckOutItemResponse/n:DateDue'));
        $this->assertEquals('2013-09-21T18:54:39.718+02:00', $dom->first('/n:NCIPMessage/n:CheckOutItemResponse')->text('n:DateDue'));
        $this->assertEquals('2013-09-21T18:54:39.718+02:00', $dom->text('//n:DateDue'));

        // xpath should return a QuiteSimpleXMLElement element
        $this->assertInstanceOf('Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement', $dom->first('/n:NCIPMessage'));

        // and we should get the SimpleXMLElement from el()
        $this->assertInstanceOf('SimpleXMLElement', $dom->first('/n:NCIPMessage')->el());
    }

    public function testExampleXmlWithDefaultNamespacePrefix()
    {
        $xml = '
		  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
		     <ns1:CheckOutItemResponse>
		        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>
		     </ns1:CheckOutItemResponse>
		  </ns1:NCIPMessage>';
        $dom = new QuiteSimpleXMLElement($xml);

        $this->assertInstanceOf('Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement', $dom);
        $this->assertEquals('2013-09-21T18:54:39.718+02:00', $dom->text('/ns1:NCIPMessage/ns1:CheckOutItemResponse/ns1:DateDue'));
    }

    public function testAsXML()
    {
        $xml = '
		  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
		     <ns1:CheckOutItemResponse>
		        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>
		     </ns1:CheckOutItemResponse>
		  </ns1:NCIPMessage>';
        $dom = new QuiteSimpleXMLElement($xml);

        $this->assertXmlStringEqualsXmlString($xml, $dom->asXML());
    }

    public function testGetName()
    {
        $xml = '
		  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
		     <ns1:CheckOutItemResponse>
		        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>
		     </ns1:CheckOutItemResponse>
		  </ns1:NCIPMessage>';
        $root = new QuiteSimpleXMLElement($xml);
        $node = $root->first('/ns1:NCIPMessage/ns1:CheckOutItemResponse');

        $this->assertEquals('NCIPMessage', $root->getName());
        $this->assertEquals('CheckOutItemResponse', $node->getName());
    }

    public function testAttr()
    {
        $xml = '
			<sear:SEGMENTS xmlns:sear="http://www.exlibrisgroup.com/xsd/jaguar/search">
				<sear:FACETLIST ACCURATE_COUNTERS="true">
					<sear:FACET NAME="creator" COUNT="200">
						Test
					</sear:FACET>
				</sear:FACETLIST>
			</sear:SEGMENTS>';
        $root = new QuiteSimpleXMLElement($xml);
        $node = $root->first('/sear:SEGMENTS/sear:FACETLIST/sear:FACET');

        $this->assertEquals('creator', $node->attr('NAME'));
        $this->assertEquals('200', $node->attr('COUNT'));
        $this->assertEquals('', $node->attr('SOMEETHING_ELSE'));
    }

    public function testText()
    {
        $xml = '
			<sear:SEGMENTS xmlns:sear="http://www.exlibrisgroup.com/xsd/jaguar/search">
				<sear:FACETLIST ACCURATE_COUNTERS="true">
					<sear:FACET NAME="creator" COUNT="200">
						Test
					</sear:FACET>
				</sear:FACETLIST>
			</sear:SEGMENTS>';
        $root = new QuiteSimpleXMLElement($xml);
        $node = $root->first('/sear:SEGMENTS/sear:FACETLIST/sear:FACET');

        $this->assertEquals('Test', $node->text());
        $this->assertEquals('Test', $root->text('/sear:SEGMENTS/sear:FACETLIST/sear:FACET'));
    }

    public function testUntrimmedText()
    {
        $xml = '
            <sear:SEGMENTS xmlns:sear="http://www.exlibrisgroup.com/xsd/jaguar/search">
                <sear:FACETLIST ACCURATE_COUNTERS="true">
                    <sear:FACET NAME="creator" COUNT="200">
                        Test
                    </sear:FACET>
                </sear:FACETLIST>
            </sear:SEGMENTS>';
        $root = new QuiteSimpleXMLElement($xml);
        $node = $root->first('/sear:SEGMENTS/sear:FACETLIST/sear:FACET');

        $this->assertEquals('
                        Test
                    ', $node->text('.', false));
    }

    public function testTextOfNonExistingNode()
    {
        $xml = '
            <sear:SEGMENTS xmlns:sear="http://www.exlibrisgroup.com/xsd/jaguar/search">
                <sear:FACETLIST ACCURATE_COUNTERS="true">
                    <sear:FACET NAME="creator" COUNT="200">
                        Test
                    </sear:FACET>
                </sear:FACETLIST>
            </sear:SEGMENTS>';
        $root = new QuiteSimpleXMLElement($xml);

        $this->assertEquals('', $root->text('/sear:NONEXISTING/sear:NONEXISTING'));
    }

    public function testSetValue()
    {
        $xml = '
            <sear:SEGMENTS xmlns:sear="http://www.exlibrisgroup.com/xsd/jaguar/search">
                <sear:FACETLIST ACCURATE_COUNTERS="true">
                    <sear:FACET NAME="creator" COUNT="200">
                        Test
                    </sear:FACET>
                </sear:FACETLIST>
            </sear:SEGMENTS>';
        $root = new QuiteSimpleXMLElement($xml);
        $node = $root->first('/sear:SEGMENTS/sear:FACETLIST/sear:FACET');
        $node->setValue('Hello');

        $this->assertEquals('Hello', $root->text('/sear:SEGMENTS/sear:FACETLIST/sear:FACET'));
        $this->assertEquals('creator', $root->first('/sear:SEGMENTS/sear:FACETLIST/sear:FACET')->attr('NAME'));
    }



    public function testHas()
    {
        $xml = '
			<sear:SEGMENTS xmlns:sear="http://www.exlibrisgroup.com/xsd/jaguar/search">
				<sear:FACETLIST ACCURATE_COUNTERS="true">
					<sear:FACET NAME="creator" COUNT="200">
						Test
					</sear:FACET>
				</sear:FACETLIST>
			</sear:SEGMENTS>';
        $root = new QuiteSimpleXMLElement($xml);
        $node1 = $root->first('/sear:SEGMENTS/sear:FACETLIST');
        $node2 = $root->first('/sear:SEGMENTS');

        $this->assertTrue($node1->has('sear:FACET'));
        $this->assertFalse($node1->has('sear:OCTET'));
        $this->assertTrue($node2->has('sear:FACETLIST'));
        $this->assertTrue($node2->has('sear:FACETLIST/sear:FACET'));
        $this->assertFalse($node2->has('sear:NA/sear:NA'));
    }

    public function testGetNamespaces()
    {
        $xml = '
		  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
		     <ns1:CheckOutItemResponse>
		        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>
		     </ns1:CheckOutItemResponse>
		  </ns1:NCIPMessage>';
        $root = new QuiteSimpleXMLElement($xml);

        $this->assertEquals(array('ns1' => 'http://www.niso.org/2008/ncip'), $root->getNamespaces());
    }

    public function testChildCount()
    {
        $xml = '
		  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
		     <ns1:CheckOutItemResponse>
		        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>
		     </ns1:CheckOutItemResponse>
		  </ns1:NCIPMessage>';
        $dom = new QuiteSimpleXMLElement($xml);
        $node1 = $dom->first('/ns1:NCIPMessage');
        $node2 = $dom->first('/ns1:NCIPMessage/ns1:CheckOutItemResponse/ns1:DateDue');
        $this->assertEquals(1, $node1->count('ns1'));
        $this->assertEquals(0, $node2->count('ns1'));
    }

    public function testChildCountWithRegisteredNamespaces()
    {
        $xml = '
	 	  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
	 	     <ns1:CheckOutItemResponse>
	 	        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>
	 	     </ns1:CheckOutItemResponse>
	 	  </ns1:NCIPMessage>';
        $dom = new QuiteSimpleXMLElement($xml);
        $ns = array('n' => 'http://www.niso.org/2008/ncip');
        $dom->registerXPathNamespaces($ns);

        $node1 = $dom->first('/n:NCIPMessage');
        $node2 = $dom->first('/n:NCIPMessage/n:CheckOutItemResponse/n:DateDue');
        $this->assertEquals(1, $node1->count('n'));
        $this->assertEquals(0, $node2->count('n'));
    }

    public function testChildren()
    {
        $xml = '
		  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
		     <ns1:CheckOutItemResponse>
		        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>
		     </ns1:CheckOutItemResponse>
		  </ns1:NCIPMessage>';
        $root = new QuiteSimpleXMLElement($xml);
        $node1 = $root->first('/ns1:NCIPMessage');
        $kids = $node1->children('ns1');

        $this->assertCount(1, $kids);
        $kiddo = $kids[0];
        $this->assertInstanceOf('Danmichaelo\QuiteSimpleXMLElement\QuiteSimpleXMLElement', $kiddo);
        $this->assertEquals('CheckOutItemResponse', $kiddo->getName());
        $this->assertEquals('2013-09-21T18:54:39.718+02:00', $kiddo->text('ns1:DateDue'));
    }

    public function testAll()
    {
        $xml = '
          <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
             <ns1:Test>
                Hello
             </ns1:Test>
          </ns1:NCIPMessage>';
        $dom = new QuiteSimpleXMLElement($xml);

        $this->assertCount(0, $dom->all('/Test[prop="val"]'));
        $this->assertCount(1, $dom->all('/ns1:NCIPMessage/ns1:Test'));
    }

    /**
     * @expectedException Danmichaelo\QuiteSimpleXMLElement\InvalidXMLException
     */
    public function testParseErrorneousXML()
    {
        $xml = '<ns1:NCI';
        new QuiteSimpleXMLElement($xml);
    }

    /**
     * @expectedException Danmichaelo\QuiteSimpleXMLElement\InvalidXMLException
     */
    public function testParseEmptyXML()
    {
        $xml = '';
        new QuiteSimpleXMLElement($xml);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testArgumentIsNull()
    {
        $dom = new QuiteSimpleXMLElement(null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testArgumentOfUnknownType()
    {
        $dom = new QuiteSimpleXMLElement(2.0);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testArgumentOfUnknownClass()
    {
        $dom = new QuiteSimpleXMLElement(new \DateTime());
    }

    public function testReplace()
    {
        $xml = '
          <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
             <ns1:Test>
                Hello
             </ns1:Test>
             <ns1:Test>
                Hi
             </ns1:Test>
          </ns1:NCIPMessage>';
        $expectedResult= '
          <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
             <ns1:Test2>Replaced</ns1:Test2>
             <ns1:Test>
                Hi
             </ns1:Test>
          </ns1:NCIPMessage>';
        $el = new QuiteSimpleXMLElement($xml);
        $node = $el->first('ns1:Test');

        $new = new QuiteSimpleXMLElement('<ns1:Test2 xmlns:ns1="http://www.niso.org/2008/ncip">Replaced</ns1:Test2>');
        $node->replace($new);

        $this->assertXmlStringEqualsXmlString($expectedResult, $el->asXML());
    }

    public function testMake()
    {
        $el = QuiteSimpleXMLElement::make('<doc xmlns="http://www.loc.gov/zing/srw/"><hello>world</hello></doc>', array(
            's' => 'http://www.loc.gov/zing/srw/',
        ));

        $this->assertEquals('world', $el->text('s:hello'));
    }

    public function testToString()
    {
        $el = QuiteSimpleXMLElement::make('<doc xmlns="http://www.loc.gov/zing/srw/"><hello>world</hello></doc>', array(
            's' => 'http://www.loc.gov/zing/srw/',
        ));

        $this->assertEquals('world', strval($el->first('s:hello')));
    }

    public function testAttributes()
    {
        $el = QuiteSimpleXMLElement::make('<doc xmlns="http://www.loc.gov/zing/srw/"><hello here="there">world</hello></doc>', array(
            's' => 'http://www.loc.gov/zing/srw/',
        ));

        $this->assertEquals(array('here' => 'there'), iterator_to_array($el->first('s:hello')->attributes()));
    }

    public function testFirstReturnsNullForNonexistingNode()
    {
        $el = QuiteSimpleXMLElement::make('<doc xmlns="http://www.loc.gov/zing/srw/"><hello>first</hello><hello>second</hello></doc>', array(
            's' => 'http://www.loc.gov/zing/srw/',
        ));

        $this->assertSame(null, $el->first('world'));
    }
}
