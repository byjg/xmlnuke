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

/**
 * @package xmlnuke
 */
class Oci8Iterator extends GenericIterator
{
	const RECORD_BUFFER = 50;
	private $_rowBuffer;
	protected $_currentRow = 0;
	protected $_moveNextRow = 0;

	/**
	*@var Oci8 Cursor
	*/
	private $_cursor;
	/**
	*@var Context
	*/
	private $_context;

	/**
	*@access public
	*@param PDOStatement $recordset
	*@param Context $context
	*@return void
	*/
	public function __construct($cursor, $context)
	{
		$this->_context = $context;
		$this->_cursor = $cursor;
		$this->_rowBuffer = array();
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
        if (count($this->_rowBuffer) >= Oci8Iterator::RECORD_BUFFER)
        {
            return true;
        }
        else if (is_null($this->_cursor))
		{
			return (count($this->_rowBuffer) > 0);
		}
		else 
		{
			$row = oci_fetch_array($this->_cursor, OCI_ASSOC+OCI_RETURN_NULLS);
			if ($row)
			{
				$sr = new SingleRow($row); // ForceUTF8\Encoding::toUtf8($row);

				$this->_currentRow++;

				// Enfileira o registo
				array_push($this->_rowBuffer, $sr);
				// Traz novos até encher o Buffer
				if (count($this->_rowBuffer) < DBIterator::RECORD_BUFFER)
				{
					$this->hasNext();
				}
				return true;
			}
			else
			{
				oci_free_statement($this->_cursor);
				$this->_cursor = null;
				return (count($this->_rowBuffer) > 0);
			}
		}
	}

	public function __destruct()
	{
		if (!is_null($this->_cursor))
		{
			oci_free_statement($this->_cursor);
			$this->_cursor = null;
		}
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		if (!$this->hasNext())
		{
			throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
		}
		else
		{
			$sr = array_shift($this->_rowBuffer);
			$this->_moveNextRow++;
			return $sr;
		}
	}

	function key()
 	{
 		return $this->_moveNextRow;
 	}
}
?>