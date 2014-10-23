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

namespace Xmlnuke\Core\Classes;

use DOMNode;
use Exception;
use Xmlnuke\Core\AnyDataset\ArrayDataSet;
use Xmlnuke\Core\AnyDataset\IIterator;
use Xmlnuke\Core\AnyDataset\SingleRow;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\CustomButtons;
use Xmlnuke\Core\Enum\DATEFORMAT;
use Xmlnuke\Core\Enum\EasyListType;
use Xmlnuke\Core\Enum\EditListFieldType;
use Xmlnuke\Core\Enum\InputTextBoxType;
use Xmlnuke\Core\Enum\INPUTTYPE;
use Xmlnuke\Core\Enum\MultipleSelectType;
use Xmlnuke\Core\Enum\UIAlert;
use Xmlnuke\Core\Enum\URLTYPE;
use Xmlnuke\Core\Enum\XmlInputObjectType;
use Xmlnuke\Core\Exception\XMLNukeException;
use Xmlnuke\Core\Formatter\CrudDualListFormatter;
use Xmlnuke\Core\Formatter\CrudPKFormatter;
use Xmlnuke\Core\Locale\LanguageCollection;
use Xmlnuke\Core\Locale\LanguageFactory;
use Xmlnuke\Util\DateUtil;

/**
 * Basic CRUD.
 * @package xmlnuke
 */
abstract class XmlnukeCrudBase extends XmlnukeDocumentObject implements IXmlnukeCrud
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
	*@param string module Module will be process this request-> Usually is the same $module instantiate the XmlnukeCrudDB
	*@param array $buttons Custom $buttons in View/Select mode
	*@return
	*/
	public function __construct($context, $fields, $header, $module, $buttons)
	{
		$this->_context = $context;
		$this->_fields = $fields->getCrudFieldCollection();
		$this->_buttons = $buttons;
		$this->_header = $header;
		$this->_module = $module;

		$this->_new = true;
		$this->_view = true;
		$this->_delete = true;
		$this->_edit = true;

		$this->_currentAction = $this->_context->get("acao");

		for($i=0, $fieldsLength = sizeof($this->_fields); $i<$fieldsLength; $i++)
		{
			if ($this->_fields[$i]->key)
			{
				$this->_keyIndex[] = $i;
			}
		}

		$this->_parameter = array();
		//$this->_filter = $this->_context->get("filter"); //encoded
		//$this->_sort = $this->_context->get("sort"); // encoded
		$this->_valueId = $this->_context->get("valueid");
		$this->_curPage = $this->_context->get("curpage");
		$this->_qtdRows = $this->_context->get("offset");

		$this->_decimalSeparator = $this->_context->Language()->getDecimalPoint();
		$this->_dateFormat = $this->_context->Language()->getDateFormat();
		$this->_lang = LanguageFactory::GetLanguageCollection(__CLASS__);
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
//			CrudFieldCollection $field
			$field = $this->_fields[$i];
			$curValue = $this->_context->get($this->_fields[$i]->fieldName);

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
		$field->formatter = new CrudPKFormatter();
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
					$field->formatter = new CrudDualListFormatter($this->_fields[$i]->arraySelectList);
				}
				else
				{
					$field->fieldType = EditListFieldType::TEXT;
				}
				$field->newColumn = $this->_fields[$i]->newColumn;
				if (!is_null($this->_fields[$i]->viewFormatter))
				{
					$field->formatter = $this->_fields[$i]->viewFormatter;
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
	 * @param CrudField $field
	 * @return EditListField
	 */
	protected function editListFieldCustomize($editListField, $field)
	{
		return $editListField;
	}


	/**
	*@desc
	*@param CrudFieldCollection $field
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
		$msg = $this->_context->get(self::PARAM_MSG);

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
		else
		{
			$title = "";
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

				if ($this->_fields[$i]->beforeUpdateFormatter != null)
				{
					$curValue = $this->_fields[$i]->beforeUpdateFormatter->Format($sr, $this->_fields[$i]->fieldName, $curValue);
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
	*@param CrudField $field
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
			if (count($cur) == 0)
				$cur = array('', '');
			else if (count($cur) == 1)
				$cur[] = '';
			
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
	*@param DOMNode $current \DOMNode where the XML will be created->
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
		if ($this->_context->get(self::PARAM_CANCEL) != "")
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
				if ($this->_currentAction != XmlnukeCrudBase::ACTION_NEW_CONFIRM)
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
?>
