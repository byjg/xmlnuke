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
* ProcessPageStateDB is class to make easy View, Edit, Delete and Update single tables from relational databases like MySQL, PostGres, Oracle, SQLServer and others->
* To use this class is necessary define the fields are used->
* <code>
* ProcessPageFields[] fieldPage = new ProcessPageFields[3];
* fieldPage[0]->fieldName = "field1";
* fieldPage[0]->key = true;             // Only one key field->
* fieldPage[0]->dataType = INPUTTYPE->NUMBER;
* fieldPage[0]->fieldCaption = "Key Field";
* fieldPage[0]->fieldXmlInput = XmlInputObjectType->TEXTBOX;
* fieldPage[0]->visibleInList = true;   // This field must be visible and in the FIRST position
* fieldPage[0]->editable = true;        // If the Key field is AutoIncrement set this property
*                                      // to false->
* fieldPage[0]->required = true;
*
* fieldPage[1]->fieldName = "field2";
* fieldPage[1]->key = false;
* fieldPage[1]->dataType = INPUTTYPE->TEXT;
* fieldPage[1]->fieldCaption = "Caption of Field 2";
* fieldPage[1]->fieldXmlInput = XmlInputObjectType->TEXTBOX;
* fieldPage[1]->visibleInList = true;
* fieldPage[1]->editable = true;
* fieldPage[1]->required = true;
*
* fieldPage[2]->fieldName = "field3";
* fieldPage[2]->key = false;
* fieldPage[2]->dataType = INPUTTYPE->DATE;
* fieldPage[2]->fieldCaption = "Caption of Field 3";
* fieldPage[2]->fieldXmlInput = XmlInputObjectType->TEXTBOX;
* fieldPage[2]->visibleInList = true;
* fieldPage[2]->editable = true;
* fieldPage[2]->required = true;
* </code>
* After defined all the fields the user must create the class, like this:
* <code>
* // Create a Block->
* XmlBlockCollection block = new XmlBlockCollection("ProcessPageStateDB Example", BlockPosition->Center);
*
* // Create the class passing all relevant paramenters->
* ProcessPageStateDB processPage = new ProcessPageStateDB(
*                   $this->_context,
*                   fieldPage,
*                   "Editing Table 'mytable'",
*                   "module:sample",
*                   null,
*                   "mytable",
*                   "myconnection");
* block1->addXmlnukeObject(processPage);
*
* // Create a XmlnukeDocument and generate XML in the CreatePage method->
* XmlnukeDocument xmlnukeDoc = new XmlnukeDocument("Titulo da Página", "Abstract Dessa Página");
* xmlnukeDoc->addXmlnukeObject(block1);
* return xmlnukeDoc->generatePage();
* </code>
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class ProcessPageStateAnydata extends ProcessPageStateBase
{
	/**
	*@var AnydatasetBaseFilenameProcessor
	*/
	protected $_anydata;
	/**
	 * @var IteratorFilter
	 */
	protected $_itf;
	/**
	*@var string
	*/
	protected $_conn;

	/**
	*@desc Constructor
	*@param Context $context XMLNuke context object
	*@param array fields Fields will be processed
	*@param string header Simple $header
	*@param string module Module will be process this request. Usually is the same $module instantiate the ProcessPageStateDB
	*@param array $buttons Custom buttons in View/Select mode
	*@param AnydatasetBaseFilenameProcessor $anydata Database connections.
	*@param IteratorFilter iteratorFilter
	*/
	public function ProcessPageStateAnydata($context, $fields, $header, $module, $buttons, $anydata, $iteratorFilter = null)
	{
		parent::ProcessPageStateBase($context, $fields, $header, $module, $buttons);
		$this->_anydata = $anydata;
		$this->_itf = $iteratorFilter;
	}

	/**
	*@desc Returns an IIterator with all records in table
	*@return IIterator
	*/
	public function getAllRecords()
	{
//		AnyDataSet $data
		$data = new AnyDataSet($this->_anydata);
		return $data->getIterator($this->_itf);
	}

	/**
	 * Enter description here...
	 *
	 * @return IteratorFilter
	 */
	protected function getIteratorFilterKey()
	{
		if ($this->_itf == null)
		{
			$itf = new IteratorFilter();
		}
		else
		{
			$itf = $this->_itf;
		}

		$arValueId = explode("|", $this->_valueId);
		$i = 0;
		foreach ($this->_keyIndex as $keyIndex)
		{
			$itf->addRelation($this->_fields[$keyIndex]->fieldName, Relation::Equal, $arValueId[$i++]);
		}
		return $itf;
	}

	/**
	*@desc Return a SingleRow with the selection of the user
	*@param Context $context
	*@return SingleRow
	*/
	public function getCurrentRecord()
	{
		if ($this->_currentAction != self::ACTION_NEW)
		{
			// IteratorFilter $itf
			$itf = $this->getIteratorFilterKey();

			// AnyDataSet $data
			$data = new AnyDataSet($this->_anydata);
			$it = $data->getIterator($itf);

			if ($it->hasNext())
			{
				return $it->moveNext();
			}
		}
		return null;
	}

	/**
	*@desc Execute the proper action to insert, update and delete $data from database.
	*@param Context $context
	*@return IXmlnukeDocumentObject $it contains all necessary XML to inform the user the operation result
	*/
	public function updateRecord()
	{
		$message = "";
//		IXmlnukeDocumentObject $mdo
		$mdo = $this->validateUpdate();
		if ($mdo != null)
		{
			return $mdo;
		}

		$data = new AnyDataSet($this->_anydata);
		if ($this->_currentAction == self::ACTION_NEW_CONFIRM)
		{
			$data->appendRow();
			for($i=0, $fieldLength = sizeof($this->_fields); $i<$fieldLength; $i++)
			{
				$value = $this->_context->ContextValue($this->_fields[$i]->fieldName);
				if ($this->_fields[$i]->saveDatabaseFormatter != null)
				{
					$value = $this->_fields[$i]->saveDatabaseFormatter->Format($srCurInfo, $this->_fields[$i]->fieldName, $value);
				}
				$data->addField($this->_fields[$i]->fieldName, $value);
			}
		}
		else
		{
			$itf = $this->getIteratorFilterKey();
			$it = $data->getIterator($itf);

			if ($it->hasNext())
			{
				$sr = $it->moveNext();

				if ($this->_currentAction == self::ACTION_EDIT_CONFIRM)
				{
					for($i=0, $fieldsLength = sizeof($this->_fields); $i<$fieldsLength; $i++)
					{
						$value = $this->_context->ContextValue($this->_fields[$i]->fieldName);

						if ($this->_fields[$i]->fieldXmlInput == XmlInputObjectType::FILEUPLOAD)
						{
							$files = $this->_context->getUploadFileNames();
							if ($files[$this->_fields[$i]->fieldName] == "")
								continue; // Do nothing if none files are uploaded.
						}

						if ($this->_fields[$i]->saveDatabaseFormatter != null)
						{
							$value = $this->_fields[$i]->saveDatabaseFormatter->Format($srCurInfo, $this->_fields[$i]->fieldName, $value);
						}
						$sr->setField($this->_fields[$i]->fieldName, $value);
					}
				}
				else if ($this->_currentAction == self::ACTION_DELETE_CONFIRM)
				{
					$data->removeRow($sr);  // Remove the Current Row;
				}
			}
		}

		$data->Save($this->_anydata);


		//XmlFormCollection $retorno = new XmlFormCollection($this->_context, $this->_module, $message);
		//$retorno->addXmlnukeObject(new XmlInputHidden("filter", $this->_filter));
		//$retorno->addXmlnukeObject(new XmlInputHidden("sort", $this->_sort));
		//$retorno->addXmlnukeObject(new XmlInputHidden("curpage", $this->_curPage->ToString()));
		//$retorno->addXmlnukeObject(new XmlInputHidden("offset", $this->_qtdRows->ToString()));
		//XmlInputButtons btnRetorno = new XmlInputButtons();
		//btnRetorno->addSubmit("Retornar", "");
		//$retorno->addXmlnukeObject(btnRetorno);

//		XmlParagraphCollection $retorno

		return null;
	}

}

?>