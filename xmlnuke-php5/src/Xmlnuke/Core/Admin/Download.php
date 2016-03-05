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
namespace Xmlnuke\Core\Admin;

use ByJG\AnyDataset\Repository\IteratorFilter;
use Xmlnuke\Core\Classes\CrudField;
use Xmlnuke\Core\Classes\CrudFieldCollection;
use Xmlnuke\Core\Classes\XmlAnchorCollection;
use Xmlnuke\Core\Classes\XmlBlockCollection;
use Xmlnuke\Core\Classes\XmlnukeCrudAnydata;
use Xmlnuke\Core\Classes\XmlnukeCrudBase;
use Xmlnuke\Core\Classes\XmlnukeText;
use Xmlnuke\Core\Classes\XmlParagraphCollection;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Enum\BlockPosition;
use Xmlnuke\Core\Enum\INPUTTYPE;
use ByJG\AnyDataset\Enum\Relation;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;

class Download extends NewBaseAdminModule
{
	function Download()
	{
	}

	public function useCache()
	{
		return false;
	}

	public function getAccessLevel() 
	{
		return AccessLevel::OnlyRole;
	}

	public function getRole()
	{
		return array("MANAGER", "EDITOR");
	}
	
	public function CreatePage() 
	{
		parent::CreatePage(); // Doesnt necessary get PX, because PX is protected!

		$myWords = $this->WordCollection(); 
		$forceReset = false;

		// Get parameters to define type of edit
		$catId = $this->_context->get("ci");
		$type = $this->_context->get("t");
		if ($type == "") 
		{
			if ($this->_action == XmlnukeCrudBase::ACTION_VIEW)
			{
				$type = "FILE";
				$catId = $this->_context->get("valueid");
				$catIdAr = explode("|", $catId);
				$catId = $catIdAr[1];
				$forceReset = true;
			}
			else
			{
				$type = "CATEGORY";
			}
		}

		$block = new XmlBlockCollection($myWords->Value("TITLE"), BlockPosition::Center);
		$this->setTitlePage($myWords->Value("TITLE"));
		$this->setHelp($myWords->Value("DESCRIPTION"));
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
		
		// Download File
		$downloadFile = new AnydatasetFilenameProcessor("_download");

		$fields = new CrudFieldCollection();

		// Create Process Page Fields
		$field = CrudField::FactoryMinimal("TYPE", $myWords->Value("FORMTYPE"), 20, false, true);
		$field->key = true;
		$field->editable = false;
		$field->dataType = INPUTTYPE::UPPERASCII;
		$field->defaultValue = $type;
		$fields->addCrudField($field);
	
		$field = CrudField::FactoryMinimal("cat_id", $myWords->Value("FORMCATEGORY"), 20, true, true);
		$field->key = ($type == "CATEGORY");
		$field->editable = ($type == "CATEGORY");
		$field->defaultValue = $catId;
		$fields->addCrudField($field);
		
		if ($type == "FILE")
		{
			$field = CrudField::FactoryMinimal("file_id", $myWords->Value("FORMFILE"), 20, true, true);
			$field->key = ($type == "FILE");
			$fields->addCrudField($field);
		}
		
		$field = CrudField::FactoryMinimal("name", $myWords->Value("LABEL_NAME"), 20, true, true);
		$field->maxLength = 40;
		$fields->addCrudField($field);

		$field = CrudField::FactoryMinimal("description", $myWords->Value("FORMDESCRIPTION"), 40, true, true);
		$field->maxLength = 500;
		$fields->addCrudField($field);

		$langs = $this->_context->LanguagesAvailable();
		foreach($langs as $key=>$desc)
		{
			$field = CrudField::FactoryMinimal("name_" . $key, $myWords->Value("LABEL_NAME") . $desc, 20, false, false);
			$field->maxLength = 40;
			$fields->addCrudField($field);
		
			$field = CrudField::FactoryMinimal("description_" . $key, $myWords->Value("FORMDESCRIPTION") . $desc, 40, false, false);
			$field->maxLength = 500;
			$fields->addCrudField($field);
		}
		
		if ($type == "FILE")
		{
			$field = CrudField::FactoryMinimal("url", $myWords->Value("FORMURL"), 40, true, true);
			$field->maxLength = 40;
			$fields->addCrudField($field);
			
			$field = CrudField::FactoryMinimal("seemore", $myWords->Value("FORMSEEMORE"), 40, false, true);
			$field->maxLength = 40;
			$fields->addCrudField($field);
			
			$field = CrudField::FactoryMinimal("emailto", $myWords->Value("FORMEMAILTO"), 40, false, true);
			$field->maxLength = 50;
			$field->dataType = INPUTTYPE::EMAIL;
			$fields->addCrudField($field);
		}
		
		// Write custom message
		if (($this->_action == XmlnukeCrudBase::ACTION_LIST) || ($this->_action == XmlnukeCrudBase::ACTION_MSG) || $forceReset)
		{
			$p = new XmlParagraphCollection();
			if ($type == "CATEGORY")
			{
				$p->addXmlnukeObject(new XmlnukeText($myWords->Value("NOTE_CATEGORY")));
			}
			else
			{
				$href = new XmlAnchorCollection("module:Xmlnuke.Admin.download");
				$href->addXmlnukeObject(new XmlnukeText($myWords->Value("NOTE_FILE", $catId)));
				$p->addXmlnukeObject($href);
			}
			$block->addXmlnukeObject($p);
		}
		
		// Show Process Page State
		$itf = new IteratorFilter();
		$itf->addRelation("TYPE",  Relation::EQUAL, $type);
		if ($type == "FILE")
		{
			$itf->addRelation("cat_id",  Relation::EQUAL, $catId);
		}
		
		$processor = new XmlnukeCrudAnydata($this->_context, $fields, $myWords->Value("TITLE_" . $type, $catId), "module:Xmlnuke.Admin.download", null, $downloadFile, $itf);
		if ($forceReset)
		{
			$processor->forceCurrentAction(XmlnukeCrudBase::ACTION_LIST);
		}
		if ($type == "FILE")
		{
			$processor->addParameter("t", $type);
			$processor->addParameter("ci", $catId);
		}
		$block->addXmlnukeObject($processor);
		
		return $this->defaultXmlnukeDocument;
	}

}

?>
