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
class ManageXML extends BaseAdminModule
{
	public function ManageXML()
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
		parent::CreatePage(); // Doesnt necessary get PX, because PX is protected!
		
		$deleteMode = false;
		
		//Strings
		$action = strtolower($this->_action);
		$id = $this->_context->ContextValue("id");
		$group = $this->_context->ContextValue("group");
		$contents = "";
		$titleIndex = $this->_context->ContextValue("titleIndex");		
		$summaryIndex = $this->_context->ContextValue("summaryIndex");
		$groupKeyword = $this->_context->ContextValue("groupKeyword");

		$myWords = $this->WordCollection();
		$this->setHelp($myWords->Value("DESCRIPTION"));
		$this->setTitlePage($myWords->Value("TITLE"));
		
		//XmlNode 
		$block = $this->_px->addBlockCenter($myWords->Value("WORKINGAREA"));

		$this->addMenuOption($myWords->Value("TXT_BACK"), "admin:ListXML");
		/*
		XmlNode paragraph;
		XmlNode form;
		XmlNode boxButton;
		XmlNode editNode; // (For Index)
		processor.XMLFilenameProcessor xmlFile;
		*/

		// Open Index File
		$indexFile = new XMLFilenameProcessor("index", $this->_context);
		//XmlDocument 
		
		$index = $this->_context->getXMLDataBase()->getDocument($indexFile->FullQualifiedName(),null);

		// --------------------------------------
		// CHECK ACTION
		// --------------------------------------
		
		if ( ($action == "edit") || ($action == "new") )
		{
			$contents = $this->_context->ContextValue("contents");				
			$contents = stripslashes($contents);
			$this->_context->setSession("texto", $contents); 
			
			/*echo "<PRE>";
			echo htmlentities($contents);
			echo "</PRE>";*/
			
			try
			{
				$title = "";
				$summary = "";
				//XmlNode $node;

				// Get edited XML and update info about INDEX.
				//XmlDocument
				$xml = XmlUtil::CreateXmlDocumentFromStr($contents);

				//$node = XmlUtil::SelectSingleNode($xml->documentElement, "/page/meta/title");
				$node = XmlUtil::SelectSingleNode($xml->documentElement, "meta/title");
				if ($node != null)
				{
					$title = $node->nodeValue;
				}
				//$node = XmlUtil::SelectSingleNode($xml->documentElement,"/page/meta/abstract");
				$node = XmlUtil::SelectSingleNode($xml->documentElement,"meta/abstract");
				if ($node != null)
				{
					$summary = $node->nodeValue;
				}
				//$node = XmlUtil::SelectSingleNode($xml->documentElement,"/page/meta/modified");
				$node = XmlUtil::SelectSingleNode($xml->documentElement,"meta/modified");
				if ($node != null)
				{
					$node->nodeValue = date("D M j Y G:i:s");
				}
				//$node = XmlUtil::SelectSingleNode($xml->documentElement,"/page/meta/groupkeyword");
				$node = XmlUtil::SelectSingleNode($xml->documentElement,"meta/groupkeyword");
				
				if ($node != null)
				{
					$node->nodeValue = $groupKeyword;
				}

				if ($id != "_all")
				{
					if ($action == "edit")
					{
						//$editNode = XmlUtil::SelectSingleNode($index->documentElement, "/xmlindex/group[id='" . $group . "']/page[id='" . $id . "']");
						$editNode = XmlUtil::SelectSingleNode($index->documentElement, "group[id='" . $group . "']/page[id='" . $id . "']");
						if ($titleIndex == "")
						{
							$titleIndex = $title;
						}
						XmlUtil::SelectSingleNode($editNode,"title")->nodeValue = $titleIndex;

						if ($summaryIndex == "")
						{
							$summaryIndex = $summary;
						}
						XmlUtil::SelectSingleNode($editNode,"summary")->nodeValue = $summaryIndex;
					}
					else
					{
						
						//$editNode = XmlUtil::SelectSingleNode($index->documentElement,"/xmlindex/group[id='" . $group . "']");		
						$editNode = XmlUtil::SelectSingleNode($index->documentElement,"group[id='" . $group . "']");									
						$newNode = XmlUtil::CreateChild($editNode, "page", "");
						XmlUtil::CreateChild($newNode, "id", $id);
						XmlUtil::CreateChild($newNode, "title", $title);
						XmlUtil::CreateChild($newNode, "summary", $summary);
						$titleIndex = $title;
						$summaryIndex = $summary;
					}
				}

				$xmlFile = new XMLFilenameProcessor($id, $this->_context);
					
				$this->_context->getXMLDataBase()->saveDocumentXML($xmlFile->FullQualifiedName(), $xml);
				$this->_context->getXMLDataBase()->saveDocumentXML($indexFile->FullQualifiedName(), $index);
				$this->_context->getXMLDataBase()->saveIndex();
				$paragraph = $this->_px->addParagraph($block);
				FileUtil::DeleteFilesFromPath($this->_cacheFile);
				FileUtil::DeleteFilesFromPath(new XSLCacheFilenameProcessor("", $this->_context));
				$this->_px->addBold($paragraph, $myWords->Value("SAVED"));
			}
			//catch (XmlException ex)
			catch (Exception $ex)
			{
				
				$paragraph = $this->_px->addParagraph($block);
				$this->_px->AddErrorMessage($paragraph, $contents, $ex);
			}
		}

