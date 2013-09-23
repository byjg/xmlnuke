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

// <editor-fold desc="EditList enum">

namespace Xmlnuke\Core\Enum;

/**
 * @package xmlnuke
 */
class CustomButtons
{
	/**
	*@var bool
	*/
	public $enabled;
	/**
	*@var string
	*/
	public $url;
	/**
	*@var string
	*/
	public $icon;
	/**
	*@var string
	*/
	public $action;
	/**
	*@var string
	*/
	public $alternateText;
	/**
	*@var MultipleSelectType
	*/
	public $multiple = MultipleSelectType::ONLYONE;
	/**
	*@var string
	*/
	public $message = "";
}

/**
 * @package xmlnuke
 */
class MultipleSelectType
{
	const NONE = 0;
	const ONLYONE = 1;
	const MULTIPLE = 2;
}

/**
 * @package xmlnuke
 */
class SelectType
{
	const RADIO = 1;
	const CHECKBOX = 2;
}

/**
 * @package xmlnuke
 */
class EditListFieldType
{
	const TEXT = 1;
	const IMAGE = 2;
	const LOOKUP = 3;
	const FORMATTER = 4;
	const CUSTOM = 99;
}

/**
 * @package xmlnuke
 */
class EditListFieldSummary
{
	const NONE = 0;
	const SUM = 1;
	const AVG = 2;
	const COUNT = 3;
}

// </editor-fold>

// <editor-fold desc="XmlnukeCollection enum">

/**
 * @package xmlnuke
 */
class XMLTransform
{
	const ALL = "";
	const IXMLNukeDocumentObject = "1";
	const Model = "2";
}

/**
 * @package xmlnuke
 */
class Menus
{
	public $id;
	public $title;
	public $summary;
	public $icon;
}

/**
 * @package xmlnuke
 */
class MenuGroup
{
	public $menuTitle;
	/**
	 * Enter description here...
	 *
	 * @var Menus[]
	 */
	public $menus;
}

/**
 * @package xmlnuke
 */
class Script
{
	public $source;
	public $file;
	public $location;
}

// </editor-fold>

// <editor-fold desc="Generic Types for Classes">

/**
 * @package xmlnuke
 */
class DATEFORMAT
{
	const DMY = 0;
	const MDY = 1;
	const YMD = 2;
}

/**
 * @package xmlnuke
 */
class INPUTTYPE
{
	const TEXT = 0;
	const LOWER = 1;
	const UPPER = 2;
	const NUMBER = 3;
	const DATE = 4;
	const DATETIME = 5;
	const UPPERASCII = 9;
	const EMAIL = 10;
}

// </editor-fold>

// <editor-fold desc="BaseModule enum">

/**
 * @package xmlnuke
 */
class ModuleAction
{
	const Create = 'new';
	const CreateConfirm = 'action.CREATECONFIRM';
	const Edit = 'edit';
	const EditConfirm = 'action.EDITCONFIRM';
	const Listing = 'action.LIST';
	const View = 'view';
	const Delete = 'delete';
	const DeleteConfirm = 'action.DELETECONFIRM';
}

/**
 * @package xmlnuke
 */
class AccessLevel
{
	const OnlyAdmin = 0;
	const OnlyCurrentSite = 1;
	const OnlyRole = 2;
	const OnlyAuthenticated= 3;
	const CurrentSiteAndRole = 4;
}

/**
 * @package xmlnuke
 */
class SSLAccess
{
	const Wherever = 0;
	const ForceSSL = 1;
	const ForcePlain = 2;
}

// </editor-fold>

// <editor-fold desc="ProcessPage enum">

/**
 * @package xmlnuke
 */
class XmlInputObjectType
{
	const TEXTBOX = 1;
	const PASSWORD = 2;
	const CHECKBOX = 3;
	const RADIOBUTTON = 4;
	const MEMO = 5;
	const HIDDEN = 6;
	const SELECTLIST = 7;
	const DUALLIST = 8;
	const HTMLTEXT = 9;
	const TEXTBOX_AUTOCOMPLETE = 10;
	const DATE = 11;
	const DATETIME = 12;
	const FILEUPLOAD = 13;
	const CUSTOM = 100; // This $fields need be created by the user
}

// </editor-fold>

// <editor-fold desc="DateUtil enum">
class DateParts
{
	const FULL="full";
	const TIME="time";
	const HOUR="hour";
	const MINUTE="minute";
	const SECOND="second";
	const DATE="date";
	const DAY="day";
	const MONTH="month";
	const YEAR="year";
}
// </editor-fold>

// <editor-fold desc="UsersBase enum">

/**
 * Constants for the most common custom property values.
 * @package xmlnuke
 */
class UserProperty
{
	const Site = "editsite";
	const Role = "roles";

	/**
	 * Get a User property from property name
	 *
	 * @param UserProperty $userProp
	 * @return string
	 */
	public static function getPropertyNodeName($userProp)
	{
		$result = $userProp;

		switch ($userProp)
		{
			case UserProperty::Site:
			{
				$result = "editsite";
				break;
			}
			case UserProperty::Role:
			{
				$result = "roles";
				break;
			}
		}
		return $result;
	}

}

