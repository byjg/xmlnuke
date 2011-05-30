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
/**
 * @package xmlnuke
 */
class ProcessPageField
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
}


/**
 * @package xmlnuke
 */
class ProcessPageFields
{
	/**
	*@var Array
	*/
	protected $fields;

	public function __construct()
	{
		$this->fields = array();
	}

	/**
	 *
	 * @param ProcessPageField $p
	 * @return void
	 */
	public function addProcessPageField($p)
	{
		if ( ($p->fieldXmlInput == XmlInputObjectType::FILEUPLOAD) && ($p->saveDatabaseFormatter == null) )
		{
			throw new Exception("ProcessPageField FileUpload need be defined saveDatabaseFormatter. Did you try to use 'ProcessPageStateBaseSaveFormatterFileUpload' class?");
		}
		$this->fields[] = $p;
	}

	public function getProcessPageFields()
	{
		return new ArrayObject($this->fields);
	}

	/**
	 * Factory to create ProcessPageField Objects
	 *
	 * @param string $name
	 * @param string $caption
	 * @param INPUTTYPE $dataType
	 * @param XmlInputObjectType $xmlObject
	 * @param int $size
	 * @param int $maxLength
	 * @param bool $visible
	 * @param bool $required
	 * @return ProcessPageField
	 */
	public static function Factory($name, $caption, $dataType, $xmlObject, $size, $maxLength, $visible, $required)
	{
		$fieldPage = new ProcessPageField();
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
	 * Factory to create ProcessPageField Objects
	 *
	 * @param string $name
	 * @param string $caption
	 * @param int $maxLength
	 * @param bool $visible
	 * @param bool $required
	 * @return ProcessPageField
	 */
	public static function FactoryMinimal($name, $caption, $maxLength, $visible, $required)
	{
		return ProcessPageFields::Factory($name, $caption, INPUTTYPE::TEXT, XmlInputObjectType::TEXTBOX, $maxLength, $maxLength, $visible, $required);
	}
}


/**
 * Basic CRUD.
 * @package xmlnuke
 */
abstract class ProcessPageStateBase extends XmlnukeDocumentObject implements IProcessPageState
{
	const ACTION_LIST = "";
	const ACTION_NEW = "ppnew";
	const ACTION_EDIT = "ppedit";
	const ACTION_VIEW = "ppview";
	const ACTION_DELETE = "ppdelete";
	const ACTION_NEW_CONFIRM = "ppnew_confirm";
	const ACTION_EDIT_CONFIRM = "ppedit_confirm";
	const ACTION_DELETE_CONFIRM = "ppdelete_confirm";
	const ACTION_MSG = "ppmsgs";
	const PARAM_MSG = "ppmsgtext";
	const PARAM_CANCEL = "ppbtncancel";

	/**
	*@var string
	*/
	protected $_currentAction;
	/**
	*@var string
	*/
	protected $_nextAction;
	/**
	*@var string
	*/
	protected $_header;
	/**
	*@var string
	*/
	protected $_module;
	/**
	*@var array()
	*/
	protected $_keyIndex = array();
	/**
	*@var Context
	*/
	protected $_context;
	/**
	*@var array
	*/
	protected $_fields;
	/**
	*@var array
	*/
	protected $_buttons;
	/**
	*@var string
	*/
	protected $_filter;
	/**
	*@var string
	*/
	protected $_sort;
	/**
	*@var string
	*/
	protected $_valueId;
	/**
	*@var int
	*/
	protected $_curPage;
	/**
	*@var int
	*/
	protected $_qtdRows;
	/**
	*@var bool
	*/
	protected $_new;
	/**
	*@var bool
	*/
	protected $_view;
	/**
	*@var bool
	*/
	protected $_edit;
	/**
	*@var bool
	*/
	protected $_delete;
	/**
	*@var array
	*/
	protected $_parameter;

	/**
	 * @var char
	 */
	protected $_decimalSeparator;
	/**
	 * @var DATEFORMAT
	 */
	protected $_dateFormat;
	/**
	 * @var LanguageCollection
	 */
	protected $_lang;

