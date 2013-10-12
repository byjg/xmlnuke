<?php
/**
 * NOTE: The class name must end with "Test" suffix.
 */
class TextFileDataSetTest extends TestCase
{
	protected $fieldNames;

	protected $fileName_Unix = "";
	protected $fileName_Windows = "";
	protected $fileName_MacClassic = "";
	protected $fileName_BlankLine = "";
	
	// Run before each test case
	function setUp()
	{
		$this->fileName_Unix = FileUtil::GetTempDir() . FileUtil::Slash() . "textfiletest-unix.csv";
		$this->fileName_Windows = FileUtil::GetTempDir() . FileUtil::Slash() . "textfiletest-windows.csv";
		$this->fileName_MacClassic = FileUtil::GetTempDir() . FileUtil::Slash() . "textfiletest-mac.csv";
		$this->fileName_BlankLine = FileUtil::GetTempDir() . FileUtil::Slash() . "textfiletest-bl.csv";

		$text = "";
		for($i=1; $i<=2000; $i++)
			$text .= "$i;STRING$i;VALUE$i\n";
		FileUtil::QuickFileWrite($this->fileName_Unix, $text);

		$text = "";
		for($i=1; $i<=2000; $i++)
			$text .= "$i;STRING$i;VALUE$i\r\n";
		FileUtil::QuickFileWrite($this->fileName_Windows, $text);

		$text = "";
		for($i=1; $i<=2000; $i++)
			$text .= "$i;STRING$i;VALUE$i\r";
		FileUtil::QuickFileWrite($this->fileName_MacClassic, $text);

		$text = "";
		for($i=1; $i<=2000; $i++)
		{
			if (rand(0, 10) < 3)
				$text .= "\n";
			$text .= "$i;STRING$i;VALUE$i\n";
		}
		FileUtil::QuickFileWrite($this->fileName_BlankLine, $text);

		// A lot of extras fields
		$this->fieldNames = array();
		for($i=1; $i<30;$i++)
		{
			$this->fieldNames[] = "field$i";
		}
	}

	// Run end each test case
	function teardown()
	{
		FileUtil::DeleteFileString($this->fileName_Unix);
		FileUtil::DeleteFileString($this->fileName_Windows);
		FileUtil::DeleteFileString($this->fileName_MacClassic);
	}

	function test_createTextFileData_Unix()
	{
		$txtFile = new TextFileDataSet($this->_context, $this->fileName_Unix, $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$this->assert($txtIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($txtIterator->hasNext(), "hasNext() method must be true");
		$this->assert($txtIterator->Count() == -1, "Count() does not return anything by default.");
		$this->assertRowCount($txtIterator, 2000);

	}

	function test_createTextFileData_Windows()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), $this->fileName_Windows, $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$this->assert($txtIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($txtIterator->hasNext(), "hasNext() method must be true");
		$this->assert($txtIterator->Count() == -1, "Count() does not return anything by default.");
		$this->assertRowCount($txtIterator, 2000);

	}

	function test_createTextFileData_MacClassic()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), $this->fileName_MacClassic, $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$this->assert($txtIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($txtIterator->hasNext(), "hasNext() method must be true");
		$this->assert($txtIterator->Count() == -1, "Count() does not return anything by default.");
		$this->assertRowCount($txtIterator, 2000);

	}

	function test_createTextFileData_BlankLine()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), $this->fileName_BlankLine, $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$this->assert($txtIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assert($txtIterator->hasNext(), "hasNext() method must be true");
		$this->assert($txtIterator->Count() == -1, "Count() does not return anything by default.");
		$this->assertRowCount($txtIterator, 2000);

	}

	function test_navigateTextIterator_Unix()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), $this->fileName_Windows, $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}
		
		$this->assert($count == 2000, "Count records mismatch. Need to process 2000 records.");
	}

	function test_navigateTextIterator_Windows()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), $this->fileName_Windows, $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assert($count == 2000, "Count records mismatch. Need to process 2000 records.");
	}

	function test_navigateTextIterator_MacClassic()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), $this->fileName_Windows, $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assert($count == 2000, "Count records mismatch. Need to process 2000 records.");
	}

	function test_navigateTextIterator_BlankLine()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), $this->fileName_BlankLine, $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assert($count == 2000, "Count records mismatch. Need to process 2000 records.");
	}

	function test_navigateTextIterator_Remote_Unix()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), 'http://www.xmlnuke.com/site/' . basename($this->fileName_Unix), $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assert($count == 2000, "Count records mismatch. Need to process 2000 records and I count $count.");
	}

	function test_navigateTextIterator_Remote_Windows()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), 'http://www.xmlnuke.com/site/' . basename($this->fileName_Windows), $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assert($count == 2000, "Count records mismatch. Need to process 2000 records and I count $count.");
	}

	/**
	 * fsockopen and fgets is buggy when read a Mac classic document (\r line ending)
	 */
	/*
	function test_navigateTextIterator_Remote_MacClassic()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), 'http://www.xmlnuke.com/site/' . basename($this->fileName_MacClassic), $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assert($count == 2000, "Count records mismatch. Need to process 2000 records and I count $count.");
	}
	 *
	 */

	function test_navigateTextIterator_Remote_BlankLine()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), 'http://www.xmlnuke.com/site/' . basename($this->fileName_BlankLine), $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assert($count == 2000, "Count records mismatch. Need to process 2000 records and I count $count.");
	}


	/**
	 * @AssertIfException NotFoundException
	 */
	function test_fileNotFound()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), "/tmp/xyz", $this->fieldNames, TextFileDataSet::CSVFILE);
	}

	/**
	 * @AssertIfException DatasetException
	 */
	function test_remoteFileNotFound()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), "http://www.xmlnuke.com/site/notfound-test", $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();
	}

	/**
	 * @AssertIfException DatasetException
	 */
	function test_serverNotFound()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), "http://notfound-test/alalal", $this->fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();
	}

	/**
	 *
	 * @param SingleRow $sr
	 */
	function assertSingleRow($sr, $count)
	{
		$this->assert($sr->getField("field1") == $count, "At line $count field 'field1' I expected '" . $count . "' but I got '" . $sr->getField("field1") . "'");
		$this->assert($sr->getField("field2") == "STRING$count", "At line $count field 'field2' I expected 'STRING" . $count . "' but I got '" . $sr->getField("field2") . "'");
		$this->assert($sr->getField("field3") == "VALUE$count", "At line $count field 'field3' I expected 'VALUE" . $count . "' but I got '" . $sr->getField("field3") . "'");
	}

	function assertRowCount($it, $qty)
	{
		$count = 0;
		foreach($it as $sr) $count++;

		$this->assert($qty == $count, "This file must have " . $qty . " lines and I read " . $count);
	}
	
}
?>