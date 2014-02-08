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
namespace Xmlnuke\Admin\Modules;

use Exception;
use Xmlnuke\Core\Admin\BaseAdminModule;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Enum\INPUTTYPE;
use Xmlnuke\Core\Processor\FilenameProcessor;
use Xmlnuke\Core\Processor\XSLCacheFilenameProcessor;
use Xmlnuke\Core\Processor\XSLFilenameProcessor;
use Xmlnuke\Util\FileUtil;
use Xmlnuke\Util\XmlUtil;

class ManageXSL extends BaseAdminModule
{
	public function ManageXSL()
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
		return array("MANAGER", "DESIGNER");
	}

	public function CreatePage()
	{
		parent::CreatePage(); // Doesnt necessary get PX, because PX is protected!

		$deleteMode = false;

		$action = strtolower($this->_action);
		$id = $this->_context->get("id");
		$contents = "";
		$myWords = $this->WordCollection();
		$this->setHelp($myWords->Value("DESCRIPTION"));
		$this->setTitlePage($myWords->Value("TITLE"));

		//XmlNodes
		$block = $this->_px->addBlockCenter($myWords->Value("WORKINGAREA"));
		/*
		XmlNode paragraph;
		XmlNode form;
		XmlNode boxButton;
		*/
		//processor.XSLFilenameProcessor xslFile;

		// --------------------------------------
		// CHECK ACTION
		// --------------------------------------			
		if ( ($action == "edit") || ($action == "new") )
		{
			$contents = $this->_context->get("contents");
			$contents = stripslashes($contents);
			try
			{
				// It is necessary only to load the document to check if it is OK.
				// If OK, use the original raw file
				$xsl = XmlUtil::CreateXmlDocumentFromStr($contents);
				$xslFile = new XSLFilenameProcessor($id);
				FileUtil::QuickFileWrite($xslFile->FullQualifiedNameAndPath(), str_replace("&amp;#", "&#", $contents));

				$paragraph = $this->_px->addParagraph($block);
				FileUtil::DeleteFilesFromPath($this->_cacheFile);
				FileUtil::DeleteFilesFromPath(new XSLCacheFilenameProcessor(""));
				$this->_px->addBold($paragraph, $myWords->Value("SAVED"));
			}
			//catch (XmlException ex)
			catch (Exception $ex)
			{
				$paragraph = $this->_px->addParagraph($block);
				$this->_px->AddErrorMessage($paragraph, $contents, $ex);
			}

		}

		if ($action == "delete")
		{
			$paragraph = $this->_px->addParagraph($block);
			$this->_px->addHref($paragraph, "module:Xmlnuke.Admin.ManageXSL?id=" . $this->_context->get("id") . "&action=confirmdelete", $myWords->Value("CONFIRMDELETE", $this->_context->get("id")) , null);
			$deleteMode = true;
		}

		if ($action == "confirmdelete")
		{
			$paragraph = $this->_px->addParagraph($block);
			FileUtil::DeleteFile(new XSLFilenameProcessor($this->_context->get("id")));
			$this->_px->addBold($paragraph, $myWords->Value("DELETED"));
			$deleteMode = true;
		}

		// --------------------------------------
		// EDIT XSL PAGE
		// --------------------------------------
		// If doesnt have an ID, list all pages or add new!
		if ($id == "")
		{
			//XmlNode list;
			//XmlNode optlist;
			$xslFile = new XSLFilenameProcessor("page");
			//array
			$templates = FileUtil::RetrieveFilesFromFolder($xslFile->PathSuggested(), "." . strtolower($this->_context->Language()->getName() . $xslFile->Extension()));
			$paragraph = $this->_px->addParagraph($block);
			$this->_px->addText($paragraph, $myWords->Value("SELECTPAGE"));
			$list = $this->_px->addUnorderedList($paragraph);
			
			foreach( $templates as $key)
			{
				$optlist = $this->_px->addOptionList($list);
				//$xslKey = substr($key, strlen($xslFile->PathSuggested()));
				$xslKey =basename($key);
				$xslKey = FilenameProcessor::StripLanguageInfo($xslKey);
				$this->_px->addHref($optlist, "module:Xmlnuke.Admin.ManageXSL?id=" . $xslKey, $xslKey, null);
				$this->_px->addText($optlist, " [");
				$this->_px->addHref($optlist, "module:Xmlnuke.Admin.ManageXSL?id=" . $xslKey . "&action=delete", $myWords->Value("TXT_DELETE"), null);
				$this->_px->addText($optlist, "]");
			}
			$action = "new";
		}
		else
		{
			$this->addMenuOption($myWords->Value("NEWPAGE"), "module:Xmlnuke.Admin.ManageXSL", null);
			$this->addMenuOption($myWords->Value("PREVIEWPAGE"), "engine:xmlnuke?xml=home&xsl=" . $id . "&lang=" . $this->_context->Language()->getName(), null);

			//array
			$langAvail = $this->_context->LanguagesAvailable();
			foreach($langAvail as $key => $value)
			{
				if ($key != strtolower($this->_context->Language()->getName()));
				{
					$this->addMenuOption($myWords->ValueArgs("EDITXSLMENU", array($value)), "module:Xmlnuke.Admin.ManageXSL?id=" . $id . "&lang=".$key, null);
				}
			}
			$action = "edit";
		}

		if (!$deleteMode)
		{
			$paragraph = $this->_px->addParagraph($block);
			//XMLNodes
			$table = $this->_px->addTable($paragraph);
			$row = $this->_px->addTableRow($table);
			$col = $this->_px->addTableColumn($row);
			$form = $this->_px->addForm($col, "module:Xmlnuke.Admin.ManageXSL", "","form", true );
			if ($action == "new")
			{
				$this->_px->addTextBox($form, $myWords->Value("XSLBOX"), "id", "", 20, true, INPUTTYPE::TEXT);
			}
			else
			{
				$this->_px->addLabelField($form, $myWords->Value("XSLBOX"), $id);
				$this->_px->addHidden($form, "id", $id);
				$xslFile = new XSLFilenameProcessor($id);
				if (FileUtil::Exists($xslFile->FullQualifiedNameAndPath()))
				{
					//XmlDocument
					$xsl = XmlUtil::CreateXmlDocumentFromFile($xslFile->FullQualifiedNameAndPath());
					$contents = XmlUtil::GetFormattedDocument($xsl);

				}
			}
			$this->_px->addLabelField($form, $myWords->Value("LANGUAGEBOX"), strtolower($this->_context->Language()->getName()));
			$this->_px->addMemo($form, $myWords->Value("LABEL_CONTENT"), "contents", $contents, 80, 30, "soft");
			$this->_px->addHidden($form, "action", $action);
			$boxButton = $this->_px->addBoxButtons($form);
			$this->_px->addSubmit($boxButton, "", $myWords->Value("TXT_SAVE"));
		}

		return $this->_px;
	}

}
?>