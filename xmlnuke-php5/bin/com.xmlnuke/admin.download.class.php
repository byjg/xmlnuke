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
		return AccessLevel::CurrentSiteAndRole;
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
		$catId = $this->_context->ContextValue("ci");
		$type = $this->_context->ContextValue("t");
		if ($type == "") 
		{
			if ($this->_action == ProcessPageStateBase::ACTION_VIEW)
			{
				$type = "FILE";
				$catId = $this->_context->ContextValue("valueid");
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
		$downloadFile = new AnydatasetFilenameProcessor("_download", $this->_context);

		$fields = new ProcessPageFields();

		// Create Process Page Fields
		$field = ProcessPageFields::FactoryMinimal("TYPE", $myWords->Value("FORMTYPE"), 20, false, true);
		$field->key = true;
		$field->editable = false;
		$field->dataType = INPUTTYPE::UPPERASCII;
		$field->defaultValue = $type;
		$fields->addProcessPageField($field);
	
		$field = ProcessPageFields::FactoryMinimal("cat_id", $myWords->Value("FORMCATEGORY"), 20, true, true);
		$field->key = ($type == "CATEGORY");
		$field->editable = ($type == "CATEGORY");
		$field->defaultValue = $catId;
		$fields->addProcessPageField($field);
		
		if ($type == "FILE")
		{
			$field = ProcessPageFields::FactoryMinimal("file_id", $myWords->Value("FORMFILE"), 20, true, true);
			$field->key = ($type == "FILE");
			$fields->addProcessPageField($field);
		}
		
		$field = ProcessPageFields::FactoryMinimal("name", $myWords->Value("LABEL_NAME"), 20, true, true);
		$field->maxLength = 40;
		$fields->addProcessPageField($field);

		$field = ProcessPageFields::FactoryMinimal("description", $myWords->Value("FORMDESCRIPTION"), 40, true, true);
		$field->maxLength = 500;
		$fields->addProcessPageField($field);

		$langs = $this->_context->LanguagesAvailable();
		foreach($langs as $key=>$desc)
		{
			$field = ProcessPageFields::FactoryMinimal("name_" . $key, $myWords->Value("LABEL_NAME") . $desc, 20, false, false);
			$field->maxLength = 40;
			$fields->addProcessPageField($field);
		
			$field = ProcessPageFields::FactoryMinimal("description_" . $key, $myWords->Value("FORMDESCRIPTION") . $desc, 40, false, false);
			$field->maxLength = 500;
			$fields->addProcessPageField($field);
		}
		
		if ($type == "FILE")
		{
			$field = ProcessPageFields::FactoryMinimal("url", $myWords->Value("FORMURL"), 40, true, true);
			$field->maxLength = 40;
			$fields->addProcessPageField($field);
			
			$field = ProcessPageFields::FactoryMinimal("seemore", $myWords->Value("FORMSEEMORE"), 40, false, true);
			$field->maxLength = 40;
			$fields->addProcessPageField($field);
			
			$field = ProcessPageFields::FactoryMinimal("emailto", $myWords->Value("FORMEMAILTO"), 40, false, true);
			$field->maxLength = 50;
			$field->dataType = INPUTTYPE::EMAIL;
			$fields->addProcessPageField($field);
		}
		
		// Write custom message
		if (($this->_action == ProcessPageStateBase::ACTION_LIST) || ($this->_action == ProcessPageStateBase::ACTION_MSG) || $forceReset)
		{
			$p = new XmlParagraphCollection();
			if ($type == "CATEGORY")
			{
				$p->addXmlnukeObject(new XmlnukeText($myWords->Value("NOTE_CATEGORY")));
			}
			else
			{
				$href = new XmlAnchorCollection("module:admin.download");
				$href->addXmlnukeObject(new XmlnukeText($myWords->Value("NOTE_FILE", $catId)));
				$p->addXmlnukeObject($href);
			}
			$block->addXmlnukeObject($p);
		}
		
		// Show Process Page State
		$itf = new IteratorFilter();
		$itf->addRelation("TYPE", Relation::Equal, $type);
		if ($type == "FILE")
		{
			$itf->addRelation("cat_id", Relation::Equal, $catId);
		}
		
		$processor = new ProcessPageStateAnydata($this->_context, $fields, $myWords->Value("TITLE_" . $type, $catId), "module:admin.download", null, $downloadFile, $itf);
		if ($forceReset)
		{
			$processor->forceCurrentAction(ProcessPageStateBase::ACTION_LIST);
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
