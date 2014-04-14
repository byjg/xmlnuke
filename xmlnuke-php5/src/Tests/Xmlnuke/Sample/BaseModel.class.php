<?php

namespace Tests\Xmlnuke\Sample;

class BaseModel extends \Xmlnuke\Core\Database\BaseModel
{
	public $Id = "";
	protected $_Name = "";

	function __construct($object = null)
	{
		parent::__construct($object);
	}

	public function getName()
	{
		return $this->_Name;
	}

	public function setName($Name)
	{
		$this->_Name = $Name;
	}



}