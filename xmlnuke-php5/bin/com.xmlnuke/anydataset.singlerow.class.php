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

/**
* SingleRow class represent an unique anydataset row.
*/
class SingleRow
{
	/**
	 * DOMNode represents a SingleRow
	 * @var DOMNode
	 */
	private $_node = null;

	private $_row = null;
	private $_originalRow = null;

	/**
	* SingleRow constructor
	* @param array()
	*/
	public function SingleRow($array = null)
	{
		if (is_null($array))
		{
			$array = array();
		}

		if (!is_array($array))
		{
			throw new XMLNukeException("SingleRow construct expects an array");
		}

		$this->_row = $array;
		$this->acceptChanges();
	}

	/**
	* Add a string field to row
	* @param string $name
	* @param string $value
	*/
	public function AddField($name, $value)
	{
		if (!array_key_exists($name, $this->_row))
		{
			$this->_row[$name] = $value;
		}
		elseif (is_array($this->_row[$name]))
		{
			$this->_row[$name][] = $value;
		}
		else
		{
			$this->_row[$name] = array($this->_row[$name], $value);
		}
		$this->informChanges();
	}
	/**
	*@param string $name - Field name
	*@return string
	*@desc et the string value from a field name
	*/
	public function getField($name)
	{
		$result = $this->_row[$name];
		if (is_array($result))
		{
			return $result[0];
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Get array from a single field
	 *
	 * @param string $name
	 * @return array
	 */
	public function getFieldArray($name)
	{
		$result = $this->_row[$name];
		if (($result == null) || ($result == ""))
		{
			return array();
		}
		elseif (is_array($result))
		{
			return $result;
		}
		else
		{
			return array($result);
		}
	}

	/**
	* Return all Field Names from current SingleRow
	* @return array
	*/
	public function getFieldNames()
	{
		return array_keys($this->_row);
	}

	/**
	* Set a string value to existing field name
	* @param string $name
	* @param string $value
	*/
	public function setField($name, $value)
	{
		if (!array_key_exists($name, $this->_row))
		{
			$this->AddField($name, $value);
		}
		else
		{
			$this->_row[$name] = $value;
		}
		$this->informChanges();
	}

	/**
	* Remove specified field name from row.
	* @param string $name
	*/
	public function removeFieldName($name)
	{
		if (array_key_exists($name, $this->_row))
		{
			unset($this->_row[$name]);
			$this->informChanges();
		}
	}

	/**
	* Remove specified field name with specified value name from row.
	* @param string $name
	*/
	public function removeFieldNameValue ($name, $value)
	{
		$result = $this->_row[$name];
		if (!is_array($result))
		{
			if ($value == $result)
			{
				unset($this->_row[$name]);
				$this->informChanges();
			}
		}
		else
		{
			for($i=sizeof($result)-1;$i>=0;$i--)
			{
				if ($result[$i] == $value)
				{
					unset($this->_row[$name][$i]);
					$this->informChanges();
				}
			}
		}
	}

	/**
	 * Update a specific field and specific value with new value
	 *
	 * @param String $name
	 * @param String $oldvalue
	 * @param String $newvalue
	 */
	public function setFieldValue ($name, $oldvalue, $newvalue)
	{
		$result = $this->_row[$name];
		if (!is_array($result))
		{
			if ($oldvalue == $result)
			{
				$this->_row[$name] = $newvalue;
				$this->informChanges();
			}
		}
		else
		{
			for($i=sizeof($result)-1;$i>=0;$i--)
			{
				if ($result[$i] == $oldvalue)
				{
					$this->_row[$name][$i] = $newvalue;
					$this->informChanges();
				}
			}
		}
	}

	/**
	* Get the DOMNode row objet
	* @return DOMNode
	*/
	public function getDomObject()
	{
		if ($this->_node == null)
		{
			$this->_node = XmlUtil::CreateXmlDocumentFromStr("<row />");
			$root = $this->_node->getElementsByTagName( "row" )->item ( 0 );
			foreach($this->_row as $key=>$value)
			{
				if (!is_array($value))
				{
					$field = XmlUtil::CreateChild($root, "field", $value);
					XmlUtil::AddAttribute($field, "name", $key);
				}
				else
				{
					foreach($value as $valueItem)
					{
						$field = XmlUtil::CreateChild($root, "field", $valueItem);
						XmlUtil::AddAttribute($field, "name", $key);
					}
				}
			}
		}
		return $this->_node;
	}

	/**
	 *
	 * @return array
	 */
	public function getRawFormat()
	{
		return $this->_row;
	}

	/**
	 * @return array
	 */
	public function getOriginalRawFormat()
	{
		return $this->_originalRow;
	}

	/**
	 *
	 * @return bool
	 */
	public function hasChanges()
	{
		return ($this->_row != $this->_originalRow);
	}

	/**
	 *
	 * @return bool
	 */
	public function acceptChanges()
	{
		$this->_originalRow = $this->_row;
	}

	/**
	 *
	 * @return bool
	 */
	public function rejectChanges()
	{
		$this->_row = $this->_originalRow;
	}

	protected function informChanges()
	{
		$this->_node = null;
	}
}
?>
