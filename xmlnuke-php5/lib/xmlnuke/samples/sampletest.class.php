<?php
// Filename: lib/xmlnuke/BannerTest.php
include_once("sample.class.php");

class SampleTest extends TestCase 
{
	protected $_sample;
	protected $_context;

	function setUp() 
	{
		$this->_sample = new Sample($this->_context);
	}

	function teardown() 
	{
		$this->_sample = null;
	}

	
	function test_getVersion() 
	{
		$version = $this->_sample->getVersion();
		if ($version != "XMLNuke 2.0 PHP5 Edition")
		{
			$this->fail("The version differs from 2.0");
		}
		else 
		{
			$this->assert(true, "Result is OK");
		}
	}

	function test_getScriptName() 
	{
		$script = $this->_sample->getScriptName();
		$this->assert($script == "UnitTest.php", "The result is: " . $script);
	}
}
	
?>
