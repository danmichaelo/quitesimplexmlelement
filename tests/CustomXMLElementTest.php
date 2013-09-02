<?php namespace Danmichaelo\CustomXMLElement;

class CustomXMLElementTest extends \PHPUnit_Framework_TestCase {
	
	public function testExampleXmlWithNamespace() {
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
		$dom = new CustomXMLElement($xml);
		$ns = array('n' => 'http://www.niso.org/2008/ncip');
		$dom->registerXPathNamespaces($ns);

		$this->assertInstanceOf('Danmichaelo\CustomXMLElement\CustomXMLElement', $dom);
		$this->assertEquals('2013-09-21T18:54:39.718+02:00', $dom->text('/n:NCIPMessage/n:CheckOutItemResponse/n:DateDue'));
		$this->assertEquals('2013-09-21T18:54:39.718+02:00', $dom->first('/n:NCIPMessage/n:CheckOutItemResponse')->text('n:DateDue'));
		$this->assertEquals('2013-09-21T18:54:39.718+02:00', $dom->text('//n:DateDue'));

	}

	public function testExampleXmlWithDefaultNamespacePrefix() {
		$xml = '
	 	  <ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip">
	 	     <ns1:CheckOutItemResponse>
	 	        <ns1:DateDue>2013-09-21T18:54:39.718+02:00</ns1:DateDue>	 	       
	 	     </ns1:CheckOutItemResponse>
	 	  </ns1:NCIPMessage>';
		$dom = new CustomXMLElement($xml);

		$this->assertInstanceOf('Danmichaelo\CustomXMLElement\CustomXMLElement', $dom);
		$this->assertEquals('2013-09-21T18:54:39.718+02:00', $dom->text('/ns1:NCIPMessage/ns1:CheckOutItemResponse/ns1:DateDue'));
	}
	
	/**
	 * @expectedException Danmichaelo\CustomXMLElement\InvalidXMLException
	 */
	public function testParseErrorneousXML() {
		$xml = '<ns1:NCI';
		new CustomXMLElement($xml);
	}

	/**
	 * @expectedException Danmichaelo\CustomXMLElement\InvalidXMLException
	 */
	public function testParseEmptyXML() {
		$xml = '';
		new CustomXMLElement($xml);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testArgumentIsNull() {
		$dom = new CustomXMLElement(null);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testArgumentOfUnknownType() {
		$dom = new CustomXMLElement(2.0);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testArgumentOfUnknownClass() {
		$dom = new CustomXMLElement(new \DateTime);
	}

}
