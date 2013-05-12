<?php
// url: unittest.php?list=tests.anydataset.testlist
class TestList
{
	public function __construct()
	{
	}

	public function getList()
	{
		$arr[] = "tests.anydataset.jsondatasettest";
		$arr[] = "tests.anydataset.xmldatasettest";
		$arr[] = "tests.anydataset.sparqldatasettest";
		$arr[] = "tests.anydataset.textfiledatasettest";
		$arr[] = "tests.anydataset.arraydatasettest";
		return $arr;
	}
}		
?>
