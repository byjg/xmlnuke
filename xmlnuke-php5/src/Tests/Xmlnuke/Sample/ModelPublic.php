<?php

namespace Tests\Xmlnuke\Sample;

class ModelPublic extends \Xmlnuke\Core\Database\BaseModel
{
	public $Id = "";
	public $Name = "";

	function __construct($Id, $Name)
	{
		$this->Id = $Id;
		$this->Name = $Name;
	}
}