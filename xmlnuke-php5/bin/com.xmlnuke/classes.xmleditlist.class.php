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
*@package com.xmlnuke
*@subpackage xmlnukeobject
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

interface IEditListFormatter
{
	/**
	 * Class to format a EditList Field
	 *
	 * @param SingleRow $row
	 * @param string $fieldname
	 * @param string $value
	 */
	function Format($row, $fieldname, $value);
}


/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class MultipleSelectType
{
	const NONE = 0;
	const ONLYONE = 1;
	const MULTIPLE = 2;
}
/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class SelectType
{
	const RADIO = 1;
	const CHECKBOX = 2;
}
/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class EditListFieldType
{
	const TEXT = 1;
	const IMAGE = 2;
	const LOOKUP = 3;
	const FORMATTER = 4;
	const CUSTOM = 99;
}

class EditListFieldSummary
{
	const NONE = 0;
	const SUM = 1;
	const AVG = 2;
	const COUNT = 3;
}

/**
*Edit list Field elements, to simulate the struct of the .NET C#
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class EditListField
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

	public function EditListField($new = true)
	{
		$this->newColumn = $new;
		$this->arrayLookup = array();
		$this->summary = EditListFieldSummary::NONE;
		$this->formatter = null;
	}
}

/**
*Edit list class
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlEditList extends XmlnukeDocumentObject
{
	/**
	*@var string
	*/
	protected $_title;
	/**
	*@var string
	*/
	protected $_module;
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
	*@var bool
	*/
	protected $_readonly;
	/**
	*@var SelectType
	*/
	protected $_selecttype;
	/**
	*@var IIterator
	*/
	protected $_it;
	/**
	*@var EditListField
	*/
	protected $_fields;
	/**
	*@var array
	*/
	protected $_customButton;
	/**
	*@var string
	*/
	protected $_name;
	/**
	*Used only in programming mode...
	*@var Array
	*/
	protected $_extraParam;
	/**
	*@var int
	*/
	protected $_curPage;
	/**
	*Used only in programming mode...
	*@var int
	*/
	protected $_qtdRows;
	/**
	*@var bool
	*/
	protected $_enablePages;
	/**
	*@var Context
	*/
	protected $_context;
	/**
	*@var string
	*/
	protected $_customsubmit = "";
	
	protected $_objXmlHeader = null;

	/**
	*@desc XmlEditList constructor
	*@param Context $context
	*@param string $title
	*@param string $module
	*@param bool $newButton
	*@param bool $view
	*@param bool $edit
	*@param bool $delete
	*/
	public function  __construct($context, $title, $module, $newButton = true, $view = true, $edit = true, $delete = true)
	{
		$this->_context = $context;
		$this->_module = $module;
		$this->_title = $title;
		$this->_new = $newButton;
		$this->_view = $view;
		$this->_edit = $edit;
		$this->_delete = $delete;
		$this->_readonly = false;
		$this->_selecttype = SelectType::RADIO;
		$this->_customButton = array();
		 
		$this->_name = "EL" . $this->_context->getRandomNumber(100000); 
		$this->_extraParam = array(); 

		$this->_curPage = $this->_context->ContextValue("curpage");

		$this->_qtdRows = $this->_context->ContextValue("offset"); 
		
		$this->_enablePages = ($this->_qtdRows > 0) && ($this->_curPage > 0); 
	}
	
	/**
	*@desc set Page Sizes
	*@param int $qtdRows
	*@param int $curPage
	*@return void
	*/
	public function setPageSize($qtdRows, $curPage) 
	{ 
		if ($curPage != 0) 
		{
			$this->_curPage = $curPage;
		}
		$this->_qtdRows = $qtdRows; 
	}

	/**
	*@desc Enable Page
	*@param bool $enable
	*@return void
	*/
	public function setEnablePage($enable) 
	{ 
		$this->_enablePages = $enable; 
		if ($this->_enablePages) 
		{ 
			if ($this->_qtdRows == 0)
			{ 
				$this->_qtdRows = 10; 
			} 
			if ($this->_curPage == 0)
			{
				$this->_curPage = 1; 
			} 
		} 
	} 
	
	/**
	*@desc set Custom Button by index
	*@param CustomButtons $cb
	*@return void
	*/
	public function setCustomButton($cb) 
	{ 
		$this->_customButton[] = $cb;
	} 

	/**
	*@desc Add a parameter
	*@param string $key
	*@param string $value
	*@return void
	*/
	public function addParameter( $key,  $value) 
	{ 
		$this->_extraParam[$key] = $value; 
	} 

	
	/**
	*@desc get Custom Button
	*@param int $index
	*@return CustomButtons
	*/
	public function getCustomButton($index)
	{
		return $this->_customButton[$index];
	}
	
	/**
	*@desc set Data Source
	*@param IIterator $it
	*@return void
	*/
	public function setDataSource($it)
	{
		$this->_it = $it;
	}
	
	/**
	 * set Fields of the EditList
	 *
	 * @param Array EditListField $fields
	 */
	public function addEditListField($fields)
	{
		$this->_fields[] = $fields;
	}

	/**
	*@desc set Select Record Type
	*@param SelectType $st
	*@return void
	*/
	public function setSelectRecordType($st) 
	{ 
		$this->_selecttype = $st; 
	} 
	
	/**
	*@desc set editList Read Only
	*/
	public function setReadonly($value = true)
	{
		$this->_readonly = $value;
	}

	// $objXmlHeader deve implementar a interface IXmlDocumentObject
	public function setXmlHeader($objXmlHeader)
	{
		$this->_objXmlHeader = $objXmlHeader;
	}

	public function setTitle($title)
	{
		$this->_title = $title;
	}

	/**
	 * Render Column
	 *
	 * @param DOMNode $column
	 * @param SingleRow $row
	 * @param EditListField $field
	 */
	public function renderColumn($column, $row, $field) 
	{ 
		switch ($field->fieldType) 
		{ 
			case EditListFieldType::TEXT:
			{ 
				XmlUtil::AddTextNode($column, $row->getField($field->fieldData));
				break; 
			} 
			case EditListFieldType::IMAGE:
			{ 
//				XmlnukeImage $xmi
				$xmi = new XmlnukeImage( $row->getField($field->fieldData)); 
				$xmi->generateObject($column); 
				break; 
			}
			case EditListFieldType::LOOKUP:
			{ 
				$value = $row->getField($field->fieldData);
				if ($value == "")
				{
					$value = "---";
				}
				else 
				{
					$value = $field->arrayLookup[$value];
				}
				XmlUtil::AddTextNode($column, $value);
				break;
			}
			case EditListFieldType::FORMATTER:
			{
				$obj = $field->formatter;
				if (is_null($obj) || !($obj instanceof IEditListFormatter))
				{
					throw new Exception("The EditListFieldType::FORMATTER requires a valid IEditListFormatter class");
				}
				else 
				{
					XmlUtil::AddTextNode($column, $obj->Format($row, $field->fieldData, $row->getField($field->fieldData)));
				}
				break;
			}
			default:
			{
				XmlUtil::AddTextNode($column, $row->getField($field->fieldData));
				break;
			}
		}
	}

	/**
	*@desc Generate $page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		$nodeWorking = XmlUtil::CreateChild($current, "editlist", "");
		XmlUtil::AddAttribute($nodeWorking, "module", $this->_module);
		XmlUtil::AddAttribute($nodeWorking, "title", $this->_title);
		XmlUtil::AddAttribute($nodeWorking, "name", $this->_name);

		if($this->_new)
			XmlUtil::AddAttribute($nodeWorking, "new", "true");
		if($this->_edit)
			XmlUtil::AddAttribute($nodeWorking, "edit", "true");
		if($this->_view)
			XmlUtil::AddAttribute($nodeWorking, "view", "true");
		if($this->_delete)
			XmlUtil::AddAttribute($nodeWorking, "delete", "true");
		if($this->_readonly)
			XmlUtil::AddAttribute($nodeWorking, "readonly", "true");
		if($this->_selecttype == SelectType::CHECKBOX)
			XmlUtil::AddAttribute($nodeWorking, "selecttype", "check");

		if($this->_extraParam != null)
		{
			foreach ($this->_extraParam as $key => $value)
			{
				$param = XmlUtil::CreateChild($nodeWorking, "param", "");
				XmlUtil::AddAttribute($param, "name", $key);
				XmlUtil::AddAttribute($param, "value", $value);
			}
		}

		if($this->_customButton != null)
		{
			for ($i=0, $customButtonsLength = sizeof($this->_customButton); $i < $customButtonsLength ; $i++)
			{
	//			CustomButtons $cb
				$cb = $this->_customButton[$i];
				if ($cb->enabled)
				{
					$nodeButton = XmlUtil::CreateChild($nodeWorking, "button");
					XmlUtil::AddAttribute($nodeButton, "custom", $i+1);
					XmlUtil::AddAttribute($nodeButton, "acao", $cb->action);
					XmlUtil::AddAttribute($nodeButton, "alt", $cb->alternateText);
					XmlUtil::AddAttribute($nodeButton, "url", $cb->url);
					XmlUtil::AddAttribute($nodeButton, "img", $cb->icon);
					XmlUtil::AddAttribute($nodeButton, "multiple", $cb->multiple);
					XmlUtil::AddAttribute($nodeButton, "message", $cb->message);
				}
			}
		}

		$qtd = 0;
		$qtdPagina = 0;
		$page = 0;
		$started = !$this->_enablePages;
		$first = true;
		$firstRow = true;
		
		$summaryFields = array();

		// Generate XML With Data
		while ($this->_it->hasNext())
		{
			//com.xmlnuke.anydataset.SingleRow
			$registro = $this->_it->moveNext();

			// Insert fields if none is passed.
			if (sizeof($this->_fields) == 0)
			{
				foreach($registro->getFieldNames() as $key=>$fieldname)
				{
					$fieldtmp = new EditListField(true);
					$fieldtmp->editlistName = $fieldname;
					$fieldtmp->fieldData = $fieldname;
					$fieldtmp->fieldType = EditListFieldType::TEXT;
					$this->addEditListField($fieldtmp);
					if (sizeof($this->_fields) == 1) // The First field isnt visible because is the "key"
					{
						$this->addEditListField($fieldtmp);
					}
			    }
			}

			// Fill values
			if ($this->_enablePages)
			{
				$page = intval($qtd / $this->_qtdRows) + 1;
				$started = ($page == $this->_curPage);
			}

			if ($started)
			{   //DOMNode
				$row = XmlUtil::CreateChild($nodeWorking, "row", "");
				$currentNode = null;
				if (is_null($this->_fields))
				{
					throw new XmlNukeObjectException(850, "No such EditListField Object");
				}
				foreach($this->_fields as $chave=>$field)
				{
					if(($field->newColumn) || ($currentNode == null))
					{
						$currentNode = XmlUtil::CreateChild($row, "field", "");
						if ($firstRow)
						{
							if (!$first)
							{
								XmlUtil::AddAttribute($currentNode, "name", $field->editlistName);
							}
							else
							{
								$first = false;
							}
						}
					}
					else
					{
						XmlUtil::CreateChild($currentNode ,"br","");
					}
					$this->renderColumn($currentNode, $registro, $field);
					
					// Check if this fields requires summary
					if ($field->summary != EditListFieldSummary::NONE)
					{
						$summaryFields[$field->fieldData] += $this->_context->Language()->getDoubleVal($registro->getField($field->fieldData)); 
					}
				}
				$firstRow = false;
				$qtdPagina++;
			}
			$qtd += 1;
		}
		
		// Generate SUMMARY Information
		if (sizeof($summaryFields) > 0)
		{
			$anydata = new AnyDataSet();
			$anydata->appendRow();
			foreach($this->_fields as $chave=>$field)
			{
				switch ($field->summary) 
				{
					case EditListFieldSummary::SUM:
						$value = $summaryFields[$field->fieldData];
						break;
					
					case EditListFieldSummary::AVG:
						$value = $summaryFields[$field->fieldData] / $qtdPagina;
						break;
					
					case EditListFieldSummary::COUNT:
						$value = $qtdPagina;
						break;
					
					default:
						$value = "";
					break;
				}
				
				$anydata->addField($field->fieldData, $value);
			}
			$ittemp = $anydata->getIterator();
			$registro = $ittemp->moveNext();
			
			$row = XmlUtil::CreateChild($nodeWorking, "row", "");
			XmlUtil::AddAttribute($row, "total", "true");
			foreach($this->_fields as $chave=>$field)
			{	 
				$currentNode = null;
				if(($field->newColumn) || ($currentNode == null))
				{
					$currentNode = XmlUtil::CreateChild($row, "field", "");
				}
				else
				{
					XmlUtil::CreateChild($currentNode ,"br","");
				}
				$this->renderColumn($currentNode, $registro, $field);
			}
		}		
		
		// Create other properties
		XmlUtil::AddAttribute($nodeWorking, "cols", sizeof($this->_fields));

		if($this->_enablePages)
		{
			if ($this->_curPage > 1)
			{
				XmlUtil::AddAttribute($nodeWorking, "pageback", strval($this->_curPage - 1));
			}

			if (!$started) // In this case, the list reachs the last element, so you dont need move forward!
			{
				XmlUtil::AddAttribute($nodeWorking, "pagefwd", strval($this->_curPage + 1));
			}
			XmlUtil::AddAttribute($nodeWorking, "curpage", strval($this->_curPage));
			XmlUtil::AddAttribute($nodeWorking, "offset", strval($this->_qtdRows));
			XmlUtil::AddAttribute($nodeWorking, "pages", strval($page));
		}

		if ($this->_customsubmit != "")
		{
			XmlUtil::AddAttribute($nodeWorking, "customsubmit", $this->_customsubmit);
		}

		if (!is_null($this->_objXmlHeader))
		{
			$nodeHeader = XmlUtil::CreateChild($nodeWorking ,"xmlheader","");
			$this->_objXmlHeader->generateObject($nodeHeader);
		}

		return $nodeWorking;
	}
	
	/**
	*@desc Force EditList uses a Custom Submit Function (HTML -> JS)
	*@param string $fnclient
	*@return void
	*/
	public function setCustomSubmit($fnclient)
	{
		$this->_customsubmit = $fnclient;
	}
	
}

?>
