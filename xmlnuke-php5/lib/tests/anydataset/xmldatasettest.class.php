<?php
/**
 * NOTE: The class name must end with "Test" suffix.
 */
class XMLDatasetTest extends TestCase
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

		$this->assert($xmlIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($xmlIterator->hasNext(), "hasNext() method must be true");
		$this->assert($xmlIterator->Count() == 3, "Count() method must return 3");
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

		$this->assert($count == 3, "Count records mismatch. Need to process 3 records.");		
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
		
		$this->assert($count == 3, "Count records mismatch. Need to process 3 records.");
	}

	/**
	 * @AssertIfException XmlUtilException
	 */
	function test_xmlNotWellFormatted()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_NOTOK, $this->rootNode, $this->arrColumn);
	}
	
	function test_wrongNodeRoot()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_OK, "wrong", $this->arrColumn);
		$xmlIterator = $xmlDataset->getIterator();

		$this->assert($xmlIterator->Count() == 0, "Count records mismatch. Need to process 0 records.");
	}

	function test_wrongColumn()
	{
		$xmlDataset = new XmlDataSet(Context::getInstance(), XMLDatasetTest::XML_OK, $this->rootNode, array("title"=>"aaaa"));
		$xmlIterator = $xmlDataset->getIterator();

		$this->assert($xmlIterator->Count() == 3, "Count records mismatch. Need to process 3 records.");
	}

	/**
	 *
	 * @param SingleRow $sr
	 */
	function assertSingleRow($sr, $count)
	{
		$this->assert($sr->getField("category") == $this->arrTest[$count]["category"], "At line $count field 'category' I expected '" . $this->arrTest[$count]["category"] . "' but I got '" . $sr->getField("category") . "'");
		$this->assert($sr->getField("title") == $this->arrTest[$count]["title"], "At line $count field 'title' I expected '" . $this->arrTest[$count]["title"] . "' but I got '" . $sr->getField("title") . "'");
		$this->assert($sr->getField("lang") == $this->arrTest[$count]["lang"], "At line $count field 'lang' I expected '" . $this->arrTest[$count]["lang"] . "' but I got '" . $sr->getField("lang") . "'");
	}

	function assertSingleRow2($sr, $count)
	{
		$this->assert($sr->getField("id") == $this->arrTest2[$count]["id"], "At line $count field 'id' I expected '" . $this->arrTest2[$count]["id"] . "' but I got '" . $sr->getField("id") . "'");
		if ($count > 0)
			$this->assert($sr->getField("label") == $this->arrTest2[$count]["label"], "At line $count field 'label' I expected '" . $this->arrTest2[$count]["label"] . "' but I got '" . $sr->getField("label") . "'");
	}
	
}
?>