	/**
	*@desc Constructor
	*@param Context $context XMLNuke context object
	*@param array fields Fields will be processed
	*@param string header Simple $header
	*@param string module Module will be process this request-> Usually is the same $module instantiate the ProcessPageStateDB
	*@param array $buttons Custom $buttons in View/Select mode
	*@return
	*/
	public function __construct($context, $fields, $header, $module, $buttons)
	{
		$this->_context = $context;
		$this->_fields = $fields->getProcessPageFields();
		$this->_buttons = $buttons;
		$this->_header = $header;
		$this->_module = $module;

		$this->_new = true;
		$this->_view = true;
		$this->_delete = true;
		$this->_edit = true;

		$this->_currentAction = $this->_context->ContextValue("acao");

		for($i=0, $fieldsLength = sizeof($this->_fields); $i<$fieldsLength; $i++)
		{
			if ($this->_fields[$i]->key)
			{
				$this->_keyIndex[] = $i;
			}
		}

		$this->_parameter = array();
		//$this->_filter = $this->_context->ContextValue("filter"); //encoded
		//$this->_sort = $this->_context->ContextValue("sort"); // encoded
		$this->_valueId = $this->_context->ContextValue("valueid");
		$this->_curPage = $this->_context->ContextValue("curpage");
		$this->_qtdRows = $this->_context->ContextValue("offset");

		$this->_decimalSeparator = $this->_context->Language()->getDecimalPoint();
		$this->_dateFormat = $this->_context->Language()->getDateFormat();
		$this->_lang = LanguageFactory::GetLanguageCollection($context, LanguageFileTypes::OBJECT, "com.xmlnuke.classes.processpagestatebase");
	}

	/**
	*@desc set page size
	*@param int $qtdRows
	*@param int $curPage
	*@return void
	*/
	public function setPageSize( $qtdRows, $curPage)
	{
		if ($curPage != 0)
		{
			$this->_curPage = $curPage;
		}
		$this->_qtdRows = $qtdRows;
	}

	/**
	*@desc set permissions
	*@param bool $newRec
	*@param bool $view
	*@param bool $edit
	*@param bool $delete
	*@return void
	*/
	public function setPermissions( $newRec,  $view,  $edit,  $delete)
	{
		$this->_new = $newRec;
		$this->_view = $view;
		$this->_delete = $delete;
		$this->_edit = $edit;
	}

	/**
	*@desc
	*@param
	*@return IIterator
	*/
	public function getAllRecords(){}


	/**
	*@desc
	*@param
	*@return SingleRow
	*/
	public function getCurrentRecord(){}


	/**
	*@desc
	*@return IXmlnukeDocumentObject
	*/
	public function updateRecord(){}


	/**
	*@desc
	*@param string $filter
	*@return void
	*/
	public function setFilter($filter)
	{
		$this->_filter = $filter;
	}

	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	public function getFilter()
	{
		return $this->_filter;
	}

	/**
	*@desc
	*@param string $sort
	*@return void
	*/
	public function setSort($sort)
	{
		$this->_sort = $sort;
	}

	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	public function getSort()
	{
		return $this->_sort;
	}

	/**
	 * Enter description here...
	 *
	 * @param char $decimalSeparator
	 * @param DATEFORMAT $dateFormat
	 */
	public function setFormParameters($decimalSeparator, $dateFormat)
	{
		$this->_decimalSeparator = $decimalSeparator;
		$this->_dateFormat = $dateFormat;
	}

	/**
	 * Adds extra information to process page. This information will be persisted during the requests.
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	*/
	public function addParameter($name, $value)
	{
		$this->_parameter[$name] = $value;
	}

	/**
	*@desc
	*@param string $action
	*@return void
	*/
	public function forceCurrentAction($action)
	{
		$this->_currentAction = $action;
	}

	/**
	*@desc
	*@param string $valueId
	*@return void
	*/
	public function forceCurrentValueId($valueId)
	{
		$this->_valueId = $valueId;
	}

