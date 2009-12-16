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
class XmlnukeMediaGallery extends XmlnukeCollection implements IXmlnukeDocumentObject
{
	/**
	 * @var Context
	 */
	protected $_context;

	protected $_name = "";
	protected $_api = false;
	protected $_visible = true;
	protected $_showCaptionOnThumb = false;

	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function XmlnukeMediaGallery($context, $name = "")
	{
		$this->_context = $context;
		$this->_name = $name;
		if ($this->_name == "")
		{
			$this->_name = "gallery_" . rand(1000, 9999);
		}
	}

	public function getName()
	{
		return $this->_name;
	}
	public function setName($value)
	{
		$this->_name = $value;
	}

	public function getApi()
	{
		return $this->_api;
	}
	public function setApi($value)
	{
		$this->_api = $value;
	}

	public function getVisible()
	{
		return $this->_visible;
	}
	public function setVisible($value)
	{
		$this->_visible = $value;
	}

	public function getShowCaptionOnThumb()
	{
		return $this->_showCaptionOnThumb;
	}
	public function setShowCaptionOnThumb($value)
	{
		$this->_showCaptionOnThumb = $value;
	}

	public function addXmlnukeObject($object)
	{
		if ($object instanceof XmlnukeMediaItem)
		{
			parent::addXmlnukeObject($object);
		}
		else
		{
			throw new XMLNukeException("Object need to an instance of XmlnukeMediaItem class");
		}
	}

	/**
	 * Create an Media Item of image Type
	 * @param $src
	 * @param $thumb
	 * @param $title
	 * @param $caption
	 * @param $width
	 * @param $height
	 * @return XmlnukeMediaItem
	 */
	public function addImage($src, $thumb="", $title = "", $caption="", $width="", $height="")
	{
		$this->addXmlnukeObject(XmlnukeMediaItem::ImageFactory($src, $thumb, $title, $caption, $width, $height));
	}

	/**
	 * Create an Media Item of Flash, Youtube or Quicktime
	 * @param $src
	 * @param $windowWidth
	 * @param $windowHeight
	 * @param $thumb
	 * @param $title
	 * @param $caption
	 * @param $width
	 * @param $height
	 * @return XmlnukeMediaItem
	 */
	public function addEmbed($src, $windowWidth, $windowHeight, $thumb="", $title = "", $caption="", $width="", $height="")
	{
		$this->addXmlnukeObject(XmlnukeMediaItem::EmbedFactory($src, $windowWidth, $windowHeight, $thumb, $title, $caption, $width, $height));
	}

	/**
	 * Create an Media Item of IFrame type
	 * @param $src
	 * @param $windowWidth
	 * @param $windowHeight
	 * @param $thumb
	 * @param $title
	 * @param $caption
	 * @param $width
	 * @param $height
	 * @return XmlnukeMediaItem
	 */
	public function addIFrame($src, $windowWidth, $windowHeight, $thumb="", $title = "", $caption="", $width="", $height="")
	{
		$this->addXmlnukeObject(XmlnukeMediaItem::IFrameFactory($src, $windowWidth, $windowHeight, $thumb, $title, $caption, $width, $height));
	}

	public function generateObject($current)
	{
		$mediaGallery = XmlUtil::CreateChild($current, "mediagallery");
		XmlUtil::AddAttribute($mediaGallery, "name", $this->_name);
		XmlUtil::AddAttribute($mediaGallery, "api", ($this->_api ? "true" : "false"));
		XmlUtil::AddAttribute($mediaGallery, "visible", ($this->_visible ? "true" : "false"));
		XmlUtil::AddAttribute($mediaGallery, "showthumbcaption", ($this->_showCaptionOnThumb ? "true" : "false"));
		$this->generatePage($mediaGallery);
	}

}









class XmlnukeMediaItem extends XmlnukeCollection implements IXmlnukeDocumentObject
{
	protected $_src;
	protected $_thumb;
	protected $_caption;
	protected $_title;
	protected $_width;
	protected $_height;

	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	protected function XmlnukeMediaItem($src, $thumb="", $title = "", $caption="", $width="", $height="")
	{
		$this->_src = $src;
		$this->_thumb = $thumb;
		$this->_caption = $caption;
		$this->_title = $title;
		$this->_width = $width;
		$this->_height = $height;
	}


	/**
	 * Create an Media Item of image Type
	 * @param $src
	 * @param $thumb
	 * @param $title
	 * @param $caption
	 * @param $width
	 * @param $height
	 * @return XmlnukeMediaItem
	 */
	public static function ImageFactory($src, $thumb="", $title = "", $caption="", $width="", $height="")
	{
		return new XmlnukeMediaItem($src, $thumb, $title, $caption, $width, $height);
	}

	/**
	 * Create an Media Item of Flash, Youtube or Quicktime
	 * @param $src
	 * @param $windowWidth
	 * @param $windowHeight
	 * @param $thumb
	 * @param $title
	 * @param $caption
	 * @param $width
	 * @param $height
	 * @return XmlnukeMediaItem
	 */
	public static function EmbedFactory($src, $windowWidth, $windowHeight, $thumb="", $title = "", $caption="", $width="", $height="")
	{
		if (strpos($src, "?") !== false)
		{
			$src .= "&amp;";
		}
		else
		{
			$src .= "?";
		}
		$src .= "width=$windowWidth&amp;height=$windowHeight";

		return new XmlnukeMediaItem($src, $thumb, $title, $caption, $width, $height);
	}

	/**
	 * Create an Media Item of IFrame type
	 * @param $src
	 * @param $windowWidth
	 * @param $windowHeight
	 * @param $thumb
	 * @param $title
	 * @param $caption
	 * @param $width
	 * @param $height
	 * @return XmlnukeMediaItem
	 */
	public static function IFrameFactory($src, $windowWidth, $windowHeight, $thumb="", $title = "", $caption="", $width="", $height="")
	{
		if (strpos($src, "?") !== false)
		{
			$src .= "&amp;";
		}
		else
		{
			$src .= "?";
		}
		$src .= "iframe=true&amp;width=$windowWidth&amp;height=$windowHeight";

		return new XmlnukeMediaItem($src, $thumb, $title, $caption, $width, $height);
	}

	public function generateObject($current)
	{
		$mediaGallery = XmlUtil::CreateChild($current, "mediaitem");
		XmlUtil::AddAttribute($mediaGallery, "src", $this->_src);
		XmlUtil::AddAttribute($mediaGallery, "thumb", $this->_thumb);
		XmlUtil::AddAttribute($mediaGallery, "title", $this->_title);
		XmlUtil::AddAttribute($mediaGallery, "caption", $this->_caption);
		XmlUtil::AddAttribute($mediaGallery, "width", $this->_width);
		XmlUtil::AddAttribute($mediaGallery, "height", $this->_height);
	}
}

?>