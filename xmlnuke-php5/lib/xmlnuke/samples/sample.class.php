<?php
class Sample
{
	private $_context;
	
	public function __construct($context)
	{
		$this->_context = $context;	
	}
	
	public function getScriptName()
	{
		return basename($this->_context->ContextValue("SCRIPT_NAME"));
	}
	
	public function getVersion()
	{
		return $this->_context->XmlNukeVersion();
	}
	
}