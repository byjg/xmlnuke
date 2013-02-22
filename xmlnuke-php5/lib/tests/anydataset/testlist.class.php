<?php
// url: unittest.php?list=tests.anydataset.testlist
class TestList
{
	public function __construct($context)
	{
	}

	public function getList()
	{
		$arr[] = "tests.anydataset.jsondatasettest";
		$arr[] = "tests.anydataset.xmldatasettest";
		$arr[] = "tests.anydataset.sparqldatasettest";
		$arr[] = "tests.anydataset.textfiledatasettest";
		return $arr;
	}
}		
?>
