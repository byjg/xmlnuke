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

namespace Xmlnuke\Core\Engine;

use ReflectionClass;
use ReflectionProperty;

class Object
{

	/**
	 *
	 * @param mixed $source
	 */
	public function bind($source)
	{
		$this->bindObject($source, $this);
	}

	/**
	 *
	 * @param mixed $target
	 */
	public function bindTo($target)
	{
		$this->bindObject($this, $target);
	}

	/**
	 *
	 * @param mixed $source
	 * @param mixed $target
	 */
	public function bindObject($source, $target)
	{
		if ($source instanceof \stdClass)
		{
			$this->bindStdClass($source, $target);
		}
		else if (is_array($source))
		{
			$this->bindArray($source, $target);
		}
		else if ($source instanceof \Xmlnuke\Core\AnyDataset\SingleRow)
		{
			$this->bindArray($source->getRawFormat(), $target);
		}
		else if ($source instanceof \Xmlnuke\Core\AnyDataset\IIterator)
		{
			if ($source->hasNext())
			{
				$this->bindArray($source->moveNext()->getRawFormat(), $target);
			}		
		}
		else if ($source instanceof Context)
		{
			$this->bindContext($source, $target);
		}
		else
		{
			$this->bindGeneralObject($source, $target);
		}
	}


	/**
	 *
	 * @param mixed $source
	 * @param mixed $target
	 */
	protected function bindGeneralObject($source, $target)
	{
		$class = new ReflectionClass(get_class($source));
		$properties = $class->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );

		if (is_null($properties))
			return;

		foreach ($properties as $prop)
		{
			$propName = $prop->getName();

			// Ignore property from BaseModel
			if ($propName == "_propertyPattern")
				continue;

			// Remove Prefix "_" from Property Name to find a value
			if ($propName[0] == "_")
			{
				$propName = substr($propName, 1);
			}

			// Try to get the SOURCE Value
			$sourceValue = $this->getPropValue($source, $prop, $propName);

			// Set the Value
			if ($sourceValue != null)
			{
				$this->setPropValue($target, $propName, $sourceValue);
			}
		}
	}

	/**
	 *
	 * @param \stdClass $source
	 * @param mixed $target
	 */
	protected function bindStdClass($source, $target)
	{
		$properties = get_object_vars($source);

		foreach ($properties as $propName => $sourceValue)
		{
			$this->setPropValue($target, $propName, $sourceValue);
		}
	}

	/**
	 *
	 * @param \Xmlnuke\Core\AnyDataset\SingleRow $source
	 * @param mixed $target
	 */
	protected function bindArray($source, $target)
	{
		foreach ($source as $propName=>$value)
		{
			$this->setPropValue($target, $propName, $value);
		}
	}

	/**
	 *
	 * @param Context $source
	 * @param mixed $target
	 */
	protected function bindContext($source, $target)
	{
		foreach ($_REQUEST as $propName => $value)
		{
			$this->setPropValue($target, $propName, $value);
		}
	}

	/**
	 *
	 * @param mixed $obj
	 * @param \ReflectionProperty $prop
	 * @param string $propName
	 * @return null
	 */
	protected function getPropValue($obj, $prop, $propName)
	{
		if (method_exists($obj, "getPropertyPattern"))
		{
			$propertyPattern = $obj->getPropertyPattern();
			if ($propertyPattern != null)
				$propName = preg_replace($propertyPattern[0], $propertyPattern[1], $propName);
		}
		
		if (method_exists($obj, 'get' . $propName))
		{
			return $obj->{'get' . $propName}();
		}
		else if ($prop == null)
		{
			return $obj->{$propName};
		}
		else
		{
			return $prop->getValue($obj);
		}

		return null;
	}

	/**
	 *
	 * @param mixed $obj
	 * @param string $propName
	 * @param string $value
	 */
	protected function setPropValue($obj, $propName, $value)
	{
		if (method_exists($obj, "getPropertyPattern"))
		{
			$propertyPattern = $obj->getPropertyPattern();
			if ($propertyPattern != null)
				$propName = preg_replace($propertyPattern[0], $propertyPattern[1], $propName);
		}
		
		if ($obj instanceof Context)
		{
			$obj->set($propName, $value);
		}
		else if ($obj instanceof \Xmlnuke\Core\AnyDataset\SingleRow)
		{
			$obj->setField($propName, $value);
		}
		else if (method_exists($obj, 'set' . $propName))
		{
			$obj->{'set' . $propName}($value);
		}
		else if (property_exists($obj, $propName))
		{
			$obj->{$propName} = $value;
		}
	}

}
