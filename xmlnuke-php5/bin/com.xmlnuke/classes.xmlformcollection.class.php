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
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlFormCollection extends XmlnukeCollection implements IXmlnukeDocumentObject
{
	/**
	*@var string
	*/
	protected $_action;
	/**
	*@var string
	*/
	protected $_title;
	/**
	*@var string
	*/
	protected $_formname;
	/**
	*@var bool
	*/
	protected $_jsValidate;
	/**
	*@var char
	*/
	protected $_decimalSeparator;
	/**
	*@var DATEFORMAT
	*/
	protected $_dateformat;
	/**
	*@var Context
	*/
	protected $_context;
	/**
	*@var string
	*/
	protected $_target = "";
	/**
	 * @var XmlnukeAjaxCallback
	 */
	protected $_ajaxcallback = "";
	/**
	 * @var string
	 */
	protected $_customSubmit = "";

	/**
	*@desc XmlFormCollection construction
	*@param Context $context
	*@param string $action
	*@param string $title
	*/
	public function XmlFormCollection($context, $action, $title)
	{
		parent::XmlnukeCollection();
		$this->_context = $context;
		$this->_action = $action;
		$this->_title = $title;
		$this->_formname = "frm" . $this->_context->getRandomNumber(10000);
		$this->_jsValidate = true;
		$this->_decimalSeparator = $this->_context->Language()->getDecimalPoint();
		$this->_dateformat = $this->_context->Language()->getDateFormat();
	}

	/**
	*@desc Set this form as required
	*@param bool $enable
	*@return void
	*/
	public function setJSValidate($enable)
	{
		$this->_jsValidate = $enable;
	}

	/**
	*@desc Set this form as required
	*@param bool $enable
	*@return void
	*/
	public function setFormName($name)
	{
		$this->_formname = $name;
	}

	/**
	*@desc Set this form as required
	*@param char $separator
	*@return void
	*/
	public function setDecimalSeparator($separator)
	{
		$this->_decimalSeparator = $separator;
	}

	/**
	*@desc Set this form as required
	*@param DATEFORMAT $format
	*@return void
	*/
	public function setDateFormat($format)
	{
		$this->_dateformat = $format;
	}

	/**
	*@desc Which window the system will open.
	*@param string $target
	*@return void
	*/
	public function setTarget($target)
	{
		$this->_target = $target;
	}

	public function setAjaxCallback($objAjax)
	{
		if (!($objAjax instanceof XmlnukeAjaxCallback))
		{
			throw new Exception("Object must be an XmlnukeAjaxCallback");
		}
		else
		{
			$this->_ajaxcallback = $objAjax;
		}
	}

	public function setCustomSubmit($customSubmit)
	{
		$this->_customSubmit = $customSubmit;
	}

	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$nodeWorking = XmlUtil::CreateChild($current, "editform", "");
		XmlUtil::AddAttribute($nodeWorking, "action", $this->_action);
		XmlUtil::AddAttribute($nodeWorking, "title", $this->_title);
		XmlUtil::AddAttribute($nodeWorking, "name", $this->_formname);
		if ($this->_target != "")
		{
			XmlUtil::AddAttribute($nodeWorking, "target", $this->_target);
		}
		if ($this->_jsValidate)
		{
			XmlUtil::AddAttribute($nodeWorking, "jsvalidate", "true");
			XmlUtil::AddAttribute($nodeWorking, "decimalseparator", $this->_decimalSeparator);
			XmlUtil::AddAttribute($nodeWorking, "dateformat", $this->_dateformat);
			$this->_customSubmit .= (($this->_customSubmit!="")?" &amp;amp;&amp;amp; ":"") . $this->_formname . "_checksubmit()";
		}

		if ($this->_ajaxcallback != null)
		{
			$ajaxId = $this->_ajaxcallback->getId();
			$this->_customSubmit .= (($this->_customSubmit!="")?" &amp;amp;&amp;amp; ":"") . "AIM.submit(this, {'onStart' : startCallback$ajaxId, 'onComplete' : completeCallback$ajaxId})";
		}
		if ($this->_customSubmit != "")
		{
			XmlUtil::AddAttribute($nodeWorking, "customsubmit", $this->_customSubmit);
		}

		$this->generatePage($nodeWorking);
	}

}

?>