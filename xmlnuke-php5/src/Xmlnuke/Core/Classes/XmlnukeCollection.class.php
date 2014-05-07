<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * Implements a collection of Xmlnuke Xml Objects.
 *
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Classes;

use DOMNode;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\XMLTransform;
use Xmlnuke\Util\XmlUtil;

class XmlnukeCollection
{
	/**
	 * @var array
	 */
	protected $_items;

	protected $_xmlTransform = XMLTransform::ALL;
	protected $_configTransform = "xmlnuke";

	/**
	 * @desc XmlnukeCollection Constructor
	 */
	public function __construct()
	{
		$this->_items = array();
	}

	/**
	 * @desc Add a child in current DocumentObject
	 * @param IXmlnukeDocumentObject $docobj
	 * @return void
 	 */
	public function addXmlnukeObject($docobj)
	{
		if (is_null($docobj))
		{
			throw new InvalidArgumentException("Parameter is null", 853);
		}
		else if (is_string($docobj))
		{
			$docobj = new XmlnukeText($docobj);
		}
		else if ($docobj == $this)
		{
			throw new InvalidArgumentException("You are adding to the document a instance from yourself", 853);
		}
		else if (!($docobj instanceof IXmlnukeDocumentObject) && !is_object($docobj) && !is_array($docobj))
		{
			throw new InvalidArgumentException(ucfirst(gettype($docobj)) . " is not a IXmlnukeDocumentObject, model, array or stdClass. ", 853);
		}
		$this->_items[] = $docobj;
	}

	/**
	 * @desc Method for process all XMLNukedocumentObjects in array.
	 * @param DOMNode $current
	 * @return void
	 * @internal IXmlnukeDocumentObject $item
	 */
	protected function generatePage($current)
	{
		if (!is_null($this->_items))
		{
			foreach( $this->_items as $item )
			{
				# Prepare
				if ($item instanceof XmlnukeCollection)
				{
					$item->setXMLTransform($this->_xmlTransform);
					$item->setConfigTransform($this->_configTransform);
				}

				# Transform
				if (($item instanceof IXmlnukeDocumentObject) && ($this->_xmlTransform != XMLTransform::Model))
				{
					$item->generateObject($current);
				}
				elseif (($item instanceof XmlnukeCollection) && ($this->_xmlTransform != XMLTransform::IXMLNukeDocumentObject))
				{
					$item->generatePage($current);
				}
				elseif (!($item instanceof IXmlnukeDocumentObject) && ($this->_xmlTransform != XMLTransform::IXMLNukeDocumentObject))
				{
					$objHandler = new \Xmlnuke\Core\Engine\ObjectHandler($current, $item, $this->_configTransform);
					$objHandler->CreateObjectFromModel();
				}
			}
		}
	}

	/**
	 * Define WHAT objects the system will process.
	 * @param XMLTransform $method
	 */
	function setXMLTransform($method)
	{
		$this->_xmlTransform = $method;
	}

	/**
	 * Define WHAT prefix in comment will be used
	 * @param string $value
	 */
	function setConfigTransform($value)
	{
		$this->_configTransform = $value;
	}

}

?>