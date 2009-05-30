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


class TreeViewActionType
{
	const OpenUrl = 1;
	const OpenUrlInsideContainer = 2;
	const OpenUrlInsideFrame = 3;
	const OpenInNewWindow = 4;
	const ExecuteJS = 99;
}

class XmlnukeTreeview extends XmlnukeDocumentObject 
{
	/**
	 * @var Context
	 */
	protected $_context;
	
	protected $_title;
	
	protected $_collection = array();

	/**
	 * 
	 * @param Context $context
	 * @param string $title
	 */
	public function __construct($context, $title)
	{
		$this->_context = $context;
		$this->_title = $title;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$treeview = XmlUtil::CreateChild($current, "treeview");
		XmlUtil::AddAttribute($treeview, "title", $this->_title);
		
		foreach ($this->_collection as $value)
		{
			$value->generateObject($treeview);
		}
	}
	
	/**
	 * @param $object
	 * @return unknown_type
	 */
	public function addChild($object)
	{
		if ( ($object instanceof XmlnukeTreeViewFolder) || ($object instanceof XmlnukeTreeViewLeaf) ) 
		{
			$this->_collection[] = $object;
		}	
		else
		{
			throw new Exception("Object need to be a XmlnukeTreeViewLeaf or XmlnukeTreeViewFolder");
		}
	}
}



class XmlnukeTreeViewLeaf extends XmlnukeDocumentObject
{
	/**
	 * @var Context
	 */
	protected $_context;
	protected $_title;
	protected $_img;
	protected $_id;
	protected $_expanded = false;
	protected $_selected = false;
	
	/**
	 * @var TreeViewActionType
	 */
	protected $_action = "";
	protected $_actionText = "";
	protected $_location = "";
	
	
	
	protected $_NODE = "leaf";
	protected $_collection = array();
	
	/**
	 * 
	 * @param Context $context
	 * @param string $title
	 * @param string $img
	 * @param string $id
	 */
	public function __construct($context, $title, $img, $id = "")
	{
		$this->_context = $context;
		$this->_title = $title;
		$this->_img = $img;
		if ($id != "")
		{
			$this->_id = $id;
		}
		else
		{
			$this->_id = strtolower($this->_NODE) . ($context->getRandomNumber(8000)+1000);
		}
	}

	/**
	 * 
	 * @param bool $bool
	 */
	public function setSelected($bool)
	{
		$this->_selected = $bool;
	}
	public function getSelected()
	{
		return $this->_selected;
	}
	
	/**
	 * 
	 * @param TreeViewActionType $actionType
	 * @param string $actionText
	 * @param string $actionContainer
	 */
	public function setAction($actionType, $actionText, $actionContainer = "")
	{
		$this->_action = $actionType;
		$this->_actionText = $actionText;
		$this->_location = $actionContainer;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$leaf = XmlUtil::CreateChild($current, $this->_NODE);
		XmlUtil::AddAttribute($leaf, "title", $this->_title);
		XmlUtil::AddAttribute($leaf, "img", $this->_img);
		XmlUtil::AddAttribute($leaf, "code", $this->_id);
		if ($this->_expanded)
		{
			XmlUtil::AddAttribute($leaf, "expanded", "true");
		}
		
		if ($this->_selected)
		{
			XmlUtil::AddAttribute($leaf, "selected", "true");
		}
		
		if ($this->_action != "")
		{
			$jsAction = "";
			$processor = new ParamProcessor($this->_context);
			$url = $processor->GetFullLink($this->_actionText);
			switch ($this->_action)
			{
				case TreeViewActionType::OpenUrl:
					$jsAction = "window.location = '" . $url . "';";
					break;
					
				case TreeViewActionType::OpenUrlInsideContainer:
					$jsAction = "loadUrl('" . $this->_location . "', '" . $url . "');";
					break;

				case TreeViewActionType::OpenUrlInsideFrame:
					$jsAction = $this->_location . ".location = '" . $url . "';";
					break;
					
				case TreeViewActionType::OpenInNewWindow:
					$jsAction = "window.open('" . $url . "', '" . $this->_id . "', 'status=1,location=1;')";
					break;
					
				default:
					$jsAction = $this->_actionText;
					break;
			}

			XmlUtil::AddAttribute($leaf, "action", str_replace("&", "&amp;", $jsAction));
		}
		
		foreach ($this->_collection as $value)
		{
			$value->generateObject($leaf);
		}
	}	
}


class XmlnukeTreeViewFolder extends XmlnukeTreeViewLeaf
{
	/**
	 * 
	 * @param Context $context
	 * @param string $title
	 * @param string $img
	 * @param string $id
	 */
	public function __construct($context, $title, $img, $id = "")
	{
		$this->_NODE = "folder";
		parent::__construct($context, $title, $img, $id);
	}
	
	/**
	 * 
	 * @param bool $bool
	 */
	public function setExpanded($bool)
	{
		$this->_expanded = $bool;
	}
	public function getExpanded()
	{
		return $this->_expanded;
	}
	
	public function addChild($object)
	{
		if ( ($object instanceof XmlnukeTreeViewFolder) || ($object instanceof XmlnukeTreeViewLeaf) ) 
		{
			$this->_collection[] = $object;
		}	
		else
		{
			throw new Exception("Object need to be a XmlnukeTreeViewLeaf or XmlnukeTreeViewFolder");
		}
	}
}

?>