	/**
	*@desc
	*@param
	*@return IXmlnukeDocumentObject
	*/
	public function validateUpdate()
	{
		if (($this->_currentAction != self::ACTION_EDIT_CONFIRM) && ($this->_currentAction != self::ACTION_NEW_CONFIRM))
		{
			return null;
		}

//		NameValueCollection $nvc = new NameValueCollection();
		$nvc = array();

		for($i=0, $fieldLength = sizeof($this->_fields); $i<$fieldLength; $i++)
		{
//			ProcessPageFields $field
			$field = $this->_fields[$i];
			$curValue = $this->_context->ContextValue($this->_fields[$i]->fieldName);

			if ($field->editable)
			{
				if ($this->_fields[$i]->fieldXmlInput == XmlInputObjectType::FILEUPLOAD)
				{
					continue; // Do not validate Upload Fields
				}
				else if (($curValue == "") && ($field->required))
				{
					$nvc["err" . $i] = $this->_lang->Value("ERR_REQUIRED", $field->fieldCaption);
				}
				else if ($this->_fields[$i]->dataType == INPUTTYPE::NUMBER)
				{
					$curValue = str_replace($this->_decimalSeparator, ".", $curValue);
					if (($curValue != "") && (!is_numeric($curValue)))
					{
						$nvc["err" . $i] = $this->_lang->Value("ERR_INVALIDNUMBER", $field->fieldCaption);
					}
				}
			}
		}

		if (sizeof($nvc)!=0)
		{
//			XmlParagraphCollection $p
			$p = new XmlParagraphCollection();
			$p->addXmlnukeObject(new XmlEasyList(EasyListType::UNORDEREDLIST, "Error", $this->_lang->Value("ERR_FOUND"), $nvc, ""));
//			XmlAnchorCollection $a
			$a = new XmlAnchorCollection("javascript:history.go(-1)","");
			$a->addXmlnukeObject(new XmlnukeText($this->_lang->Value("TXT_GOBACK")));
			$p->addXmlnukeObject($a);
			return $p;
		}
		else
		{
			return null;
		}
	}

