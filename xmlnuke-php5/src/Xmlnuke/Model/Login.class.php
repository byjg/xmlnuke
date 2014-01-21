<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*/

namespace Xmlnuke\Model;

/**
 * @Xmlnuke:NodeName Login
 */
class Login extends \Xmlnuke\Core\Database\BaseModel
{
	protected $_Username;
	protected $_Password;
	protected $_Email;
	protected $_Name;

	protected $_CanRegister;
	protected $_CanRetrievePassword;

	protected $_Action;
	protected $_NextAction;

	protected $_ReturnUrl;

	public function getUsername()
	{
		return $this->_Username;
	}

	public function getPassword()
	{
		return $this->_Password;
	}

	public function getEmail()
	{
		return $this->_Email;
	}

	public function getName()
	{
		return $this->_Name;
	}

	public function getCanRegister()
	{
		return $this->_CanRegister;
	}

	public function getCanRetrievePassword()
	{
		return $this->_CanRetrievePassword;
	}

	public function getAction()
	{
		return $this->_Action;
	}

	public function getNextAction()
	{
		return $this->_NextAction;
	}

	public function getReturnUrl()
	{
		return $this->_ReturnUrl;
	}

	public function setUsername($Username)
	{
		$this->_Username = $Username;
	}

	public function setPassword($Password)
	{
		$this->_Password = $Password;
	}

	public function setEmail($Email)
	{
		$this->_Email = $Email;
	}

	public function setName($Name)
	{
		$this->_Name = $Name;
	}

	public function setCanRegister($CanRegister)
	{
		$this->_CanRegister = $CanRegister;
	}

	public function setCanRetrievePassword($CanRetrievePassword)
	{
		$this->_CanRetrievePassword = $CanRetrievePassword;
	}

	public function setAction($Action)
	{
		$this->_Action = $Action;
	}

	public function setNextAction($NextAction)
	{
		$this->_NextAction = $NextAction;
	}

	public function setReturnUrl($ReturnUrl)
	{
		$this->_ReturnUrl = $ReturnUrl;
	}


}
