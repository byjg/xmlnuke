<?php

/**
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *
 *  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
 *  for more information.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

namespace Xmlnuke\Core\Module;

use BadMethodCallException;
use Xmlnuke\Core\Classes\ServiceDocument;
use Xmlnuke\Core\Enum\AuthMode;
use Xmlnuke\Core\Exception\NotImplementedException;

/**
 * BaseModule class is the base for custom module implementation.
 * This class uses cache, save to disk and other functionalities.
 * All custom modules must inherits this class and need to have com.xmlnuke.module namespace.
 * @see com.xmlnuke.module.ModuleFactory
 * @package xmlnuke
 */
abstract class BaseService extends BaseModule implements IService
{

	public function Setup($xmlModuleName, $customArgs)
	{
		parent::Setup($xmlModuleName, $customArgs);

		//if ($this->_context->get("CONTENT_TYPE") == "application/json")
		//	$this->
	}

	/**
	 * Expected TWO pa
	 * @return type
	 * @throws BadMethodCallException
	 */
	public final function CreatePage()
	{
		$method = strtoupper($this->_context->get("REQUEST_METHOD"));

		$customAction = strtolower($method) . ucfirst($this->_action);

		$this->defaultXmlnukeDocument = new ServiceDocument();

		if (method_exists($this, $customAction))
			$this->$customAction($this->getRawRequest(), $this->_context->get("id"));
		else
			throw new BadMethodCallException("The method '$customAction' does not exists.");

		return $this->defaultXmlnukeDocument;
	}

	/**
	 * Base method for the HTTP Method "GET"
	 * Usually it is intended for retrieve objects
	 *
	 * @param string $postData
	 * @param integer $id
	 * @throws NotImplementedException
	 */
	public function Get($postData, $id = "")
	{
		throw new NotImplementedException("Method GET not implemented");
	}

	/**
	 * Base method for the HTTP Method "POST"
	 * Usually it is intended for add or update an object
	 *
	 * @param string $postData
	 * @param integer $id
	 * @throws NotImplementedException
	 */
	public function Post($postData, $id = "")
	{
		throw new NotImplementedException("Method POST not implemented");
	}

	/**
	 * Base method for the HTTP Method "DELETE"
	 * Usually it is intended for delete an object
	 *
	 * @param string $postData
	 * @param integer $id
	 * @throws NotImplementedException
	 */
	public function Delete($postData, $id = "")
	{
		throw new NotImplementedException("Method DELETE not implemented");
	}

	/**
	 * Base method for the HTTP Method "PUT"
	 * Usually it is intended for add an object
	 *
	 * @param string $postData
	 * @param integer $id
	 * @throws NotImplementedException
	 */
	public function Put($postData, $id = "")
	{
		throw new NotImplementedException("Method PUT not implemented");
	}

	/**
	 *
	 * @return type
	 */
	//public function getAuthMode()
	//{
	//	return AuthMode::HttpBasic;
	//}
}

?>