		// Get the group from the Index and Update Edit Fields
		//$editNode = XmlUtil::SelectSingleNode( $index->documentElement,"/xmlindex/group[page[id='" . $id . "']]/id");
		$editNode = XmlUtil::SelectSingleNode( $index->documentElement,"group[page[id='" . $id . "']]/id");
		if ($editNode != null)
		{
			$group = $editNode->nodeValue;
			//$editNode = XmlUtil::SelectSingleNode($index->documentElement,"/xmlindex/group[id='" . $group . "']/page[id='" . $id . "']");
			$editNode = XmlUtil::SelectSingleNode($index->documentElement,"group[id='" . $group . "']/page[id='" . $id . "']");
			$titleIndex = XmlUtil::SelectSingleNode($editNode,"title")->nodeValue;
			$summaryIndex = XmlUtil::SelectSingleNode($editNode,"summary")->nodeValue;
		}
		
		if ($action == "delete")
		{
			$paragraph = $this->_px->addParagraph($block);
			$this->_px->addHref($paragraph, "admin:ManageXML?id=" . $this->_context->ContextValue("id") . "&action=confirmdelete", $myWords->Value("CONFIRMDELETE", $this->_context->ContextValue("id")), null);
			$deleteMode = true;
		}

		if ($action == "confirmdelete")
		{
			$paragraph = $this->_px->addParagraph($block);
			//$editNode = XmlUtil::SelectSingleNode($index->documentElement,"/xmlindex/group[id='" . $group . "']");		
			$editNode = XmlUtil::SelectSingleNode($index->documentElement,"group[id='" . $group . "']");
			$delNode = XmlUtil::SelectSingleNode($editNode,"page[id='" . $id . "']");
			if ($delNode != null)
			{
				$editNode->removeChild($delNode);
			}
			
			$this->_context->getXMLDataBase()->saveDocumentXML($indexFile->FullQualifiedName(), $index);
			//util.FileUtil.DeleteFile(new processor.XMLFilenameProcessor(_context.ContextValue("id"), this._context));
			$this->_context->getXMLDataBase()->saveIndex();
			$this->_px->addBold($paragraph, $myWords->Value("DELETED"));
			$deleteMode = true;
		}

		// --------------------------------------
		// EDIT XML PAGE
		// --------------------------------------
		// If doesnt have an ID, list all pages or add new!
		if ($id == "")
		{
			$action = "new";
		}
		else
		{
			$this->addMenuOption($myWords->Value("PREVIEWMENU"), "engine:xmlnuke?site=[param:site]&xml=" . $id . "&xsl=[param:xsl]&lang=[param:lang]", "preview");
			$this->addMenuOption($myWords->Value("NEWXMLMENU"), "admin:ManageXML", null);
			$langAvail = $this->_context->LanguagesAvailable();
			$processorFile = new XMLFilenameProcessor($id, $this->_context );
			foreach(array_keys($langAvail) as $key) 				
			{
				if ($key != strtolower($this->_context->Language()->getName()));
				{
					$repositorio = 	new XmlNukeDB($this->_context->XmlHashedDir(), $this->_context->XmlPath(), $key);
					$fileToCheck = $processorFile->FullName($id, "", $key) . $processorFile->Extension();
					if ($repositorio->existsDocument($fileToCheck))
					{
						$this->addMenuOption($myWords->ValueArgs("EDITXMLMENU" ,array($langAvail[$key])), "admin:ManageXML?id=" . $id . "&lang=".$key, null);
					}
					else
					{
						$this->addMenuOption($myWords->ValueArgs("CREATEXMLMENU", array($langAvail[$key])), "admin:ManageXML?id=" . $id . "&lang=".$key, null);
					}
				}
			}
			$action = "edit";
		}


