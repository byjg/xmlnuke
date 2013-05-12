<?php
/**
 * NOTE: The class name must end with "Test" suffix.
 */
class ArrayDatasetTest extends TestCase
{
	protected $fieldNames;

	protected $SAMPLE1 = array("ProdA", "ProdB", "ProdC");
	protected $SAMPLE2 = array("A"=>"ProdA", "B"=>"ProdB", "C"=>"ProdC");
	protected $SAMPLE3 = array("A"=>array('code'=>1000, 'name'=>"ProdA"),
		"B"=>array('code'=>1001, 'name'=>"ProdB"),
		"C"=>array('code'=>1002, 'name'=>"ProdC"));
	
	// Run before each test case
	function setUp()
	{

	}

	// Run end each test case
	function teardown()
	{
	}

	/**
	 * @AssertIfException UnexpectedValueException
	 */
	function test_InvalidConstructor()
	{
		$arrayDataset = new ArrayDataset('aaa');
	}

	function test_createArrayIteratorSample1()
	{
		$arrayDataset = new ArrayDataset($this->SAMPLE1);
		$arrayIterator = $arrayDataset->getIterator();

		$this->assert($arrayIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($arrayIterator->hasNext(), "hasNext() method must be true");
		$this->assert($arrayIterator->Count() == 3, "Count() method must return 3");
	}

	function test_createArrayIteratorSample2()
	{
		$arrayDataset = new ArrayDataset($this->SAMPLE2);
		$arrayIterator = $arrayDataset->getIterator();

		$this->assert($arrayIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($arrayIterator->hasNext(), "hasNext() method must be true");
		$this->assert($arrayIterator->Count() == 3, "Count() method must return 3");
	}

	function test_createArrayIteratorSample3()
	{
		$arrayDataset = new ArrayDataset($this->SAMPLE3);
		$arrayIterator = $arrayDataset->getIterator();

		$this->assert($arrayIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($arrayIterator->hasNext(), "hasNext() method must be true");
		$this->assert($arrayIterator->Count() == 3, "Count() method must return 3");
	}


	function test_navigateArrayIteratorSample1()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE1);
		$arrayIterator = $arrayDataset->getIterator();

		$count = 0;

		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 0);
			$this->assertField($sr, $count, "key", 0);
			$this->assertField($sr, $count, "value", 'ProdA');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 1);
			$this->assertField($sr, $count, "key", 1);
			$this->assertField($sr, $count, "value", 'ProdB');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 2);
			$this->assertField($sr, $count, "key", 2);
			$this->assertField($sr, $count, "value", 'ProdC');
			$count++;
		}
		$this->assert(!$arrayIterator->hasNext(), 'I did not expected more records');
		$this->assert($count == 3, "Count records mismatch. Need to process 3 records.");
	}

	function test_navigateArrayIteratorSample2()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE2);
		$arrayIterator = $arrayDataset->getIterator();

		$count = 0;

		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 0);
			$this->assertField($sr, $count, "key", 'A');
			$this->assertField($sr, $count, "value", 'ProdA');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 1);
			$this->assertField($sr, $count, "key", 'B');
			$this->assertField($sr, $count, "value", 'ProdB');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 2);
			$this->assertField($sr, $count, "key", 'C');
			$this->assertField($sr, $count, "value", 'ProdC');
			$count++;
		}
		$this->assert(!$arrayIterator->hasNext(), 'I did not expected more records');
		$this->assert($count == 3, "Count records mismatch. Need to process 3 records.");
	}

	function test_navigateArrayIteratorSample3()
	{
		$arrayDataset = new ArrayDataSet($this->SAMPLE3);
		$arrayIterator = $arrayDataset->getIterator();

		$count = 0;

		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 0);
			$this->assertField($sr, $count, "key", 'A');
			$this->assertField($sr, $count, "code", 1000);
			$this->assertField($sr, $count, "name", 'ProdA');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 1);
			$this->assertField($sr, $count, "key", 'B');
			$this->assertField($sr, $count, "code", 1001);
			$this->assertField($sr, $count, "name", 'ProdB');
			$count++;
		}
		if ($arrayIterator->hasNext())
		{
			$sr = $arrayIterator->moveNext();
			$this->assertField($sr, $count, "id", 2);
			$this->assertField($sr, $count, "key", 'C');
			$this->assertField($sr, $count, "code", 1002);
			$this->assertField($sr, $count, "name", 'ProdC');
			$count++;
		}
		$this->assert(!$arrayIterator->hasNext(), 'I did not expected more records');
		$this->assert($count == 3, "Count records mismatch. Need to process 3 records.");
	}

	/**
	 *
	 * @param SingleRow $sr
	 */
	function assertField($sr, $line, $field, $value)
	{
		$this->assert($sr->getField($field) === $value, "At line $line field '$field' I expected '" . $value . "' but I got '" . $sr->getField($field) . "'");
	}

	function assertSingleRow2($sr, $count)
	{
		$this->assert($sr->getField("id") == $this->arrTest2[$count]["id"], "At line $count field 'id' I expected '" . $this->arrTest2[$count]["id"] . "' but I got '" . $sr->getField("id") . "'");
		if ($count > 0)
			$this->assert($sr->getField("label") == $this->arrTest2[$count]["label"], "At line $count field 'label' I expected '" . $this->arrTest2[$count]["label"] . "' but I got '" . $sr->getField("label") . "'");
	}

}
?>