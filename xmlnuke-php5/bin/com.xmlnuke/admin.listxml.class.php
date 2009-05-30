<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  PHP5 Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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
class ListXML extends NewBaseAdminModule
{
	public function ListXML()
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

	//Returns: classes.PageXml
	public function CreatePage() 
	{
		parent::CreatePage();
		
		$onlyGroup = ($this->_context->ContextValue("onlygroup") != "");
		$urlXml = "admin:ManageXML";
		$urlGrp = "admin:ManageGroup";

		$this->myWords = $this->WordCollection();
		$this->setTitlePage($this->myWords->Value("TITLE"));
		$this->setHelp($this->myWords->Value("DESCRIPTION"));
		
		if (!$onlyGroup)
		{
			$this->addMenuOption($this->myWords->Value("EDITALLXML"), $urlXml."?id=_all");
			$this->addMenuOption($this->myWords->Value("NEWXML"), $urlXml);
		}
		$this->addMenuOption($this->myWords->Value("NEWGROUP"), $urlGrp);
		
		// Open Index File
		$indexFile = new XMLFilenameProcessor("index", $this->_context);
		//XmlDocument 
		
		$index = $this->_context->getXMLDataBase()->getDocument($indexFile->FullQualifiedName(),null);

		$groupList = XmlUtil::SelectNodes($index->documentElement,"group");
		$table = new XmlTableCollection();
		foreach( $groupList as $node )					
		{				
			
			$groupText = XmlUtil::SelectSingleNode($node,"title")->nodeValue;
			$groupId = XmlUtil::SelectSingleNode($node,"id")->nodeValue;
			
			$row = new XmlTableRowCollection();

			$col = new XmlTableColumnCollection();
			$anchor = new XmlAnchorCollection($urlGrp."?id=".$groupId, "");
			$anchor->addXmlnukeObject(new XmlnukeText($this->myWords->Value("TXT_EDIT"), true, false, false));
			$col->addXmlnukeObject($anchor);
			$row->addXmlnukeObject($col);
			
			$col = new XmlTableColumnCollection();
			$anchor = new XmlAnchorCollection($urlGrp."?id=".$groupId."&action=delete", "");
			$anchor->addXmlnukeObject(new XmlnukeText($this->myWords->Value("TXT_DELETE"), true, false, false));
			$col->addXmlnukeObject($anchor);
			$row->addXmlnukeObject($col);

			$col = new XmlTableColumnCollection();
			$col->addXmlnukeObject(new XmlnukeText($groupText, true, false, false));
			$row->addXmlnukeObject($col);
			$table->addXmlnukeObject($row);

			if (!$onlyGroup)
			{
				$fileList = XmlUtil::SelectNodes($index->documentElement,"group[id='".$groupId."']/page");
				foreach( $fileList as $nodeFile )
				{
					$fileText = XmlUtil::SelectSingleNode($nodeFile,"title")->nodeValue;
					$fileId = XmlUtil::SelectSingleNode($nodeFile,"id")->nodeValue;
					$fileAbstract = XmlUtil::SelectSingleNode($nodeFile,"summary")->nodeValue;

					$row = new XmlTableRowCollection();
					
					$col = new XmlTableColumnCollection();
					$anchor = new XmlAnchorCollection($urlXml."?id=".$fileId, "");
					$anchor->addXmlnukeObject(new XmlnukeText($this->myWords->Value("TXT_EDIT")));
					$col->addXmlnukeObject($anchor);
					$row->addXmlnukeObject($col);
					
					$col = new XmlTableColumnCollection();
					$anchor = new XmlAnchorCollection($urlXml."?id=".$fileId."&action=delete", "");
					$anchor->addXmlnukeObject(new XmlnukeText($this->myWords->Value("TXT_DELETE")));
					$col->addXmlnukeObject($anchor);
					$row->addXmlnukeObject($col);
	
					$col = new XmlTableColumnCollection();
					$col->addXmlnukeObject(new XmlnukeText($fileText));
					$col->addXmlnukeObject(new XmlnukeBreakLine());
					$col->addXmlnukeObject(new XmlnukeText($fileAbstract, false, true, false));
					$row->addXmlnukeObject($col);
					$table->addXmlnukeObject($row);

				}
			}
		
		}
		
		$block = new XmlBlockCollection($this->myWords->Value("WORKINGAREA"), BlockPosition::Center );

		$paragraph = new XmlParagraphCollection();
		$paragraph->addXmlnukeObject($table);
		$block->addXmlnukeObject($paragraph);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
		
		return $this->defaultXmlnukeDocument->generatePage();
	}

}
?>