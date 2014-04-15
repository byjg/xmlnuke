<?php

use Xmlnuke\Core\AnyDataset\IIterator;
use Xmlnuke\Core\AnyDataset\SingleRow;
use Xmlnuke\Core\AnyDataset\TextFileDataSet;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Exception\DatasetException;
use Xmlnuke\Core\Exception\NotFoundException;
use Xmlnuke\Util\FileUtil;
/**
 * NOTE: The class name must end with "Test" suffix.
 */
class TextFileDataSetTest extends PHPUnit_Framework_TestCase
{
	protected static $fieldNames;

	protected static $fileName_Unix = "";
	protected static $fileName_Windows = "";
	protected static $fileName_MacClassic = "";
	protected static $fileName_BlankLine = "";

	const RemoteURL = "http://www.xmlnuke.com/site/";

	protected $_context = null;

	public static function setUpBeforeClass()
	{
		self::$fileName_Unix = FileUtil::GetTempDir() . FileUtil::Slash() . "textfiletest-unix.csv";
		self::$fileName_Windows = FileUtil::GetTempDir() . FileUtil::Slash() . "textfiletest-windows.csv";
		self::$fileName_MacClassic = FileUtil::GetTempDir() . FileUtil::Slash() . "textfiletest-mac.csv";
		self::$fileName_BlankLine = FileUtil::GetTempDir() . FileUtil::Slash() . "textfiletest-bl.csv";

		$text = "";
		for($i=1; $i<=2000; $i++)
			$text .= "$i;STRING$i;VALUE$i\n";
		FileUtil::QuickFileWrite(self::$fileName_Unix, $text);

		$text = "";
		for($i=1; $i<=2000; $i++)
			$text .= "$i;STRING$i;VALUE$i\r\n";
		FileUtil::QuickFileWrite(self::$fileName_Windows, $text);

		$text = "";
		for($i=1; $i<=2000; $i++)
			$text .= "$i;STRING$i;VALUE$i\r";
		FileUtil::QuickFileWrite(self::$fileName_MacClassic, $text);

		$text = "";
		for($i=1; $i<=2000; $i++)
		{
			if (rand(0, 10) < 3)
				$text .= "\n";
			$text .= "$i;STRING$i;VALUE$i\n";
		}
		FileUtil::QuickFileWrite(self::$fileName_BlankLine, $text);

		// A lot of extras fields
		self::$fieldNames = array();
		for($i=1; $i<30;$i++)
		{
			self::$fieldNames[] = "field$i";
		}
	}

	public static function tearDownAfterClass()
	{
		FileUtil::DeleteFileString(self::$fileName_Unix);
		FileUtil::DeleteFileString(self::$fileName_Windows);
		FileUtil::DeleteFileString(self::$fileName_MacClassic);
		FileUtil::DeleteFileString(self::$fileName_BlankLine);
	}

	// Run before each test case
	function setUp()
	{
		$this->_context = Context::getInstance();
	}

	// Run end each test case
	function teardown()
	{

	}

	function test_createTextFileData_Unix()
	{
		$txtFile = new TextFileDataSet($this->_context, self::$fileName_Unix, self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$this->assertTrue($txtIterator instanceof IIterator, "Resultant object must be an interator");
		$this->assertTrue($txtIterator->hasNext(), "hasNext() method must be true");
		$this->assertTrue($txtIterator->Count() == -1, "Count() does not return anything by default.");
		$this->assertRowCount($txtIterator, 2000);

	}

	function test_createTextFileData_Windows()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::$fileName_Windows, self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$this->assertTrue($txtIterator instanceof IIterator);
		$this->assertTrue($txtIterator->hasNext());
		$this->assertEquals($txtIterator->Count(), -1);
		$this->assertRowCount($txtIterator, 2000);

	}

	function test_createTextFileData_MacClassic()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::$fileName_MacClassic, self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$this->assertTrue($txtIterator instanceof IIterator);
		$this->assertTrue($txtIterator->hasNext());
		$this->assertEquals($txtIterator->Count(), -1);
		$this->assertRowCount($txtIterator, 2000);

	}

	function test_createTextFileData_BlankLine()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::$fileName_BlankLine, self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$this->assertTrue($txtIterator instanceof IIterator);
		$this->assertTrue($txtIterator->hasNext());
		$this->assertEquals($txtIterator->Count(), -1);
		$this->assertRowCount($txtIterator, 2000);

	}

	function test_navigateTextIterator_Unix()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::$fileName_Windows, self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}
		
		$this->assertEquals($count, 2000);
	}

	function test_navigateTextIterator_Windows()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::$fileName_Windows, self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assertEquals($count, 2000);
	}

	function test_navigateTextIterator_MacClassic()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::$fileName_Windows, self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assertEquals($count, 2000);
	}

	function test_navigateTextIterator_BlankLine()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::$fileName_BlankLine, self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assertEquals($count, 2000);
	}

	function test_navigateTextIterator_Remote_Unix()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::RemoteURL . basename(self::$fileName_Unix), self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assertEquals($count, 2000);
	}

	function test_navigateTextIterator_Remote_Windows()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::RemoteURL . basename(self::$fileName_Windows), self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assertEquals($count, 2000);
	}

	/**
	 * fsockopen and fgets is buggy when read a Mac classic document (\r line ending)
	 */
	function test_navigateTextIterator_Remote_MacClassic()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::RemoteURL . basename(self::$fileName_MacClassic), self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assertEquals($count, 2000);
	}

	function test_navigateTextIterator_Remote_BlankLine()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::RemoteURL . basename(self::$fileName_BlankLine), self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();

		$count = 0;
		foreach ($txtIterator as $sr)
		{
			$this->assertSingleRow($sr, ++$count);
		}

		$this->assertEquals($count, 2000); 
	}


	/**
	 * @expectedException \Xmlnuke\Core\Exception\NotFoundException
	 */
	function test_fileNotFound()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), "/tmp/xyz", self::$fieldNames, TextFileDataSet::CSVFILE);
	}

	/**
	 * @expectedException \Xmlnuke\Core\Exception\DatasetException
	 */
	function test_remoteFileNotFound()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), self::RemoteURL . "notfound-test", self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();
	}

	/**
	 * @expectedException \Xmlnuke\Core\Exception\DatasetException
	 */
	function test_serverNotFound()
	{
		$txtFile = new TextFileDataSet(Context::getInstance(), "http://notfound-test/alalal", self::$fieldNames, TextFileDataSet::CSVFILE);
		$txtIterator = $txtFile->getIterator();
	}

	/**
	 *
	 * @param SingleRow $sr
	 */
	function assertSingleRow($sr, $count)
	{
		$this->assertEquals($sr->getField("field1"), $count);
		$this->assertEquals($sr->getField("field2"), "STRING$count");
		$this->assertEquals($sr->getField("field3"), "VALUE$count");
	}

	function assertRowCount($it, $qty)
	{
		$count = 0;
		foreach($it as $sr) $count++;

		$this->assertEquals($qty, $count); 
	}
	
}
?>