	/**
	*@desc
	*@param
	*@return IXmlnukeDocumentObject
	*/
	protected function listAllRecords()
	{
//		XmlEditList $editList
		$editList = new XmlEditList($this->_context, $this->_header, $this->_module, false, false, false, false);
		$editList->setDataSource($this->getAllRecords());
		$editList->setPageSize($this->_qtdRows, $this->_curPage);
		$editList->setEnablePage(true);
		//$editList->addParameter("filter", $this->_filter);
		//$editList->addParameter("sort", $this->_sort);
		foreach ($this->_parameter as $key=>$value)
		{
			$editList->addParameter($key, $value);
		}

		if ($this->_new)
		{
			$cb = new CustomButtons();
			$cb->action = self::ACTION_NEW;
			$cb->alternateText = $this->_lang->Value("TXT_NEW");
			$cb->icon = "common/editlist/ic_novo.gif";
			$cb->enabled = true;
			$cb->multiple = MultipleSelectType::NONE;
			$editList->setCustomButton($cb);
		}
		if ($this->_view)
		{
			$cb = new CustomButtons();
			$cb->action = self::ACTION_VIEW;
			$cb->alternateText = $this->_lang->Value("TXT_VIEW");
			$cb->icon = "common/editlist/ic_detalhes.gif";
			$cb->enabled = true;
			$cb->multiple = MultipleSelectType::ONLYONE;
			$editList->setCustomButton($cb);
		}
		if ($this->_edit)
		{
			$cb = new CustomButtons();
			$cb->action = self::ACTION_EDIT;
			$cb->alternateText = $this->_lang->Value("TXT_EDIT");
			$cb->icon = "common/editlist/ic_editar.gif";
			$cb->enabled = true;
			$cb->multiple = MultipleSelectType::ONLYONE;
			$editList->setCustomButton($cb);
		}
		if ($this->_delete)
		{
			$cb = new CustomButtons();
			$cb->action = self::ACTION_DELETE;
			$cb->alternateText = $this->_lang->Value("TXT_DELETE");
			$cb->icon = "common/editlist/ic_excluir.gif";
			$cb->enabled = true;
			$cb->multiple = MultipleSelectType::ONLYONE;
			$editList->setCustomButton($cb);
		}

		if ($this->_buttons != null)
		{
			for($i=0, $buttonsLength = sizeof($this->_buttons); $i<$buttonsLength ;$i++)
			{
//				CustomButtons $cb;
				if (($this->_buttons[$i]->action != "") || ($this->_buttons[$i]->url != ""))
				{
					$cb = new CustomButtons();
					$cb->action = $this->_buttons[$i]->action;
					$cb->alternateText = $this->_buttons[$i]->alternateText;
					$cb->icon = $this->_buttons[$i]->icon;
					$cb->url = $this->_buttons[$i]->url;
					$cb->enabled = true;
					$cb->multiple = $this->_buttons[$i]->multiple;
					$editList->setCustomButton($cb);
				}
			}
		}


		$fldKey = "";
		for($i=0 , $current = 0; $i<sizeof($this->_keyIndex); $i++, $current++)
		{
			$fldKey .= (($fldKey != "") ? "|" : "") . $this->_fields[$this->_keyIndex[$i]]->fieldName;
		}
		$field = new EditListField();
		$field->fieldData = $fldKey;
		$field->editlistName = "#";
		$field->formatter = new ProcessPageStateBaseFormatterKey();
		$field->fieldType = EditListFieldType::FORMATTER;
		$editList->addEditListField($field);

		for($i=0, $fieldLength = sizeof($this->_fields); $i<$fieldLength; $i++, $current++)
		{
			if ($this->_fields[$i]->visibleInList)
			{
				$field = new EditListField();
				$field->fieldData = $this->_fields[$i]->fieldName;
				$field->editlistName = $this->_fields[$i]->fieldCaption;
				if ($this->_fields[$i]->fieldXmlInput == XmlInputObjectType::SELECTLIST)
				{
					$field->fieldType = EditListFieldType::LOOKUP;
					$field->arrayLookup = $this->_fields[$i]->arraySelectList;
				}
				elseif ($this->_fields[$i]->fieldXmlInput == XmlInputObjectType::DUALLIST)
				{
					$field->fieldType = EditListFieldType::FORMATTER;
					$field->formatter = new ProcessPageStateBaseFormatterDualList($this->_fields[$i]->arraySelectList);
				}
				else
				{
					$field->fieldType = EditListFieldType::TEXT;
				}
				$field->newColumn = $this->_fields[$i]->newColumn;
				if (!is_null($this->_fields[$i]->editListFormatter))
				{
					$field->formatter = $this->_fields[$i]->editListFormatter;
					$field->fieldType = EditListFieldType::FORMATTER;
				}

				$field = $this->editListFieldCustomize($field, $this->_fields[$i]);

				$editList->addEditListField($field);
			}
		}
		return $editList;
	}


	/**
	 *
	 * @param EditListField $editListField
	 * @param ProcessPageField $field
	 * @return EditListField
	 */
	protected function editListFieldCustomize($editListField, $field)
	{
		return $editListField;
	}


	/**
	*@desc
	*@param ProcessPageFields $field
	*@return bool
	*/
	protected function isReadOnly($field)
	{
		$formReadOnly = true;
		if ( ($this->_currentAction == self::ACTION_EDIT) || ($this->_currentAction == self::ACTION_NEW))
		{
			$formReadOnly = false;
		}

		$fieldReadOnly = (!$field->editable);

		return ($formReadOnly || $fieldReadOnly || ($field->key && $this->_currentAction != self::ACTION_NEW));
	}

	/**
	 * Enter description here...
	 *
	 * @return IXmlnukeDocumentObject
	 */
	protected function showResultMessage()
	{
		$msg = $this->_context->ContextValue(self::PARAM_MSG);

		if ($msg == self::ACTION_NEW_CONFIRM)
		{
			$message = $this->_lang->Value("MSG_NEW_SUCCESS");
		}
		else if ($msg == self::ACTION_EDIT_CONFIRM)
		{
			$message = $this->_lang->Value("MSG_EDIT_SUCCESS");
		}
		else if ($msg == self::ACTION_DELETE_CONFIRM)
		{
			$message = $this->_lang->Value("MSG_DELETE_SUCCESS");
		}
		else
		{
			$message = $this->_lang->Value("MSG_NOCHANGE");
		}

		$container = new XmlnukeUIAlert($this->_context, UIAlert::BoxInfo);
		$container->setAutoHide(8000);
		$container->addXmlnukeObject(new XmlnukeText($message, true, true, false));

		return $container;
	}


