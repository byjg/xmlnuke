<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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

class EditLanguage extends BaseAdminModule
{
	/// <summary>
	/// Default constructor
	/// </summary>
	public function EditLanguage()
	{}

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
		$this->setTitlePage($myWords->Value("TITLE"));
		$this->addMenuOption($myWords->Value("LANGUAGEMENU"),"admin:EditLanguage");
		//$this->addMenuOption("Add Category","admin:Download?action=addcat");

		$langfile = $this->_context->ContextValue("langfile");
		$contents = $this->_context->ContextValue("contents");
		$contents = stripslashes($contents);
		
		$blockcenter = $this->_px->addBlockCenter( $myWords->Value("WORKINGAREA") );

		$paragraph = $this->_px->addParagraph($blockcenter);
		$this->_px->addBold($paragraph, $myWords->Value("MESSAGEWORKING"));
		$langDir = new AnydatasetLangFilenameProcessor("", $this->_context);
		$filelist = FileUtil::RetrieveFilesFromFolder($langDir->PrivatePath(), $langDir->Extension());
		$this->generateList($this->_px, $paragraph, $filelist, $langDir);

		$this->_px->addBold($paragraph, $myWords->Value("MESSAGEWORKINGSHARED"));
		$filelist = FileUtil::RetrieveFilesFromFolder($langDir->SharedPath(), $langDir->Extension());
		$this->generateList($this->_px, $paragraph, $filelist, $langDir);


		$blockcenter = $this->_px->addBlockCenter( $myWords->Value("EDITINGAREA") );

		// --------------------------------------
		// CHECK ACTION
		// --------------------------------------
		if ( ($this->_action == "save") || ($this->_action == "create") )
		{
			try
			{
				$xmlLang = XmlUtil::CreateXmlDocumentFromStr($contents);
				$lang = new AnydatasetLangFilenameProcessor($langfile, $this->_context);
				XmlUtil::SaveXmlDocument( $xmlLang, $lang->FullQualifiedNameAndPath() );
				$paragraph = $this->_px->addParagraph($blockcenter);
				$this->_px->addBold($paragraph, $myWords->Value("SAVED"));
			}
			catch (Exception $ex)
			{
				$paragraph = $this->_px->addParagraph($blockcenter);
				$this->_px->AddErrorMessage($paragraph, $contents, $ex);
			}
		}
		

		$form = $this->_px->addForm($blockcenter, "admin:EditLanguage", $myWords->ValueArgs("FORMTITLE", array($langfile)));
		if ($this->_action == "")
		{
			$this->_px->addTextBox($form, $myWords->Value("LANGUAGEFILE"), "langfile", "", 20);
			$this->_px->addHidden($form, "action", "create");
			$contents = "<?xml version=\"1.0\"?>\n" .
				"<anydataset>\n" .
					"	<row>\n" .
  				"		<field name=\"LANGUAGE\">en-us</field>\n" .
					"	</row>\n" .
					"	<row>\n" .
  				"		<field name=\"LANGUAGE\">pt-br</field>\n" .
					"	</row>\n" .
				"</anydataset>\n";
		}
		else
		{
			$this->_px->addHidden($form, "action", "save");
			$this->_px->addLabelField($form, $myWords->Value("LANGUAGEFILE"), $langfile);
			$this->_px->addHidden($form, "langfile", $langfile);
			$lang = new AnydatasetLangFilenameProcessor($langfile, $this->_context);
			
			if (FileUtil::Exists($lang->FullQualifiedNameAndPath()))
			{
				$langXml = XmlUtil::CreateXmlDocumentFromFile($lang->FullQualifiedNameAndPath());
				$contents = str_replace("&amp;","&", XmlUtil::GetFormattedDocument($langXml));
			}
		}
		$this->_px->addMemo($form, $myWords->Value("LABEL_CONTENT"), "contents", $contents, 80, 30, "soft");
		$boxButton = $this->_px->addBoxButtons($form);
		$this->_px->addSubmit($boxButton, "", $myWords->Value("TXT_SAVE"));

		return $this->_px;
	}

	private function generateList($px, $paragraph, $filelist, $proc)
	{
		$list = $this->_px->addUnorderedList($paragraph);
		asort($filelist);
		foreach($filelist as $key => $file)
		{
			//echo("<br>$key -> $file<br>");
			$name = FileUtil::ExtractFileName($file);
			$name = $proc->removeLanguage($name);
			$optlist = $this->_px->addOptionList($list);
			$this->_px->addHref($optlist, "admin:EditLanguage?action=edit&langfile=" . $name, "- " . $name);
		}

	}

}

?>