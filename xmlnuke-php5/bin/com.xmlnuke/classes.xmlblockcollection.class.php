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
 * @package xmlnuke
 */
class BlockPosition
{
	const Left = 1;
	const Center = 2;
	const Right = 3;
}
	
/**
 * @package xmlnuke
 */
class XmlBlockCollection extends XmlnukeCollection implements IXmlnukeDocumentObject 
{
	/**
	*@var string
	*/
	protected $_title;
	/**
	*@var BlockPosition
	*/
	protected $_position;

	/**
	*@desc XmlBlockCollection construction
	*@param string $title
	*@param BlockPosition $position
	*/
	public function __construct($title, $position)
	{
		parent::__construct();
		$this->_title = $title;
		$this->_position = $position;
	}

	public function setTitle($title)
	{
		$this->_title = $title;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$block = "";
		switch ($this->_position)
		{
			case BlockPosition::Center:
				$block = "blockcenter";
				break;
			case BlockPosition::Left:
				$block = "blockleft";
				break;
			case BlockPosition::Right:
				$block = "blockright";
				break;
		}
		$objBlockCenter = XmlUtil::CreateChild($current, $block, "");
		XmlUtil::CreateChild($objBlockCenter, "title", $this->_title);	
		parent::generatePage(XmlUtil::CreateChild($objBlockCenter, "body", ""));
	}

}

?>