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

		//if ($this->_context->Value("CONTENT_TYPE") == "application/json")
		//	$this->
	}

	public function CreatePage()
	{
		$method = strtoupper($this->_context->Value("REQUEST_METHOD"));

		switch ($method)
		{
			case "GET":
				$this->defaultXmlnukeDocument = $this->Get();
				break;

			case "POST":
				$this->defaultXmlnukeDocument = $this->Post();
				break;

			case "PUT":
				$this->defaultXmlnukeDocument = $this->Put();
				break;

			case "DELETE":
				$this->defaultXmlnukeDocument = $this->Delete();
				break;

			default:
				throw new NotImplementedException("Method $method not implemented");

		}

		return $this->defaultXmlnukeDocument;
	}

	public function Get()
	{
		throw new NotImplementedException("Method GET not implemented");
	}

	public function Post()
	{
		throw new NotImplementedException("Method POST not implemented");
	}

	public function Delete()
	{
		throw new NotImplementedException("Method DELETE not implemented");
	}

	public function Put()
	{
		throw new NotImplementedException("Method PUT not implemented");
	}
}

?>
