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
 * @package xmlnuke
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
	 *
	 * @param Context $context
	 * @param string $name 
	 */
	public function __construct($context, $name = "")
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
			throw new InvalidArgumentException("Object need to an instance of XmlnukeMediaItem class");
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
		
		return $mediaGallery;
	}

}

?>