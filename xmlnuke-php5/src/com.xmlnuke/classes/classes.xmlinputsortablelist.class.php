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

use ByJG\Util\XmlUtil;

/**
 * @package xmlnuke
 */
class XmlInputSortableList extends XmlnukeDocumentObject 
{
	protected $_items = array();
	protected $_name;
	protected $_caption;
	protected $_connectKey;
	protected $_columns = 1;
	protected $_fullSize = false;

	/**
	 *
	 * @param string $caption
	 * @param string $name
	 * @param int_or_array $columns
	 */
	public function  __construct($caption, $name, $columns = 1)
	{	
		$this->_name = $name;
		$this->_caption = $caption;

		if (is_array($columns))
		{
			$this->_columns = count($columns);
			foreach($columns as $value)
			{
				$this->_items[$value] = array();
			}
		}
		else
		{
			$this->_columns = $columns;
			for($i=0;$i<$columns;$i++)
			{
				$this->_items[$i] = array();
			}
		}
	}
	
	/**
	 * 
	 * @param string $key
	 * @param IXmlnukeDocumentObject $docobj
	 * @param SortableListItemState $state
	 * @param int $column
	 * @return unknown_type
	 */
	public function addSortableItem($key, $docobj, $state = "", $column = 0)
	{
		if (is_null($docobj) || !($docobj instanceof IXmlnukeDocumentObject)) 
		{
			throw new InvalidArgumentException("Object is null or not is IXmlnukeDocumentObject. Found object type: " . get_class($docobj), 853);
		}
		if (!array_key_exists($column, $this->_items))
		{
			throw new InvalidArgumentException("Column does not exists", 853);
		}
		$this->_items[$column][$key . "|" . $state] = $docobj;
	}
		
	public function addPortlet($key, $title, $docobj, $column = 0)
	{
		if (is_null($docobj) || !($docobj instanceof IXmlnukeDocumentObject))
		{
			throw new InvalidArgumentException("Object is null or not is IXmlnukeDocumentObject. Found object type: " . get_class($docobj), 853);
		}
		if (!array_key_exists($column, $this->_items))
		{
			throw new InvalidArgumentException("Column does not exists", 853);
		}
		$this->_items[$column][$key . "|portlet"] = array($title, $docobj);
	}

	public function getConnectKey()
	{
		if ($this->_connectKey == "")
		{
			$this->_connectKey = "connect" . rand(0, 9) . rand(1000, 9999);
		}
		return $this->_connectKey;
	}
	public function setConnectKey($value)
	{
		$this->_connectKey = $value;
	}

	public function getFullSize()
	{
		return $this->_fullSize;
	}
	public function setFullSize($value)
	{
		$this->_fullSize = $value;
	}

	public function generateObject($current)
	{
		$editForm = $current;
		while (($editForm != null) && ($editForm->tagName != "editform")) 
		{
			$editForm = $editForm->parentNode;
		} 
		
		if ($editForm == null)
		{
			throw new InvalidArgumentException("XmlInputSortableList must be inside a XmlFormCollection");
		}

		$node = XmlUtil::createChild($current, "sortablelist", "");
		XmlUtil::addAttribute($node, "name", $this->_name);
		XmlUtil::addAttribute($node, "caption", $this->_caption);
		XmlUtil::addAttribute($node, "connectkey", $this->getConnectKey());
		XmlUtil::addAttribute($node, "columns", $this->_columns);
		XmlUtil::addAttribute($node, "fullsize", ($this->_fullSize ? "true" : "false"));
		foreach ($this->_items as $index=>$column)
		{
			$columnNode = XmlUtil::createChild($node, "column", "");
			XmlUtil::addAttribute($columnNode, "id", $index);

			foreach ($column as $key=>$value)
			{
				$info = explode("|", $key);
				$nodeitem = XmlUtil::createChild($columnNode, "item", "");
				XmlUtil::addAttribute($nodeitem, "key", $info[0]);
				XmlUtil::addAttribute($nodeitem, "state", $info[1]);
				if (is_array($value))
				{
					XmlUtil::addAttribute($nodeitem, "title", $value[0]);
					$value[1]->generateObject($nodeitem);
				}
				else
				{
					$value->generateObject($nodeitem);
				}
			}
		}
	}
	
	/**
	 * @param string $duallistaname
	 * @return string[]
	 */
	public static function Parse($sortableName)
	{
		$context = Context::getInstance();
		
		$cols = $context->Value($sortableName. "_columns");
		
		if ($cols == "")
			return null;
		
		$arCols = explode("|", $cols);
		
		$ret = array();
		
		foreach($arCols as $col)
		{
			$value = $context->Value($sortableName. "_" . $col);
			$ret[$col] = explode("|", $value);
		}
		
		return $ret;
	}
	
}

?>