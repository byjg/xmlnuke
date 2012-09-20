<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*/

class BTreeNode implements IBTreeNode
{
	/**
	*@var string
	*/
	private  $_key;
	/**
	*@var array()
	*/
	private  $_values;

	/**
	*@param string $key
	*@param string $value
	*/
	public function  BTreeNode($key, $value = null)
	{
		$this->_key = strtolower($key);
		if ($value == null)
		{
			$this->_values = array();
		}
		else
		{
			$this->_values[] = $value;
		}
	}

	/**
	*@param IBTreeNode $bnode
	*@return bool 
	*/
	public function lessThan($bnode)
	{
		if (strcmp($this->getKey(),$bnode->getKey())< 0)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	*@param IBTreeNode $bnode
	*@return bool 
	*/
	public function greaterThan($bnode)
	{

		if (strcmp($this->getKey(),$bnode->getKey()) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	*@param IBTreeNode $bnode
	*@return bool
	*/
	public function equalsTo($bnode)
	{
		if (strcmp(trim($this->getKey()),trim($bnode->getKey())) == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*@return string
	*/
	public function getKey()
	{
		return $this->_key;
	}

	/**
	*@return array()
	*/
	public function values()
	{
		return $this->_values;
	}
	
	/**
	*@param string $value
	*@return void
	*/
	public function addValue($value)
	{
		$this->_values[] = $value;
	}

}
?>
