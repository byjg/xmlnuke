<?php
/**
 * NOTE: The class name must end with "Test" suffix.
 */
class JSONDatasetTest extends TestCase
{
	const JSON_OK = '[{"name":"Joao","surname":"Magalhaes","age":"38"},{"name":"John","surname":"Doe","age":"20"},{"name":"Jane","surname":"Smith","age":"18"}]';
	const JSON_NOTOK = '"name":"Joao","surname":"Magalhaes","age":"38"}]';
	const JSON_OK2 = '{"menu": {"header": "SVG Viewer", "items": [ {"id": "Open"}, {"id": "OpenNew", "label": "Open New"} ]}}';

	protected $arrTest = array();
	protected $arrTest2 = array();
	
	// Run before each test case
	function setUp()
	{
		$this->arrTest = array();
		$this->arrTest[] = array("name"=>"Joao", "surname"=>"Magalhaes", "age"=>38);
		$this->arrTest[] = array("name"=>"John", "surname"=>"Doe", "age"=>20);
		$this->arrTest[] = array("name"=>"Jane", "surname"=>"Smith", "age"=>18);

		$this->arrTest2 = array();
		$this->arrTest2[] = array("id"=>"Open");
		$this->arrTest2[] = array("id"=>"OpenNew", "label"=>"Open New");
	}

	// Run end each test case
	function teardown()
	{
	}

	function test_createJSONIterator()
	{
		$jsonDataset = new JSONDataSet(JSONDatasetTest::JSON_OK);
		$jsonIterator = $jsonDataset->getIterator();

		$this->assert($jsonIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($jsonIterator->hasNext(), "hasNext() method must be true");
		$this->assert($jsonIterator->Count() == 3, "Count() method must return 3");
	}
	
	function test_navigateJSONIterator()
	{
		$jsonDataset = new JSONDataSet(JSONDatasetTest::JSON_OK);
		$jsonIterator = $jsonDataset->getIterator();

		$count = 0;
		while ($jsonIterator->hasNext())
		{
			$this->assertSingleRow($jsonIterator->moveNext(), $count++);
		}

		$this->assert($count == 3, "Count records mismatch. Need to process 3 records.");		
	}

	function test_navigateJSONIterator2()
	{
		$jsonDataset = new JSONDataSet(JSONDatasetTest::JSON_OK);
		$jsonIterator = $jsonDataset->getIterator();

		$count = 0;
		foreach ($jsonIterator as $sr)
		{
			$this->assertSingleRow($sr, $count++);
		}
		
		$this->assert($count == 3, "Count records mismatch. Need to process 3 records.");
	}

	/**
	 * @AssertIfException Exception
	 */
	function test_jsonNotWellFormatted()
	{
		$jsonDataset = new JSONDataSet(JSONDatasetTest::JSON_NOTOK);
	}
	
	function navigateJSONComplex($path)
	{
		$jsonDataset = new JSONDataSet(JSONDatasetTest::JSON_OK2);		
		$jsonIterator = $jsonDataset->getIterator($path);

		$count = 0;
		foreach ($jsonIterator as $sr)
		{
			$this->assertSingleRow2($sr, $count++);
		}
		
		$this->assert($count == 2, "Count records mismatch. Need to process 2 records.");				
	}

	function test_navigateJSONComplexIterator()
	{
		$this->navigateJSONComplex("/menu/items");
	}
	
	function test_navigateJSONComplexIteratorWithOutSlash()
	{
		$this->navigateJSONComplex("menu/items");
	}

	function test_navigateJSONComplexIteratorWrongPath()
	{
		$jsonDataset = new JSONDataSet(JSONDatasetTest::JSON_OK2);		
		$jsonIterator = $jsonDataset->getIterator("/menu/wrong");
		
		$this->assert($jsonIterator->Count() == 0, "Without throw error");
	}

	/**
	 * @AssertIfException Exception
	 */
	function test_navigateJSONComplexIteratorWrongPath2()
	{
		$jsonDataset = new JSONDataSet(JSONDatasetTest::JSON_OK2);		
		$jsonIterator = $jsonDataset->getIterator("/menu/wrong", true);
	}
	
	/**
	 *
	 * @param SingleRow $sr
	 */
	function assertSingleRow($sr, $count)
	{
		$this->assert($sr->getField("name") == $this->arrTest[$count]["name"], "At line $count field 'name' I expected '" . $this->arrTest[$count]["name"] . "' but I got '" . $sr->getField("name") . "'");
		$this->assert($sr->getField("surname") == $this->arrTest[$count]["surname"], "At line $count field 'surname' I expected '" . $this->arrTest[$count]["surname"] . "' but I got '" . $sr->getField("surname") . "'");
		$this->assert($sr->getField("age") == $this->arrTest[$count]["age"], "At line $count field 'age' I expected '" . $this->arrTest[$count]["age"] . "' but I got '" . $sr->getField("age") . "'");
	}

	function assertSingleRow2($sr, $count)
	{
		$this->assert($sr->getField("id") == $this->arrTest2[$count]["id"], "At line $count field 'id' I expected '" . $this->arrTest2[$count]["id"] . "' but I got '" . $sr->getField("id") . "'");
		if ($count > 0)
			$this->assert($sr->getField("label") == $this->arrTest2[$count]["label"], "At line $count field 'label' I expected '" . $this->arrTest2[$count]["label"] . "' but I got '" . $sr->getField("label") . "'");
	}
	
}
?>