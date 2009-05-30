<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  Acknowledgments to: Roan Brasil Monteiro
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
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlNukeFlash extends XmlnukeCollection implements IXmlnukeDocumentObject 
{
	protected $_movie = "";
	
	protected $_width = "";

	protected $_height = "";
	
	protected $_extraParams = array();
	
	protected $_majorVersion;
	protected $_minorVersion;
	protected $_revision;
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function XmlNukeFlash($majorVersion=9, $minorVersion=0, $revision=45)
	{	
		$this->_majorVersion = $majorVersion;
		$this->_minorVersion = $minorVersion;
		$this->_revision = $revision;
	}
	
	public function setMovie($movie)
	{
		$this->_movie = $movie;
	}
	public function getMovie()
	{
		return str_replace(".swf", "", str_replace("&", "&amp;", str_replace("&amp;amp;","&amp;",$this->_movie)));
	}
	public function setWidth($width)
	{
		$this->_width = $width;
	}
	public function getWidth()
	{
		return $this->_width;
	}
	public function getHeight()
	{
		return $this->_height;
	}
	public function setHeight($height)
	{
		$this->_height = $height;
	}
	
	public function setDimension($width, $height)
	{
		$this->_width = $width;
		$this->_height = $height;
	}
	
	public function addParam($name, $value)
	{
		$this->_extraParams[$name] = $value;
	}

	
	public function generateObject($current)
	{
		$node = XmlUtil::CreateChild($current, "flash", "");
		XmlUtil::AddAttribute($node, "major", $this->_majorVersion);
		XmlUtil::AddAttribute($node, "minor", $this->_minorVersion);
		XmlUtil::AddAttribute($node, "revision", $this->_revision);
		
		if ($this->_movie != "")
		{
			XmlUtil::AddAttribute($node, "movie", $this->getMovie());
		}
		if ($this->_width != "")
		{
			XmlUtil::AddAttribute($node, "width", $this->getWidth());
		}
		if ($this->_height != "")
		{
			XmlUtil::AddAttribute($node, "height", $this->getHeight());
		}
		
		foreach ($this->_extraParams as $key=>$value) 
		{
			$param = XmlUtil::CreateChild($node, "param");
			XmlUtil::AddAttribute($param, "name", $key);
			XmlUtil::AddAttribute($param, "value", str_replace("&", "&amp;amp;", $value));
		}
				
		parent::generatePage($node);
	}
}

?>
