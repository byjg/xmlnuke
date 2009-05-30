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

/// <summary>
/// Summary description for com.
/// </summary>
class ManageGroup extends BaseAdminModule
{
	public function ManageGroup()
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
		$this->setHelp($myWords->Value("DESCRIPTION"));
		//this.addMenuOption("OK", "admin:ManageGroup?action=aqui");
		$this->setTitlePage($myWords->Value("TITLE"));

		//Strings
		$action = strtolower($this->_action);
		$id = $this->_context->ContextValue("id");
		$title = $this->_context->ContextValue("title");
		$keyword = $this->_context->ContextValue("keyword");

		//XmlNodes
		$block = $this->_px->addBlockCenter($myWords->Value("WORKINGAREA"));

		$this->addMenuOption($myWords->Value("TXT_BACK"), "admin:ListXML?onlygroup=true");

		// Open Index File
		$indexFile = new XMLFilenameProcessor("index", $this->_context);
		//DOMDocument
		$index = $this->_context->getXMLDataBase()->getDocument($indexFile->FullQualifiedName(), null);
		// Delete a Group Node
		if ($action == "delete")
		{
			$paragraph = $this->_px->addParagraph($block);
			$this->_px->addHref($paragraph, "admin:ManageGroup?id=" . $id . "&action=confirmdelete", $myWords->ValueArgs("CONFIRMDELETE", array($id)) , null);
			return $this->_px;
		}

		if ($action == "confirmdelete")
		{
		        //XmlNode

			//$editNode = XmlUtil::SelectSingleNode($index->documentElement,"xmlindex");
			$editNode =$index->getElementsByTagName("xmlindex")->item(0);
			$delNode = XmlUtil::SelectSingleNode($editNode,"/group[id='" . $id . "']");
			$editNode->removeChild($delNode);
			$paragraph = $this->_px->addParagraph($block);
			$this->_context->getXMLDataBase()->saveDocumentXML($indexFile->FullQualifiedName(), $index);
			$this->_px->addBold($paragraph, $myWords->Value("DELETED"));
			FileUtil::DeleteFilesFromPath($this->_cacheFile);
			return $this->_px;
		}

		// Edit or Insert a new Group!
		if ($title != "")
		{
			if ($this->_context->ContextValue("new") != "")
			{
				
				//$editNode = XmlUtil::SelectSingleNode($index->documentElement,"xmlindex");
				$editNode = $index->getElementsByTagName("xmlindex")->item(0);

				$newNode = XmlUtil::CreateChild($editNode, "group", "");					
				XmlUtil::CreateChild($newNode, "id", $id);
				XmlUtil::CreateChild($newNode, "title", $title);
				XmlUtil::CreateChild($newNode, "keyword", $keyword);					
				
			}
			else
			{
				//$editNode = XmlUtil::SelectSingleNode($index->documentElement,"/xmlindex/group[id='" . $id . "']");
				$editNode = XmlUtil::SelectSingleNode($index->documentElement,"group[id='" . $id . "']");
				XmlUtil::SelectSingleNode($editNode,"title")->nodeValue = $title;
				XmlUtil::SelectSingleNode($editNode,"keyword")->nodeValue = $keyword;
			}
			$paragraph = $this->_px->addParagraph($block);		
			$this->_context->getXMLDataBase()->saveDocumentXML($indexFile->FullQualifiedName(), $index);
			FileUtil::DeleteFilesFromPath($this->_cacheFile);
			
			$this->_px->addBold($paragraph, $myWords->Value("SAVED", $id));
		}

		// Get new Index from disk
		$idnew = "true";
		$index = $this->_context->getXMLDataBase()->getDocument($indexFile->FullQualifiedName(), null);
		//$edit = XmlUtil::SelectSingleNode($index->documentElement,"/xmlindex/group[id='" . $id . "']");
		$edit = XmlUtil::SelectSingleNode($index->documentElement,"group[id='" . $id . "']");
		if ($edit != null)
		{
			$idnew = "";
			$title = XmlUtil::SelectSingleNode($edit,"title")->nodeValue;
			$keyword = XmlUtil::SelectSingleNode($edit,"keyword")->nodeValue;
		}

		// Show form to Edit/Insert
		//XmlNodes
		$paragraph = $this->_px->addParagraph($block);
		$table = $this->_px->addTable($paragraph);
		$row = $this->_px->addTableRow($table);
		$col = $this->_px->addTableColumn($row);
		$form = $this->_px->addForm($col, "admin:ManageGroup?xsl=page", "" ,"form", true);
		$this->_px->addHidden($form, "new", $idnew);
		if ($idnew == "")
		{
			$this->_px->addCaption($form, $myWords->Value("EDITINGID", $id));
			$this->_px->addHidden($form, "id", $id);
		}
		else
		{
			$this->_px->addTextBox($form, $myWords->Value("GROUPID", $id), "id", $id, 20, true, INPUTTYPE::TEXT);
		}
		$this->_px->addTextBox($form, $myWords->Value("TITLEBOX"), "title", $title, 20, true, INPUTTYPE::TEXT);
		$this->_px->addTextBox($form, $myWords->Value("KEYWORDBOX"), "keyword", $keyword, 20, true, INPUTTYPE::TEXT);
		$boxButton = $this->_px->addBoxButtons($form);
		$this->_px->addSubmit($boxButton, "", $myWords->Value("TXT_SAVE"));

		return $this->_px;
	}
}
?>