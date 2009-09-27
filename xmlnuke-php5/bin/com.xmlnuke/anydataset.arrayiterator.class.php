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

class ArrayIIterator extends GenericIterator
{
	/**
	*@var array
	*/
	protected $_rows;

	/**
	 * Enter description here...
	 *
	 * @var array
	 */
	protected $_keys;

	/**
	/*@var int
	*/
	protected $_currentRow;

	/**
	*@access public
	*@return IIterator
	*/
	public function ArrayIIterator($rows)
	{
		if (!is_array($rows))
		{
			throw new Exception("ArrayIIterator must receive an array");
		}
		$this->_currentRow = 0;
		$this->_rows = $rows;
		$this->_keys = array_keys($rows);
	}

	/**
	*@access public
	*@return int
	*/
	public function Count()
	{
		return sizeof($this->_rows);
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
		return ($this->_currentRow < $this->Count());
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		if ($this->hasNext())
		{
			$cols = array();
			$key = $this->_keys[$this->_currentRow];
			$row = $this->_rows[$key];
			if (strpos($row, "||#||")===false)
			{
				$cols[] = $row;
			}
			else
			{
				$cols = explode("||#||", $this->_rows[$this->_currentRow]);
			}

			$any = new AnyDataSet();
			$any->appendRow();
			$any->addField("id", $this->_currentRow);
			$any->addField("key", $key);
			for($i=0;$i<sizeof($cols); $i++)
			{
				$field = explode(":=", $cols[$i]);
				$any->addField(strtolower($field[0]), $field[1]);
			}
			$it = $any->getIterator(null);
			$sr = $it->moveNext();
			$this->_currentRow++;
			return $sr;
		}
		else
		{
			return null;
		}
	}

 	function key()
 	{
 		return $this->_currentRow;
 	}
}
?>