	/**
	*@desc
	*@param
	*@return XmlFormCollection
	*/
	protected function showCurrentRecord()
	{
		if ($this->_currentAction == self::ACTION_NEW)
		{
			$title = $this->_lang->Value("TITLE_NEW", $this->_header);
		}
		else if ($this->_currentAction == self::ACTION_EDIT)
		{
			$title = $this->_lang->Value("TITLE_EDIT", $this->_header);
		}
		else if ($this->_currentAction == self::ACTION_DELETE)
		{
			$title = $this->_lang->Value("TITLE_DELETE", $this->_header);
		}
		else if ($this->_currentAction == self::ACTION_VIEW)
		{
			$title = $this->_lang->Value("TITLE_VIEW", $this->_header);
		}

//		XmlFormCollection $form
		$form = new XmlFormCollection($this->_context, $this->_module, $title);
		$form->setDecimalSeparator($this->_decimalSeparator);
		$form->setDateFormat($this->_dateFormat);

		//$form->addXmlnukeObject(new XmlInputHidden("filter", $this->_filter));
		//$form->addXmlnukeObject(new XmlInputHidden("sort", $this->_sort));
		$form->addXmlnukeObject(new XmlInputHidden("curpage", $this->_curPage));
		$form->addXmlnukeObject(new XmlInputHidden("offset", $this->_qtdRows));
		$form->addXmlnukeObject(new XmlInputHidden("acao", $this->_currentAction . "_confirm"));
		$form->addXmlnukeObject(new XmlInputHidden("valueid", $this->_valueId));
		foreach ($this->_parameter as $key=>$value)
		{
			$form->addXmlnukeObject(new XmlInputHidden($key, $value));
		}

//		SingleRow $sr
		$sr = $this->getCurrentRecord();

		for($i=0, $fieldLength = sizeof($this->_fields); $i<$fieldLength ;$i++)
		{
			$curValue = "";
			if ($this->_currentAction != self::ACTION_NEW)
			{
				$curValue = $sr->getField($this->_fields[$i]->fieldName);
				if ((($this->_fields[$i]->dataType == INPUTTYPE::DATE)||($this->_fields[$i]->dataType == INPUTTYPE::DATETIME)) && ($curValue != ""))
				{
					$curValue = $this->dateFromSource($curValue, ($this->_fields[$i]->dataType == INPUTTYPE::DATETIME));
				}
				elseif ($this->_fields[$i]->dataType == INPUTTYPE::NUMBER)
				{
					$curValue = str_replace(".", $this->_decimalSeparator, $curValue);
				}

				if ($this->_fields[$i]->editFormatter != null)
				{
					$curValue = $this->_fields[$i]->editFormatter->Format($sr, $this->_fields[$i]->fieldName, $curValue);
				}
			}
			else
			{
				$curValue = $this->_fields[$i]->defaultValue;
			}

			$form->addXmlnukeObject($this->renderField($this->_fields[$i], $curValue));
		}
//		XmlInputButtons $buttons
		$buttons = new XmlInputButtons();
		if ($this->_currentAction != self::ACTION_VIEW)
		{
			$buttons->addSubmit($this->_lang->Value("TXT_SUBMIT"), "");
		}
		$buttons->addButton($this->_lang->Value("TXT_BACK"), "", "document.location='" . str_replace("&", "&amp;", $this->redirProcessPage(true)) . "'");
		$form->addXmlnukeObject($buttons);

		return $form;
	}

	/**
	 * Format a date field from Database values
	 * @param $curValue
	 * @return string
	 */
	protected function dateFromSource($curValue, $hour = false)
	{
		try
		{
			return DateUtil::ConvertDate($curValue, DATEFORMAT::YMD, $this->_dateFormat, $hour);
		}
		catch (Exception $ex)
		{
			return "??/??/????";
		}
	}

