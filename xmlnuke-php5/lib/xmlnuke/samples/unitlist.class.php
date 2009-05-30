<?php
// url: unittest.php?list=unitlist
class UnitList
{
	protected $_context;
	
	public function __construct($context)
	{
		$this->_context = $context;
	}
	
	public function getList()
	{	
		$path = "lib/xmlnuke/samples/";
		$arr[] = $path."sampletest.class.php";
		return $arr;
	}
}
?>