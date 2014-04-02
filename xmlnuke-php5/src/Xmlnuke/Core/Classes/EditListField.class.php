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

use Xmlnuke\Core\Enum\EditListFieldSummary;
use Xmlnuke\Core\Enum\EditListFieldType;
use Xmlnuke\Core\Formatter\IEditListFormatter;

class  EditListField
{
	/**
	*@var string
	*/
	public $fieldData;
	/**
	*@var string
	*/
	public $editlistName;
	/**
	*@var EditListFieldType
	*/
	public $fieldType;
	/**
	*@var int
	*/
	public $maxSize;
	/**
	*@var array
	*/
	public $arrayLookup;
	/**
	*@var bool
	*/
	public $newColumn;
	/**
	 * @var IEditListFormatter
	 */
	public $formatter;
	/**
	 * @var EditListFieldSummary
	 */
	public $summary;

	public function __construct($new = true)
	{
		$this->newColumn = $new;
		$this->arrayLookup = array();
		$this->summary = EditListFieldSummary::NONE;
		$this->formatter = null;
	}
}


?>