	/**
	*@desc
	*@param ProcessPageField $field
	*@param string $curValue
	*@return IXmlnukeDocumentObject
	*/
	public function renderField( $field, $curValue)
	{
		if (($field->fieldXmlInput == XmlInputObjectType::TEXTBOX) || ($field->fieldXmlInput == XmlInputObjectType::PASSWORD) || ($field->fieldXmlInput == XmlInputObjectType::TEXTBOX_AUTOCOMPLETE))
		{
//			XmlInputTextBox $itb
			$itb = new XmlInputTextBox($field->fieldCaption, $field->fieldName, $curValue, $field->size);
			$itb->setRequired($field->required);
			$itb->setRange($field->rangeMin, $field->rangeMax);
			$itb->setDescription($field->fieldCaption);
			if ($field->fieldXmlInput == XmlInputObjectType::PASSWORD)
			{
				$itb->setInputTextBoxType(InputTextBoxType::PASSWORD);
			}
			elseif ($field->fieldXmlInput == XmlInputObjectType::TEXTBOX_AUTOCOMPLETE)
			{
				if (!is_array($field->arraySelectList) || ($field->arraySelectList["URL"]=="") || ($field->arraySelectList["PARAMREQ"]==""))
				{
					throw new XMLNukeException(
						"You have to pass a array to arraySelectList field parameter with the following keys: URL, PARAMREQ. Optional: ATTRINFO, ATTRID, JSCALLBACK");
				}
				$itb->setInputTextBoxType(InputTextBoxType::TEXT);
				$itb->setAutosuggest($this->_context, $field->arraySelectList["URL"], $field->arraySelectList["PARAMREQ"], $field->arraySelectList["JSCALLBACK"]);
			}
			else
			{
				$itb->setInputTextBoxType(InputTextBoxType::TEXT);
			}
			$itb->setReadOnly($this->isReadOnly($field));
			$itb->setMaxLength($field->maxLength);
			$itb->setDataType($field->dataType);
			return $itb;
		}
		else if (($field->fieldXmlInput == XmlInputObjectType::RADIOBUTTON) || ($field->fieldXmlInput == XmlInputObjectType::CHECKBOX))
		{
//			XmlInputCheck $ic
			$ic = new XmlInputCheck($field->fieldCaption, $field->fieldName, $field->defaultValue);
			if ($field->fieldXmlInput == XmlInputObjectType::TEXTBOX)
			{
				$ic->setType(InputCheckType::CHECKBOX);
			}
			else
			{
				$ic->setType(InputCheckType::CHECKBOX);
			}
			$ic->setChecked($field->defaultValue == $curValue);
			$ic->setReadOnly($this->isReadOnly($field));
			return $ic;
		}
		else if ($field->fieldXmlInput == XmlInputObjectType::MEMO)
		{
//			XmlInputMemo $im
			$im = new XmlInputMemo($field->fieldCaption, $field->fieldName, $curValue);
			$im->setWrap("SOFT");
			$im->setSize(50, 8);
			$im->setReadOnly($this->isReadOnly($field));
			return $im;
		}
		else if ($field->fieldXmlInput == XmlInputObjectType::HTMLTEXT)
		{
//			XmlInputMemo $im
			$im = new XmlInputMemo($field->fieldCaption, $field->fieldName, $curValue);
			//$im->setWrap("SOFT");
			//$im->setSize(50, 8);
			$im->setVisualEditor(true);
			$im->setReadOnly($this->isReadOnly($field));
			return $im;
		}
		else if ($field->fieldXmlInput == XmlInputObjectType::HIDDEN)
		{
//			XmlInputHidden $ih
			$ih = new XmlInputHidden($field->fieldName, $curValue);
			return $ih;
		}
		else if ($field->fieldXmlInput == XmlInputObjectType::SELECTLIST)
		{
//			XmlEasyList $el
			$el = new XmlEasyList(EasyListType::SELECTLIST, $field->fieldName, $field->fieldCaption, $field->arraySelectList, $curValue);
			$el->setReadOnly($this->isReadOnly($field));
			return $el;
		}
		else if ($field->fieldXmlInput == XmlInputObjectType::DUALLIST)
		{
			$ards = new ArrayDataSet($field->arraySelectList, "value");
			$duallist = new XmlDualList($this->_context, $field->fieldName, $this->_lang->Value("TXT_AVAILABLE", $field->fieldCaption), $this->_lang->Value("TXT_USED", $field->fieldCaption));
			$duallist->createDefaultButtons();
			$duallist->setDataSourceFieldName("key", "value");

			if ($curValue != "")
			{
				$ardt = explode(",", $curValue);
				$ardt  = array_flip($ardt);
				foreach ($ardt as $key=>$value)
				{
					$ardt[$key] = $field->arraySelectList[$key];
				}
			}
			else
			{
				$ardt = array();
			}
			$ards2 = new ArrayDataSet($ardt, "value");

			$duallist->setDataSource($ards->getIterator(), $ards2->getIterator());

			$label = new XmlInputLabelObjects("=>");
			$label->addXmlnukeObject($duallist);

			return $label;
		}
		else if (($field->fieldXmlInput == XmlInputObjectType::DATE) || ($field->fieldXmlInput == XmlInputObjectType::DATETIME))
		{
			$cur = explode(" ", $curValue);
			$idt = new XmlInputDateTime($field->fieldCaption, $field->fieldName, $this->_dateFormat, ($field->fieldXmlInput == XmlInputObjectType::DATETIME), $cur[0], $cur[1]);
			return $idt;
		}
		else if ($field->fieldXmlInput == XmlInputObjectType::FILEUPLOAD)
		{
			$file = new XmlInputFile($field->fieldCaption, $field->fieldName);
			return $file;
		}
		else
		{
//			XmlInputLabelField xlf
			$xlf = new XmlInputLabelField($field->fieldCaption, $curValue);
			return $xlf;
		}
	}

