<?php

namespace __PROJECT__\Base;

class BaseService extends \Xmlnuke\Core\Module\BaseService
{

	/**
	 *
	 * @return OutputData
	 */
	public function getOutputFormat()
	{
		if ($this->_context->getOutputFormat() == \Xmlnuke\Core\Enum\OutputData::Xml)
		{
			return \Xmlnuke\Core\Enum\OutputData::Xml;
		}
		else
		{
			return \Xmlnuke\Core\Enum\OutputData::Json;
		}
	}

}