<?php
// url: unittest.php?list=tests.anydataset.testlist
class TestList
{
	public function __construct($context)
	{
	}

	public function getList()
	{
		$arr[] = "tests.util.mailutiltest";
		return $arr;
	}
}		
?>