	protected function redirProcessPage($full)
	{
		$url = new XmlnukeManageUrl(URLTYPE::MODULE, $this->_module);
		$url->addParam("acao", self::ACTION_MSG);
		$url->addParam(self::PARAM_MSG, $this->_currentAction);
		//$url->addParam("filter", $this->_filter);
		//$url->addParam("sort", $this->_sort);
		$url->addParam("curpage", $this->_curPage);
		$url->addParam("offset", $this->_qtdRows);
		foreach ($this->_parameter as $key=>$value)
		{
			$url->addParam($key, $value);
		}
		//if ($full)
		//{
			return $url->getUrlFull();
		//}
		//else
		//{
		//	return $url->getUrl();
		//}
	}

	/**
	*@desc Contains specific instructions to generate all XML informations-> This method is processed only one time-> Usually is the last method processed->
	*@param DOMNode $current DOMNode where the XML will be created->
	*@return void
	*/
	public function generateObject($current)
	{
		// Improve Security
		$wrongway = !$this->_edit && (($this->_currentAction == self::ACTION_EDIT) || ($this->_currentAction == self::ACTION_EDIT_CONFIRM));
		$wrongway = $wrongway || (!$this->_new && (($this->_currentAction == self::ACTION_NEW) || ($this->_currentAction == self::ACTION_NEW_CONFIRM)));
		$wrongway = $wrongway || (!$this->_delete && (($this->_currentAction == self::ACTION_DELETE) || ($this->_currentAction == self::ACTION_DELETE_CONFIRM)));
		if ($wrongway)
		{
			$message = $this->_lang->Value("MSG_DONT_HAVEGRANT");
			$p = new XmlParagraphCollection();
			$p->addXmlnukeObject(new XmlnukeText($message, true, true, false));
			$p->generateObject($current);
			return;
		}

		// Checkings!
		if ($this->_context->ContextValue(self::PARAM_CANCEL) != "")
		{
			$this->listAllRecords()->generateObject($current);
		}
		else if (strpos($this->_currentAction, "_confirm") !== false)
		{
			try
			{
				$validateResult = $this->updateRecord();
			}
			catch (Exception $ex)
			{
				$nvc = array($ex->getMessage());

				//XmlParagraphCollection $p
				$p = new XmlParagraphCollection();
				$p->addXmlnukeObject(new XmlEasyList(EasyListType::UNORDEREDLIST, "Error", $this->_lang->Value("ERR_FOUND"), $nvc, ""));
				//XmlAnchorCollection $a
				$a = new XmlAnchorCollection("javascript:history.go(-1)","");
				$a->addXmlnukeObject(new XmlnukeText($this->_lang->Value("TXT_GOBACK")));
				$p->addXmlnukeObject($a);
				$validateResult = $p;
			}
			if (is_null($validateResult))
			{
				$this->_context->redirectUrl($this->redirProcessPage(false));
			}
			else
			{
				$validateResult->generateObject($current);
				if ($this->_currentAction != ProcessPageStateBase::ACTION_NEW_CONFIRM)
				{
					$this->showCurrentRecord()->generateObject($current);
				}
			}
		}
		else if ($this->_currentAction == self::ACTION_MSG)
		{
			$this->showResultMessage()->generateObject($current);
			$this->listAllRecords()->generateObject($current);
		}
		else if (($this->_currentAction == self::ACTION_NEW) || ($this->_currentAction == self::ACTION_VIEW) || ($this->_currentAction == self::ACTION_EDIT) || ($this->_currentAction == self::ACTION_DELETE))
		{

			$this->showCurrentRecord()->generateObject($current);
		}
		else
		{
			$this->listAllRecords()->generateObject($current);
		}

	}


}



class ProcessPageStateBaseFormatterKey implements IEditListFormatter
{
	public function Format($row, $fieldname, $value)
	{
		$fieldnameKey = explode("|", $fieldname);
		$value = "";
		foreach ($fieldnameKey as $fieldnameValue)
		{
			$value .= (($value!="")?"|":"") . $row->getField($fieldnameValue);
		}
		return $value;
	}
}

class ProcessPageStateBaseFormatterDualList implements IEditListFormatter
{
	protected $_arraySource = array();

