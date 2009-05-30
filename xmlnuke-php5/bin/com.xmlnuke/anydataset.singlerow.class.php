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
	private $_node;

	/**
	* SingleRow constructor
	* Xml Node represents a SingleRow
	* @param DOMNode
	*/
	public function SingleRow($node)
	{
		$this->_node = $node;
	}

	/**
	* Add a string field to row
	* @param string $name
	* @param string $value
	*/
	public function AddField($name, $value)
	{
		$nodeWorking = XmlUtil::CreateChild($this->_node, "field", $value);
		XmlUtil::AddAttribute($nodeWorking, "name", $name);
	}
	/**
	*@param string $name - Field name
	*@return string
	*@desc et the string value from a field name
	*/
	public function getField($name)
	{
		$node = XmlUtil::SelectSingleNode($this->_node,"field[@name='" . $name . "']");
		if ($node == null)
		{
			return "";
		}
		if ($node->childNodes->length > 1) 
		{
			return XmlUtil::innerText($node);
		}
		return $node->nodeValue;
	}

	/**
	* Get the NodeList from a single field. You need you when the field is repeated.
	* @param string $name
	* @return DOMNodeList
	*/
	public function getFieldNodes($name)
	{
		return XmlUtil::selectNodes($this->_node,"field[@name='" . $name . "']");
	}
	/**
	 * Get array from a single field
	 *
	 * @param string $name
	 * @return array
	 */
	public function getFieldArray($name)
	{
		$nodes = $this->getFieldNodes($name);
		$array = null;
		foreach($nodes as $node)
		{
			$array[]=$node->nodeValue;
		}
		return $array;
	}
	/**
	* Return all Field Names from current SingleRow
	* @return array
	*/
	public function getFieldNames()
	{
		$fields = XmlUtil::selectNodes($this->_node,"/field");
		$array = array();
		foreach($fields  as $field)
		{
			//$fieldname = XmlUtil::SelectSingleNode($field,"@name");
			$fieldname = $field->getAttribute ("name");
			if ($fieldname == null)
			{
				$array[] = "_NULL_";
			}
			else
			{
				$array[] = $fieldname;
			}
		}
		return $array ;
	}

	/**
	* Set a string value to existing field name
	* @param string $name
	* @param string $value
	*/
	public function setField($name, $value)
	{	
		$node = XmlUtil::SelectSingleNode($this->_node,"field[@name='" . $name . "']");
		if ($node != null)
		{
			$node->nodeValue  = $value;
		}
		else
		{
			$this->AddField($name, $value);
		}
	}

	/**
	* Remove specified field name from row.
	* @param string $name
	*/
	public function removeFieldName($name)
	{		
		$node = XmlUtil::SelectSingleNode($this->_node,"field[@name='" . $name .  "']");
		$this->removeField($node);
	}

	/**
	* Remove specified field name with specified value name from row.
	* @param string $name
	*/
	public function removeFieldNameValue ($name, $value)
	{		
		$array = $this->getFieldArray($name);
		
		if ($array)
		{
			foreach ($array as $numNode => $nodeValue)
			{
				if ($nodeValue == $value)
					break;
			}
	
			$nodes = $this->getFieldNodes($name);
			$cont = 0;
			foreach ($nodes as $node)
			{
				if ($cont == $numNode)
				{
					$this->removeField($node);
				}
				$cont++;
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
		$array = $this->getFieldArray($name);
		
		if ($array)
		{
			foreach ($array as $numNode => $nodeValue)
			{
				if ($nodeValue == $oldvalue)
					break;
			}
	
			$nodes = $this->getFieldNodes($name);
			$cont = 0;
			foreach ($nodes as $node)
			{
				if ($cont == $numNode)
				{
					if ($node != null)
					{
						$node->nodeValue  = $newvalue;
					}
					else
					{
						$this->AddField($name, $newvalue);
					}
					break;
				}
				$cont++;
			}
		}		
	}
	
	/**
	* Remove specified field node from row
	* @param DOMNode $node
	* @return DOMNode
	*/
	public function removeField($node)
	{
		if ($node != null)
		{
			$this->_node->removeChild($node);
		}
	}

	/**
	* Get the DOMNode row objet
	* @return DOMNode
	*/
	public function getDomObject()
	{
		return $this->_node;
	}
}
?>