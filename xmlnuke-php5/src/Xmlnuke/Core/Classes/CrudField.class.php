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
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Classes;

class  CrudField
{
	/**
	*@var string
	*/
	public $fieldName;
	/**
	*@var string
	*/
	public $fieldCaption;
	/**
	*@var XmlInputObjectType
	*/
	public $fieldXmlInput;
	/**
	*@var INPUTTYPE
	*/
	public $dataType;
	/**
	*@var int
	*/
	public $size;
	/**
	*@var int
	*/
	public $maxLength;
	/**
	*@var string
	*/
	public $rangeMin;
	/**
	*@var string
	*/
	public $rangeMax;
	/**
	*@var bool
	*/
	public $visibleInList;
	/**
	*@var bool
	*/
	public $editable;
	/**
	*@var bool
	*/
	public $required;
	/**
	*@var bool
	*/
	public $key;
	/**
	*@var string
	*/
	public $defaultValue;
	/**
	*@var array
	*/
	public $arraySelectList;
	/**
	*@var bool
	*/
	public $newColumn;
	/**
	* @var IEditListFormatter
	*/
	public $editListFormatter;
	/**
	 * @var IEditListFormatter
	 */
	public $editFormatter;
	/**
	 * @var IEditListFormatter
	 */
	public $saveDatabaseFormatter;

	public function __construct($newcolumn = true)
	{
		$this->newColumn = $newcolumn;
	}

	/**
	 * Factory to create CrudField Objects
	 *
	 * @param string $name
	 * @param string $caption
	 * @param INPUTTYPE $dataType
	 * @param XmlInputObjectType $xmlObject
	 * @param int $size
	 * @param int $maxLength
	 * @param bool $visible
	 * @param bool $required
	 * @return CrudField
	 */
	public static function Factory($name, $caption, $dataType, $xmlObject, $size, $maxLength, $visible, $required)
	{
		$fieldPage = new CrudField();
		$fieldPage->fieldName = $name;
		$fieldPage->fieldCaption = $caption;
		$fieldPage->key = false;
		$fieldPage->dataType = $dataType;
		$fieldPage->size = $size;
		$fieldPage->maxLength = $maxLength;
		$fieldPage->fieldXmlInput = $xmlObject ;
		$fieldPage->visibleInList = $visible;
		$fieldPage->editable = true;
		$fieldPage->required = $required;
		return $fieldPage;
	}

	/**
	 * Factory to create CrudField Objects
	 *
	 * @param string $name
	 * @param string $caption
	 * @param int $maxLength
	 * @param bool $visible
	 * @param bool $required
	 * @return CrudField
	 */
	public static function FactoryMinimal($name, $caption, $maxLength, $visible, $required)
	{
		return CrudField::Factory($name, $caption, INPUTTYPE::TEXT, XmlInputObjectType::TEXTBOX, $maxLength, $maxLength, $visible, $required);
	}
}

?>