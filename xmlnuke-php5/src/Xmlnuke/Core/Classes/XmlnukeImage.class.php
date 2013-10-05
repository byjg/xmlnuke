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
namespace Xmlnuke\Core\Classes;

class  XmlnukeImage extends XmlnukeDocumentObject 
{
	/**
	*@var string
	*/
	private $_id;
	/**
	*@var string
	*/
	private $_src;
	/**
	*@var string
	*/
	private $_alt;
	/**
	*@var int
	*/
	private $_width;
	/**
	*@var int
	*/
	private $_height;
	/**
	 * @var string
	 */
	private $_alternateImage;
	
	/**
	*@desc XmlnukeImage constructor
	*@param string $src
	*@param string $text
	*/
	public function __construct($src, $text = "")
	{
		$this->_src = str_replace("&", "&amp;", $src);
		$this->_alt = $text;
		$this->_id = "";
	}

	/**
	 * @desc set ID
	 * @param string $text
	 * @return void
	*/
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	*@desc set image $text
	*@param string $text
	*@return void
	*/
	public function setText($text)
	{
		$this->_alt = $text;
	}
	
	/**
	*@desc set image dimensions
	*@param int $width
	*@param int $height
	*@return void
	*/
	public function setDimension( $width, $height)
	{
		$this->_width = $width;
		$this->_height = $height;
	}

	/**
	 * @param $src
	 */
	public function setAlternateImage($src)
	{
		$this->_alternateImage = $src;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param \DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$nodeWorking = XmlUtil::CreateChild($current, "img", "");
		
		$link = $this->_src;
		XmlUtil::AddAttribute($nodeWorking, "src", $link);
		XmlUtil::AddAttribute($nodeWorking, "alt", $this->_alt);
		if (!empty($this->_id))
		{
			XmlUtil::AddAttribute($nodeWorking, "id", $this->_id);
		}
		if ($this->_width != 0)
		{
			XmlUtil::AddAttribute($nodeWorking, "width", $this->_width);
		}
		if ($this->_height != 0)
		{
		 	XmlUtil::AddAttribute($nodeWorking, "height", $this->_height);
		}
		if ($this->_alternateImage != "")
		{
		 	XmlUtil::AddAttribute($nodeWorking, "altimage", $this->_alternateImage);
		}
	}

}
?>