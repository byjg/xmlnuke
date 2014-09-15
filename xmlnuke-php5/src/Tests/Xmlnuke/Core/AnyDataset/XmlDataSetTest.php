<?php

use Xmlnuke\Core\AnyDataset\IIterator;
use Xmlnuke\Core\AnyDataset\SingleRow;
use Xmlnuke\Core\AnyDataset\XmlDataSet;
use Xmlnuke\Core\Engine\Context;
use Whoops\Exception\ErrorException;

/**
 * NOTE: The class name must end with "Test" suffix.
 */
class XmlDataSetTest extends PHPUnit_Framework_TestCase
{
	const XML_OK = '<?xml version="1.0" encoding="UTF-8"?>
		<bookstore>
		<book category="COOKING">
		  <title lang="en">Everyday Italian</title>
		  <author>Giada De Laurentiis</author>
		  <year>2005</year>
		  <price>30.00</price>
		</book>
		<book category="CHILDREN">
		  <title lang="de">Harry Potter</title>
		  <author>J K. Rowling</author>
		  <year>2005</year>
		  <price>29.99</price>
		</book>
		<book category="WEB">
		  <title lang="pt">Learning XML</title>
		  <author>Erik T. Ray</author>
		  <year>2003</year>
		  <price>39.95</price>
		</book>
		</bookstore>';
	const XML_NOTOK = '<book><nome>joao</book>';

	protected $rootNode = "book";
	protected $arrColumn = array("category"=>"@category", "title"=>"title", "lang"=>"title/@lang");
	
	protected $arrTest = array();
	protected $arrTest2 = array();
	
	// Run before each test case
	function setUp()
	{
		$this->arrTest = array();
		$this->arrTest[] = array("category"=>"COOKING", "title"=>"Everyday Italian", "lang"=>"en");
		$this->arrTest[] = array("category"=>"CHILDREN", "title"=>"Harry Potter", "lang"=>"de");
		$this->arrTest[] = array("category"=>"WEB", "title"=>"Learning XML", "lang"=>"pt");

		$this->arrTest2 = array();
		$this->arrTest2[] = array("id"=>"Open");
		$this->arrTest2[] = array("id"=>"OpenNew", "label"=>"Open New");
	}

	// Run end each test case
	function teardown()
	{
	}

	function test_createXMLDataset()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_OK, $this->rootNode, $this->arrColumn);
		$xmlIterator = $xmlDataset->getIterator();

		$this->assertTrue($xmlIterator instanceof IIterator);
		$this->assertTrue($xmlIterator->hasNext());
		$this->assertEquals($xmlIterator->Count(), 3);
	}
	
	function test_navigateXMLIterator()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_OK, $this->rootNode, $this->arrColumn);
		$xmlIterator = $xmlDataset->getIterator();

		$count = 0;
		while ($xmlIterator->hasNext())
		{
			$this->assertSingleRow($xmlIterator->moveNext(), $count++);
		}

		$this->assertEquals($count, 3);
	}

	function test_navigateXMLIterator2()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_OK, $this->rootNode, $this->arrColumn);
		$xmlIterator = $xmlDataset->getIterator();

		$count = 0;
		foreach ($xmlIterator as $sr)
		{
			$this->assertSingleRow($sr, $count++);
		}
		
		$this->assertEquals($count, 3);
	}

	/**
	 * @expectedException \Whoops\Exception\ErrorException
	 */
	function test_xmlNotWellFormatted()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_NOTOK, $this->rootNode, $this->arrColumn);
	}
	
	function test_wrongNodeRoot()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_OK, "wrong", $this->arrColumn);
		$xmlIterator = $xmlDataset->getIterator();

		$this->assertEquals($xmlIterator->Count(), 0);
	}

	function test_wrongColumn()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_OK, $this->rootNode, array("title"=>"aaaa"));
		$xmlIterator = $xmlDataset->getIterator();

		$this->assertEquals($xmlIterator->Count(), 3); 
	}

	function test_repeatedNodes()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<bookstore>
		<book category="COOKING">
		  <title lang="en">Everyday Italian</title>
		  <author>Giada De Laurentiis</author>
		  <author>Another Author</author>
		  <year>2005</year>
		  <price>30.00</price>
		</book></bookstore>';

		$xmlDataset = new XmlDataSet(Context::getInstance(), $xml, $this->rootNode, array("author"=>"author"));
		$xmlIterator = $xmlDataset->getIterator();

		$this->assertEquals(1, $xmlIterator->Count());

		$sr = $xmlIterator->moveNext();
		$authors = $sr->getFieldArray('author');

		$this->assertEquals(2, count($authors));
		$this->assertEquals('Giada De Laurentiis', $authors[0]);
		$this->assertEquals('Another Author', $authors[1]);
		
	}

	/**
	 *
	 * @param SingleRow $sr
	 */
	function assertSingleRow($sr, $count)
	{
		$this->assertEquals($sr->getField("category"), $this->arrTest[$count]["category"]);
		$this->assertEquals($sr->getField("title"), $this->arrTest[$count]["title"]);
		$this->assertEquals($sr->getField("lang"), $this->arrTest[$count]["lang"]);
	}

	function assertSingleRow2($sr, $count)
	{
		$this->assertEquals($sr->getField("id"), $this->arrTest2[$count]["id"]);
		if ($count > 0)
			$this->assertEquals($sr->getField("label"), $this->arrTest2[$count]["label"]); 
	}
	
}
?>