		// Show form to Edit/Insert
		if (!$deleteMode)
		{
			$paragraph = $this->_px->addParagraph($block);
			//XmlNodeS 
			$table = $this->_px->addTable($paragraph);
			$row = $this->_px->addTableRow($table);
			$col = $this->_px->addTableColumn($row);
			$form = $this->_px->addForm($col, "admin:ManageXML", "","form", true );

			$xmlExist = true;
			if ($id != "")
			{
				$xmlTestExist = new XMLFilenameProcessor($id, $this->_context );
				$xmlExist = $this->_context->getXMLDataBase()->existsDocument($xmlTestExist->FullQualifiedName());
			}

			$canUseNew = (($action != "new") && !$xmlExist);
			
			//Trecho acrescentado para manter o conteudo do textarea mesmo no caso de erro.
			$contents = $this->_context->getSession("texto");
			//echo $contents;
			if (($action == "new") || $canUseNew)
			{
				$action = "new"; // This is necessary, because user can Create a predefined ID...
				if (!$canUseNew || ($id == ""))
				{
					$this->_px->addTextBox($form, $myWords->Value("XMLBOX"), "id", "", 20, true, INPUTTYPE::TEXT);
				}
				else
				{
					$this->_px->addLabelField($form, $myWords->Value("XMLBOX"), $id);
					$this->_px->addHidden($form, "id", $id);
				}
				$this->_px->addLabelField($form, $myWords->Value("LANGUAGEBOX"), strtolower($this->_context->Language()->getName()));
				//if($contents!="")
				//{
					$contents = 
						"<page>\n" .
						"  <meta>\n" .
						"    <title>Put your title here</title>\n" .
						"    <abstract>Put page abstract informations here</abstract>\n" .
						"    <created>" . date("D M j Y G:i:s") . "</created>\n" .
						"    <modified/>\n" .
						"    <keyword>xmlnuke</keyword>\n" . 
						"    <groupkeyword>all</groupkeyword>\n" .
						"  </meta>\n" .
						"  <blockcenter>\n" . 
						"    <title>Block Title</title>\n" .
						"    <body>\n" .
						"      <p>This is the first paragraph</p>\n" .
						"    </body>\n" .
						"  </blockcenter>\n" .
						"</page>\n";
				//}
				session_unregister("texto");
			}
			else
			{
				$this->_px->addLabelField($form, $myWords->Value("LANGUAGEBOX"), strtolower($this->_context->Language()->getName()));
				$this->_px->addHidden($form, "id", $id);
				$xmlFile = new XMLFilenameProcessor($id, $this->_context);
				//XmlDocument 
				$xml = $this->_context->getXMLDataBase()->getDocument($xmlFile->FullQualifiedName(), null);
				//if($contents!="")					
					$contents = str_replace("&amp;", "&", XmlUtil::GetFormattedDocument($xml));
				$editNode = XmlUtil::SelectSingleNode($xml->documentElement,"meta/groupkeyword");
				if ($editNode != null)
				{
					$groupKeyword = $editNode->nodeValue;
				}
				session_unregister("texto");
			}

			if ($id != "_all")
			{
				$this->_px->addCaption($form, $myWords->Value("SITEMAPINFO"));
				$this->_px->addTextBox($form, $myWords->Value("INDEXBOX"), "titleIndex", $titleIndex, 60, true, INPUTTYPE::TEXT);
				$selectNode = $this->_px->addSelect($form, $myWords->Value("LISTEDBOX"), "group");
				$this->_px->addTextBox($form, $myWords->Value("INDEXSUMMARYBOX"), "summaryIndex", $summaryIndex, 60, true, INPUTTYPE::TEXT);

				$this->_px->addCaption($form, $myWords->Value("PAGEINFO"));
				//XmlNode 
				$selectPageNode = $this->_px->addSelect($form,$myWords->Value("SHOWMENUBOX"), "groupKeyword");
				$this->_px->addOption($selectPageNode, $myWords->Value("NOTLISTEDOPTION"), "-", null);					
				
				//XmlNodeList 
				//$groupList = XmlUtil::SelectNodes($index->documentElement,"/xmlindex/group");
				$groupList = XmlUtil::SelectNodes($index->documentElement,"/group");
				
				foreach( $groupList as $node )					
				{				
					
					$value = XmlUtil::SelectSingleNode($node,"title")->nodeValue;
										
					$this->_px->addOption($selectNode, XmlUtil::SelectSingleNode($node,"title")->nodeValue . " (" . XmlUtil::SelectSingleNode($node,"/id")->nodeValue . ")", XmlUtil::SelectSingleNode($node,"/id")->nodeValue, XmlUtil::SelectSingleNode($node,"/id")->nodeValue == $group);
					
					$this->_px->addOption($selectPageNode, XmlUtil::SelectSingleNode($node,"title")->nodeValue . " (" . XmlUtil::SelectSingleNode($node,"/keyword")->nodeValue . ")", XmlUtil::SelectSingleNode($node,"/keyword")->nodeValue, XmlUtil::SelectSingleNode($node,"/keyword")->nodeValue == $groupKeyword);
				
				}
			
			}
			
			$this->_px->addHidden($form, "action", $action);
			$this->_px->addCaption($form, $myWords->Value("XMLEDITINFO"));
			$this->_px->addMemo($form, $myWords->Value("XMLCONTENTBOX"), "contents", $contents, 80, 30, "soft");
			$boxButton = $this->_px->addBoxButtons($form);
			$this->_px->addSubmit($boxButton, "", $myWords->Value("TXT_SAVE"));
		}
		
		return $this->_px;
	}

}
?>