<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification and Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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

namespace Xmlnuke\Core\Database;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Xmlnuke\Core\AnyDataset\IIterator;
use Xmlnuke\Core\AnyDataset\SingleRow;
use Xmlnuke\Core\Engine\Context;

/**
 * @package xmlnuke
 */
abstract class BaseModel
{

	protected $_propertyPattern = array('/(\w*)/', '$1');

	/**
	 * Construct a model and optionally can set (bind) your properties base and the attribute matching from SingleRow, IIterator or the Xmlnuke Context
	 * @param Object $object
	 * @return void
	 */
	public function __construct($object=null)
	{
		if ($object instanceof SingleRow)
		{
			$this->bindSingleRow($object);
		}
		elseif ($object instanceof IIterator)
		{
			$this->bindIterator($object);
		}
		elseif ($object instanceof Context)
		{
			$this->bindFromContext($object);
		}
		else
		{
			$this->bindObject($object);
		}
	}


	/**
	 * This setter enable changes in how XMLNuke will match a property (protected) into your Getter or Setter
	 * @param $pattern
	 * @param $replace
	 * @return void
	 */
	public function setPropertyPattern($pattern, $replace)
	{
		$this->_propertyPattern = array(($pattern[0]!="/" ? "/" : "") . $pattern . ($pattern[strlen($pattern)-1]!="/" ? "/" : ""), $replace);
	}
	public function getPropertyPattern()
	{
		return $this->_propertyPattern;
	}

	/**
	 * Set the public properties based on the matching with the SingleRow->getField()
	 *
	 * @param SingleRow $sr
	 */
	public function bindSingleRow($sr)
	{
		if ($sr == null)
		{
			return;
		}
		elseif (!($sr instanceof SingleRow))
		{
			throw new InvalidArgumentException("I expected a SingleRow object");
		}

		$this->bindObject($sr);
	}

	/**
	 * Set the public properties based on the first iteration matching with IIterater->moveNext()->getField() 
	 *
	 * @param IIterator $it
	 */
	public function bindIterator($it)
	{
		if ($it->hasNext())
		{
			$sr = $it->moveNext();
			$this->bindSingleRow($sr);
		}
	}


	/**
	 * Set the public properties based on the Get/Post request defined in the XMLnuke context;
	 *
	 * @param Context $context
	 */
	public function bindFromContext($context = null)
	{
		if ($context == null)
		{
			$context = Context::getInstance();
		}

		if (!($context instanceof Context))
		{
			throw new InvalidArgumentException("I expected a Context object");
		}

		$this->bindObject($context);
	}

	/**
	 * Set the public properties based on the public properties existing in the $object variable
	 *
	 * @param Object object
	 */
	protected function bindObject($object)
	{
		$class = new ReflectionClass(get_class($this));

		$properties = $class->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );

		if (!is_null($properties))
		{
			foreach ($properties as $prop)
			{
				$propName = $prop->getName();
				if ($propName == "_propertyPattern")
					continue;

				// Remove Prefix "_" from Property Name to find a value
				if ($propName[0] == "_")
				{
					$propName = substr($propName, 1);
				}

				if ($object instanceof SingleRow)
				{
					$propValue = $object->getField(strtolower($propName));
				}
				elseif ($object instanceof Context)
				{
					$propValue = $object->get($propName);
				}
				elseif (is_object($object) && method_exists($object, $propName))
				{
					$propValue = $object->{$propName}();
				}
				elseif (is_object($object) && method_exists($object, "get$propName"))
				{
					$getPropName = "get$propName";
					$propValue = $object->{$getPropName}();
				}
				else
				{
					$propValue = "";
				}

				// If exists value, set it;
				if ($propValue != "")
				{
					if ($prop->isPublic())
						$prop->setValue($object, $propValue);
					else
					{
						$setName = "set" . ucfirst(preg_replace($this->_propertyPattern[0], $this->_propertyPattern[1], $propName));
						if (method_exists($object, $setName))
							$object->{$setName}($propValue);
					}
				}
			}
		}
	}
}
?>