	public function __construct($arraySource)
	{
		$this->_arraySource = $arraySource;
	}

	public function Format($row, $fieldname, $value)
	{
		if ($value != "")
		{
			$ardt = explode(",", $value);
			$arResult = array();
			foreach ($ardt as $key=>$value)
			{
				$arResult[] = $this->_arraySource[$value];
			}
			return implode(", ", $arResult);
		}
		else
		{
			return "-";
		}
	}
}


class ProcessPageStateBaseSaveFormatterFileUpload implements IEditListFormatter
{
	/**
	 * @var Context
	 */
	protected $_context = "";
	protected $_path = "";
	protected $_saveAs = "";

	protected $_width = 0;
	protected $_height = 0;

	public function __construct($context, $path, $saveAs = "*")
	{
		$this->_context = $context;
		$this->_path = $path;
		$this->_saveAs = $saveAs;
	}

	public function resizeImageTo($width, $height)
	{
		$this->_width = $width;
		$this->_height = $height;
	}

	public function Format($row, $fieldname, $value)
	{
		$files = $this->_context->getUploadFileNames();

		if ($files[$fieldname] != "")
		{
			$fileProcessor = new UploadFilenameProcessor($this->_saveAs);
			$fileProcessor->setFilenameLocation(ForceFilenameLocation::DefinePath, FileUtil::GetTempDir());

			// Save the files in a temporary directory
			$result = $this->_context->processUpload($fileProcessor, false, $fieldname);

			// Get a way to rename the files
			$fileinfo = pathinfo($result[0]);
			if ($this->_saveAs != "*")
			{
				$path_parts = pathinfo($this->_saveAs);
			}
			else
			{
				$path_parts = pathinfo($result[0]);
			}
			$newName = $this->_path . FileUtil::Slash() .  $path_parts['filename'] . "." . $fileinfo["extension"];

			// Put the image in the right place
			if (strpos(".jpg.gif.jpeg.png", ".".$fileinfo["extension"])===false)
			{
				rename( $result[0]  , $newName  );
			}
			else
			{
				if (($this->_width > 0) || ($this->_height > 0))
				{
					$image = new ImageUtil($result[0]);
					$image->resizeAspectRatio($this->_width, $this->_height, 255, 255, 255)->save($newName);
				}
				else
				{
					rename( $result[0]  , $newName  );
				}
			}
			return $newName;
		}
		else
		{
			return $row->getField($fieldname);
		}
	}
}

?>
