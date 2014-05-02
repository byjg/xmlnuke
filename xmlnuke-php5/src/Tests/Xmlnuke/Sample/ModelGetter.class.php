<?php

namespace Tests\Xmlnuke\Sample;

/**
 * @Xmlnuke:NodeName ModelGetter
 */
class ModelGetter extends \Xmlnuke\Core\Database\BaseModel
{
	protected $_Id = "";
	protected $_Name = "";

	function __construct($Id, $Name)
	{
		$this->_Id = $Id;
		$this->_Name = $Name;
	}

	public function getId()
	{
		return $this->_Id;
	}

	public function getName()
	{
		return $this->_Name;
	}

	public function setId($Id)
	{
		$this->_Id = $Id;
	}

	public function setName($Name)
	{
		$this->_Name = $Name;
	}



}