/**
 * Structure to represent the users in XMLNuke
 * @package xmlnuke
 */
class UserTable
{
	public $Table;
	public $Id;
	public $Name ;
	public $Email;
	public $Username ;
	public $Password ;
	public $Created;
	public $Admin ;
}

/**
 * Structure to represent the user's custom values in XMLNuke
 * @package xmlnuke
 */
class CustomTable
{
	public $Table;
	public $Id;
	public $Name;
	public $Value;
}

/**
 * Structure to represent the user roles used in XMLNuke.
 * @package xmlnuke
 */
class RolesTable
{
	public $Table;
	public $Site;
	public $Role;
}



// </editor-fold>

// <editor-fold desc="FixedTextFileDataSet enum">

/**
 * @package xmlnuke
 */
class FixedTextDefinition
{
	public $fieldName;
	public $startPos;
	public $endPos;
	public $requiredValue;
	public $subTypes = array();

	/**
	 *
	 * @param string $fieldName
	 * @param int $startPos
	 * @param int $endPos
	 * @param bool $requiredValue
	 * @param array_of_FixedTextDefinition $subTypes
	 */
	public function __construct($fieldName, $startPos, $endPos, $requiredValue = "", $subTypes = null)
	{
		$this->fieldName = $fieldName;
		$this->startPos = $startPos;
		$this->endPos = $endPos;
		$this->requiredValue = $requiredValue;
		$this->subTypes = $subTypes;
	}
}

// </editor-fold>

// <editor-fold desc="IteratorFilter">

/**
 * Constants to represent relational operators.
 *
 * Use this in AddRelation method.
 * @package xmlnuke
 */
class Relation
{
	/**
	 * "Equal" relational operator
	 */
	const Equal = 0;

	/**
	 * "Less than" relational operator
	 */
	const LessThan = 1;

	/**
	 * "Greater than" relational operator
	 */
	const GreaterThan = 2;

	/**
	 * "Less or Equal Than" relational operator
	 */
	const LessOrEqualThan = 3;
	/**
	 * "Greater or equal than" relational operator
	 */
	const GreaterOrEqualThan = 4;
	/**
	 * "Not equal" relational operator
	 */
	const NotEqual = 5;
	/**
	 * "Starts with" unary comparator
	 */
	const StartsWith = 6;
	/**
	 * "Contains" unary comparator
	 */
	const Contains = 7;
}

// </editor-fold>

// <editor-fold desc="EasyListType.">

/**
 * @package xmlnuke
 */
class EasyListType
{
	const CHECKBOX = 1;
	const RADIOBOX = 2;
	const SELECTLIST = 3;
	const UNORDEREDLIST = 4;
	const SELECTIMAGELIST = 5;
}

// </editor-fold>

// <editor-fold desc="BlockCollection">

/**
 * @package xmlnuke
 */
class BlockPosition
{
	const Left = 1;
	const Center = 2;
	const Right = 3;
}

// </editor-fold>

// <editor-fold desc="InputTextBox">

/**
 * @package xmlnuke
 */
class InputTextBoxType
{
	const TEXT = 1;
	const PASSWORD = 2;
}

// </editor-fold>

// <editor-fold desc="Sortable">

/**
 * @package xmlnuke
 */
class SortableListItemState
{
	const Normal="";
	const Highligth="highlight";
	const Disabled="disabled";
}

// </editor-fold>

// <editor-fold desc="UIAlert">

/**
 * @package xmlnuke
 */
class UIAlert
{
	const Dialog = "dialog";
	const ModalDialog = "modaldialog";
	const BoxInfo = "boxinfo";
	const BoxAlert = "boxalert";
}

/**
 * @package xmlnuke
 */
class UIAlertOpenAction
{
	const URL = "url";
	const Image = "image";
	const Button = "button";
	const NoAutoOpen = "noautoopen";
	const None = "";
}


// </editor-fold>

// <editor-fold desc="Input Button">

/**
 * @package xmlnuke
 */
class ButtonType
{
	const SUBMIT = 1;
	const RESET = 2;
	const CLICKEVENT = 3;
	const BUTTON = 4;
}

/**
 * @package xmlnuke
 */
class InputButton
{
	/**
	*@var ButtonType
	*/
	public $buttonType;
	/**
	*@var string
	*/
	public $name;
	/**
	*@var string
	*/
	public $caption;
	/**
	*@var string
	*/
	public $onClick;
}

// </editor-fold>

// <editor-fold desc="SQLType">
/**
 * @package xmlnuke
 */
class SQLType
{
	const SQL_UPDATE = 1;
	const SQL_INSERT = 2;
	const SQL_DELETE = 3;
}

// </editor-fold>

// <editor-fold desc="SQLFieldType">

/**
 * @package xmlnuke
 */
class SQLFieldType
{
	const Literal = 'F';
	const Text = 'T';
	const Number = 'N';
	const Date = 'D';
	const Boolean = 'B';
}

// </editor-fold>

// <editor-fold desc="">

// </editor-fold>


?>