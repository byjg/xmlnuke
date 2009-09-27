<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes
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

class TextFileIterator extends GenericIterator
{
	/**
	*@var Context
	*/
	private $_context;

	protected $_fields;

	protected $_fieldexpression;

	protected $_handle;

	protected $_current = 0;

	/**
	*@access public
	*@return IIterator
	*/
	public function TextFileIterator($context, $handle, $fields, $fieldexpression)
	{
		$this->_context = $context;
		$this->_fields = $fields;
		$this->_fieldexpression = $fieldexpression;
		$this->_handle = $handle;
	}

	/**
	*@access public
	*@return int
	*/
	public function Count()
	{
		return -1;
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
		if (!$this->_handle)
		{
			return false;
		}
		else
		{
			if (feof($this->_handle))
			{
				fclose($this->_handle);
				return false;
			}
			else
			{
				return true;
			}
		}
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		if ($this->hasNext())
		{
			$buffer = fgets($this->_handle, 4096);
			$cols = preg_split($this->_fieldexpression,$buffer,-1,PREG_SPLIT_DELIM_CAPTURE);

			$sr = new SingleRow();

			for($i=0;($i<sizeof($this->_fields)) && ($i<sizeof($cols)); $i++)
			{
				$sr->AddField(strtolower($this->_fields[$i]), $cols[$i]);
				//Debug::PrintValue(strtolower($this->_fields[$i]), $cols[$i]);
			}

			$this->_current++;
			return 	$sr;
		}
		else
		{
			if ($this->_handle)
			{
				fclose($this->_handle);
			}
			return null;
		}
	}

 	function key()
 	{
 		return $this->_current;
 	}

}
?>
