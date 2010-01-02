<?php
// url: unittest.php?list=xmlnuke.samples.unitlist
class UnitList
{
	protected $_context;

	public function __construct($context)
	{
		$this->_context = $context;
	}

	public function getList()
	{
		$arr[] = "xmlnuke.samples.sampletest";
		return $arr;
	}
}
?>
