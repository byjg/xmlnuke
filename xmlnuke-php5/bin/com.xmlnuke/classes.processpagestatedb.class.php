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
*ProcessPageStateDB is class to make easy View, Edit, Delete and Update single tables from relational databases like MySQL, PostGres, Oracle, SQLServer and others->
*To use $this class is necessary define the $fields are used->
*
*<code>
* ProcessPageFields[] fieldPage = new ProcessPageFields[3];
* fieldPage[0]->fieldName = "field1";
* fieldPage[0]->key = true;             ** Only one key field->
* fieldPage[0]->dataType = INPUTTYPE::NUMBER;
* fieldPage[0]->fieldCaption = "Key Field";
* fieldPage[0]->fieldXmlInput = XmlInputObjectType->TEXTBOX;
* fieldPage[0]->visibleInList = true;   ** This field must be visible and in the FIRST position
* fieldPage[0]->editable = true;        ** If the Key field is AutoIncrement set $this property to false->
* fieldPage[0]->required = true;
*
* fieldPage[1]->fieldName = "field2";
* fieldPage[1]->key = false;
* fieldPage[1]->dataType = INPUTTYPE::TEXT;
* fieldPage[1]->fieldCaption = "Caption of Field 2";
* fieldPage[1]->fieldXmlInput = XmlInputObjectType->TEXTBOX;
* fieldPage[1]->visibleInList = true;
* fieldPage[1]->editable = true;
* fieldPage[1]->required = true;
*
* fieldPage[2]->fieldName = "field3";
* fieldPage[2]->key = false;
* fieldPage[2]->dataType = INPUTTYPE::DATE;
* fieldPage[2]->fieldCaption = "Caption of Field 3";
* fieldPage[2]->fieldXmlInput = XmlInputObjectType->TEXTBOX;
* fieldPage[2]->visibleInList = true;
* fieldPage[2]->editable = true;
* fieldPage[2]->required = true;
* </code>
* After defined $all the $fields the user must create the class, like $this:
* <code>
* ** Create a Block->
* XmlBlockCollection block = new XmlBlockCollection("ProcessPageStateDB Example", BlockPosition->Center);
*
* ** Create the class passing $all relevant paramenters->
* ProcessPageStateDB processPage = new ProcessPageStateDB(
*                   $this->_context,
*                   fieldPage,
*                   "Editing Table 'mytable'",
*                   "$module:sample",
*                   null,
*                   "mytable",
*                   "myconnection");
* block1->addXmlnukeObject(processPage);
*
* ** Create a XmlnukeDocument and generate XML in the CreatePage method->
* XmlnukeDocument xmlnukeDoc = new XmlnukeDocument("Titulo da Pagina", "Abstract Dessa Pagina");
* xmlnukeDoc->addXmlnukeObject(block1);
* return xmlnukeDoc->generatePage();
*</code>
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class ProcessPageStateDB extends ProcessPageStateBase
{
	/**
	*@var string
	*/
	protected $_table;
	/**
	*@var string
	*/
	protected $_conn;
	/**
	*@var string
	*/
	protected $_fieldDeliLeft = "";
	/**
	*@var string
	*/
	protected $_fieldDeliRight = "";
	/**
	 * @var DBDataSet
	 */
	protected $_dbData = null;

	/**
	*@desc Constructor
	*@param Context $context XMLNuke context object
	*@param array $fields Fields will be processed
	*@param string $header Simple string header
	*@param string $module Module will be process this request-> Usually is the same module instantiate the ProcessPageStateDB
	*@param array $buttons Custom buttons in View/Select mode
	*@param string $table Table in database-> This table must contains all fields defined in "fields" parameter
	*@param string $connection Database connections
	*/
	public function ProcessPageStateDB($context, $fields, $header, $module, $buttons, $table, $connection)
	{
		parent::ProcessPageStateBase($context, $fields, $header, $module, $buttons);
		$this->_conn = $connection;
		$this->_table = $table;
		$this->_dbData = new DBDataSet($this->_conn, $this->_context);
	}

	/**
	*@desc Returns an IIterator with all records in table
	*@param
	*@return IIterator
	*/
	public function getAllRecords()
	{
		return $this->GetIterator(true);
	}

	/**
	*@desc Return a SingleRow with the selection of the user
	*@param
	*@return SingleRow
	*/
	public function getCurrentRecord()
	{
		if ($this->_currentAction != self::ACTION_NEW)
		{
			$it = $this->GetIterator(false);

			if ($it->hasNext())
			{
				return $it->moveNext();
			}
		}
		return null;
	}

	protected function getWhereClause(&$param)
	{
		$arValueId = explode("|", $this->_valueId);
		$where = "";
		$i = 0;
		foreach ($this->_keyIndex as $keyIndex)
		{
			$where .= (($where!="")? " and " : "") . $this->_fields[$keyIndex]->fieldName . " = [[valueid" . $keyIndex. "]] ";
			$param["valueid" . $keyIndex] = $arValueId[$i++];
		}
		return $where;
	}

	/**
	*@desc Execute the proper action to insert, update and delete data from database
	*@param
	*@return IXmlnukeDocumentObject - it contains all necessary XML to inform the user the operation result
	*/
	public function updateRecord()
	{
		//$message = "";
		//IXmlnukeDocumentObject $mdo
		$mdo = $this->validateUpdate();
		if ($mdo != null)
		{
			return $mdo;
		}

		if ($this->_currentAction == self::ACTION_NEW_CONFIRM)
		{
			$this->ExecuteSQL(SQLType::SQL_INSERT);
		}
		else if ($this->_currentAction == self::ACTION_EDIT_CONFIRM)
		{
			$this->ExecuteSQL(SQLType::SQL_UPDATE);
		}
		else if ($this->_currentAction == self::ACTION_DELETE_CONFIRM)
		{
			$this->ExecuteSQL(SQLType::SQL_DELETE);
		}

//		$retorno = new XmlFormCollection($this->_context, $this->_module, $message);
//		$retorno->addXmlnukeObject(new XmlInputHidden("filter", $this->_filter));
//		$retorno->addXmlnukeObject(new XmlInputHidden("sort", $this->_sort));
//		$retorno->addXmlnukeObject(new XmlInputHidden("curpage", $this->_curPage->ToString()));
//		$retorno->addXmlnukeObject(new XmlInputHidden("offset", $this->_qtdRows->ToString()));
//		$btnRetorno = new XmlInputButtons();
//		$btnRetorno->addSubmit("Retornar", "");
//		$retorno->addXmlnukeObject($btnRetorno);

//		XmlParagraphCollection retorno

		return null;
	}

	protected function preProcessValue($fieldName, $dataType, $currentValue)
	{
		$value = null;
		if ($currentValue == "")
		{
			$value = null;
		}
		elseif ($dataType == INPUTTYPE::NUMBER)
		{
			$value = str_replace($this->_decimalSeparator, ".", $currentValue);
		}
		elseif (($dataType == INPUTTYPE::DATE) || ($dataType == INPUTTYPE::DATETIME))
		{
			$value = $this->_dbData->getDbFunctions()->toDate($currentValue, $this->_dateFormat, ($dataType == INPUTTYPE::DATETIME));
		}
		else
		{
			$value = $currentValue;
		}

		return $value;
	}

	public function getFieldDeliLeft()
	{
		return $this->_fieldDeliLeft;
	}
	public function setFieldDeliLeft($value)
	{
		$this->_fieldDeliLeft = $value;
	}

	public function getFieldDeliRight()
	{
		return $this->_fieldDeliRight;
	}
	public function setFieldDeliRight($value)
	{
		$this->_fieldDeliRight = $value;
	}


	/**
	 * @param SQLType $sqlType
	 */
	protected function ExecuteSQL($sqlType)
	{
		$fieldList = array();
		if ($sqlType != SQLType::SQL_DELETE)
		{
			// Get a SingleRow with all field values
			$anyCurInfo = new AnyDataSet();
			$anyCurInfo->appendRow();
			foreach ($this->_fields as $field)
			{
				$anyCurInfo->addField($field->fieldName, $this->_context->ContextValue($field->fieldName));
			}
			$itCurInfo = $anyCurInfo->getIterator();
			$srCurInfo = $itCurInfo->moveNext();

			// Format and Adjust all field values
			foreach ($this->_fields as $field)
			{
				if ($field->editable)
				{
					$value = $this->preProcessValue($field->fieldName, $field->dataType, $this->_context->ContextValue($field->fieldName));
					if ($field->fieldXmlInput == XmlInputObjectType::FILEUPLOAD)
					{
						$files = $this->_context->getUploadFileNames();
						if ($files[$field->fieldName] == "")
							continue; // Do nothing if none files are uploaded.
					}

					if ($field->saveDatabaseFormatter != null)
					{
						$value = $field->saveDatabaseFormatter->Format($srCurInfo, $field->fieldName, $value);
					}

					$fieldList[$field->fieldName] = array(SQLFieldType::Text, $value);
				}
			}
		}

		$param = array();
		if ($sqlType != SQLType::SQL_INSERT)
		{
			$filter = $this->getWhereClause($param);
		}
		else
		{
			$filter = "";
		}

		$helper = new SQLHelper($this->_dbData);
		$helper->setFieldDelimeters($this->getFieldDeliLeft(), $this->getFieldDeliRight());
		$sql = $helper->generateSQL($this->_table, $fieldList, $param, $sqlType, $filter, $this->_decimalSeparator);
		$this->DebugInfo($sql, $param);
		$this->_dbData->execSQL($sql, $param);
	}

	/**
	 * @param bool $getAll
	 * @return IIterator
	 */
	protected function GetIterator($getAll)
	{
		$fields = "";
		foreach ($this->_fields as $field)
		{
			if ($field->visibleInList || $field->key || !$getAll)
			{
				if ($fields != "") $fields .= ",";
				$fields .= $this->getFieldDeliLeft() . $field->fieldName . $this->getFieldDeliRight();
			}
		}

		$sql =
			"select " . $fields . " " .
			"from " . $this->_table . " ";

		$param = array();
		if (!$getAll)
		{
			$sql .= "where " . $this->getWhereClause($param);
		}
		if ($this->_filter != "")
		{
			$sql .= ($getAll ? " where " : " and ") . " " . $this->getFilter();
		}
		if (($this->_sort != "") && ($getAll))
		{
			$sql .= " order by " . $this->getSort();
		}

		$this->DebugInfo($sql, $param);
		return $this->_dbData->getIterator($sql, $param);
	}

	protected function DebugInfo($sql, $param)
	{
		if ($this->_context->getDebugInModule())
		{
			Debug::PrintValue("<hr>");
			Debug::PrintValue("Class name: " . get_class($this));
			Debug::PrintValue("SQL: " . $sql);
			if ($param != null)
			{
				$s = "";
				foreach($param as $key=>$value)
				{
					if ($s!="")
					{
						$s .= ", ";
					}
					$s .= "[$key]=$value";
				}
				Debug::PrintValue("Params: $s");
			}
		}
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
			return $this->_dbData->getDbFunctions()->fromDate($curValue, $this->_dateFormat, $hour);
		}
		catch (Exception $ex)
		{
			return "??/??/????";
		}
	}

	/**
	 *
	 * @param EditListField $editListField
	 * @param ProcessPageField $field
	 * @return EditListField
	 */
	protected function editListFieldCustomize($editListField, $field)
	{
		if ( ((($field->dataType == INPUTTYPE::DATE) || ($field->dataType == INPUTTYPE::DATETIME))) && ($field->editListFormatter == null))
		{
			$editListField->fieldType = EditListFieldType::FORMATTER;
			$editListField->formatter = new ProcessPageStateDBFormatterDate($this->_dbData, $this->_dateFormat, ($field->dataType == INPUTTYPE::DATETIME));
		}
		return $editListField;
	}
}




class ProcessPageStateDBFormatterDate implements IEditListFormatter
{
	/**
	 * @var DBDataSet
	 */
	protected $_dbData = null;
	protected $_hour = null;
	protected $_dateFormat = null;

	public function __construct($dbData, $dateFormat, $hour)
	{
		$this->_dbData = $dbData;
		$this->_dateFormat = $dateFormat;
		$this->_hour = $hour;
	}

	public function Format($row, $fieldname, $value)
	{
		if ($value != "")
		{
			return $this->_dbData->getDbFunctions()->fromDate($value, $this->_dateFormat, $this->_hour);
		}
		else
		{
			return "";
		}
	}
}


?>