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
class ManageSitesAction extends ModuleAction 
{
	const OFFLINE = 'action.OFFLINE';
	const Save = 'save'; /* Editlist Action */
}

class ManageSites extends NewBaseAdminModule
{
	/**
	* BlockPosition is Center
	*@var XmlBlockCollection
	*/
	protected $_block;
	public function ManageSites()
	{
	}

	public function useCache()
	{
		return false;
	}
	public function  getAccessLevel() 
        { 
              return AccessLevel::CurrentSiteAndRole; 
        } 

        public function getRole() 
        { 
               return "MANAGER"; 
        }
	public function CreatePage() 
	{
		parent::CreatePage();
		$myWords = $this->WordCollection();
		$this->setTitlePage($myWords->Value("TITLE"));
		$this->setHelp($myWords->Value("DESCRIPTION"));
		
		$this->_block = new XmlBlockCollection($myWords->Value("WORKINGAREA"), BlockPosition::Center);
		$paragraph = new XmlParagraphCollection();
		$this->_block->addXmlnukeObject($paragraph);
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_block);
		
		switch ($this->_action) {
			case ManageSitesAction::OFFLINE :
				$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("NOTIMPLEMENTED")));
				break;
			case ManageSitesAction::Save :
				$this->ActionCreate($paragraph);
				break;
			case ManageSitesAction::Delete :
				$this->ActionDelete($paragraph);
				break;
			case ManageSitesAction::Create :
				$this->ActionCreateForm($paragraph);
				break;
			case ManageSitesAction::View :
				$url = new XmlnukeManageUrl(URLTYPE::ENGINE);
				$url->addParam('site', $this->_context->ContextValue('valueid'));
				$this->_context->redirectUrl($url->getUrlFull($this->_context));
				break;
			case ManageSitesAction::Edit :
				$url = new XmlnukeManageUrl(URLTYPE::ADMIN, "admin:ManageSites");
				$url->addParam('site', $this->_context->ContextValue('valueid'));
				$this->_context->redirectUrl($url->getUrlFull($this->_context));
				break;
			default:
			/* Nothing to do */
				break;
		}
		$this->ActionList();
		return $this->defaultXmlnukeDocument->generatePage();
	}
		
	/**
	*@param XmlParagraphCollection $paragraph
	*@return void
	*@desc Action to list sites in repository
	*/
	protected function ActionList()
	{
		$myWords = $this->WordCollection();
		$paragraph = new XmlParagraphCollection();
		$this->_block->addXmlnukeObject($paragraph);
		$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("CURRENTSITE"), true, false, false, true));
		$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("SELECTSITE"), false, false, false, true));
		
		$paragraph2 = new XmlParagraphCollection();
		$this->_block->addXmlnukeObject($paragraph2);
		$url = new XmlnukeManageUrl(URLTYPE::ADMIN, 'admin:ManageSites');
		$url->addParam('xsl', 'page');
		$editList = new XmlEditList($this->_context, $myWords->Value("SITESLIST"), $url->getUrl(), true, true, true);
		$editList->setDataSource($this->getSiteList());
		
		$field = new EditListField();
		$field->fieldData = "id";
		$editList->addEditListField($field);
		
		$field = new EditListField();
		$field->fieldData = "name";
		$field->editlistName = $myWords->Value("TXT_NAME");
		$editList->addEditListField($field);
		
		$cb = new CustomButtons();
		$cb->action = ManageSitesAction::OFFLINE ;
		$cb->enabled = true;
		$cb->alternateText = $myWords->Value("CREATEOFFLINE");
		$url->addParam('action', ManageSitesAction::OFFLINE );
		$cb->url = $url->getUrl();
		$cb->icon = "common/editlist/ic_custom.gif";
		$editList->setCustomButton($cb);
		//$editList->setPageSize(10, 0);
		$editList->setEnablePage(false);
		$paragraph2->addXmlnukeObject($editList);
	}
		
	/**
	*@param XmlParagraphCollection $paragraph
	*@return void
	*@desc Action to create repository
	*/
	protected function ActionCreate($paragraph)
	{
		$myWords = $this->WordCollection();
		$newSiteName = strtolower($this->_context->ContextValue("newsite"));
		$xslTemplate = $this->_context->ContextValue("xsltemplate");
		$newSitePath = $this->_context->SiteRootPath() . $newSiteName ;
		FileUtil::ForceDirectories($newSitePath , 0777 );
		FileUtil::ForceDirectories($newSitePath . FileUtil::Slash() . "xsl", 0777 );
		FileUtil::ForceDirectories($newSitePath . FileUtil::Slash() ."cache" , 0777 );
		FileUtil::ForceDirectories($newSitePath . FileUtil::Slash() ."offline" , 0777 );
		FileUtil::ForceDirectories($newSitePath . FileUtil::Slash() ."anydataset" , 0777 );
		FileUtil::ForceDirectories($newSitePath . FileUtil::Slash() ."lang" , 0777 );
		FileUtil::ForceDirectories($newSitePath . FileUtil::Slash() ."snippet" , 0777 );
		
		$langAvail = array();
		$langAvail = $this->_context->LanguagesAvailable();
		
		foreach(array_keys($langAvail) as $key )
		{
			self::createRepositoryForSite($this->_context->XmlHashedDir(), $xslTemplate, $newSitePath, $key, $this->_context);
		}
		$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("CREATED", $newSiteName)));
	}
		
	/**
	*@param XmlParagraphCollection $paragraph
	*@return void
	*@desc Action to create repository
	*/
	protected function ActionDelete($paragraph)
	{
		$complete = false;
		$myWords = $this->WordCollection();
		$SiteName = $this->_context->ContextValue("valueid");
		$removeSitePath = $this->_context->SiteRootPath() . $SiteName ;
		try 
		{
			FileUtil::ForceRemoveDirectories($removeSitePath);
			$complete = true;
		}
		catch (Exception $e)
		{
			if($e->getCode() == 18)
			{
				$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("TEXT_REMOVED_FAIL_DIR")));
			}
			if($e->getCode() == 16)
			{
				$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("TEXT_REMOVED_FAIL_FILE")));
			}
		}
		if ($complete) 
		{
			$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("TEXT_REMOVED")));
		}
	}
		
	/**
	*@param XmlParagraphCollection $paragraph
	*@return void
	*@desc Action to create repository
	*/
	protected function ActionCreateForm($paragraph)
	{
		$url = new XmlnukeManageUrl(URLTYPE::ADMIN, 'admin:ManageSites');
		$url->addParam('xsl', 'page');
		$url->addParam('action', ManageSitesAction::Save );
		$myWords = $this->WordCollection();
		$form = new XmlFormCollection($this->_context, $url->getUrl(), $myWords->Value("FORM_TITLE"));
		$textbox = new XmlInputTextBox($myWords->Value("FORM_NEWSITE"), 'newsite', '');
		$textbox->setDataType(INPUTTYPE::LOWER);
		$textbox->setRequired(true);

		$xsl = new XSLFilenameProcessor("", $this->_context);
		$filelist = FileUtil::RetrieveFilesFromFolder($xsl->SharedPath(), $xsl->Extension());
		$xsllist = array();
		foreach($filelist as $key=>$file)
		{
			$xsllist[FileUtil::ExtractFileName($file)] = FileUtil::ExtractFileName($file);
		}
		asort($xsllist);
		$easylist = new XmlEasyList(EasyListType::SELECTLIST, "xsltemplate", $myWords->Value("FORM_DEFAULTXSL"), $xsllist);
		$easylist->setRequired(true);
		$form->addXmlnukeObject($easylist);
		
		$button = new XmlInputButtons();
		$button->addSubmit($myWords->Value("TXT_CREATE"), 'submit');
		$form->addXmlnukeObject($textbox);
		$form->addXmlnukeObject($button);
		$paragraph->addXmlnukeObject($form);
	}
		
	/**
	*@return IIterator
	*@desc Get site informations
	*/
	protected function getSiteList()
	{
		$sites = new AnydatasetFilenameProcessor("sites", $this->_context);
		$sitesanydata = new AnyDataSet($sites);
		$siteAvail = $this->_context->ExistingSites();
		foreach($siteAvail as $key )
		{
			if (FileUtil::ExtractFileName($key) != 'CVS') 
			{
				$keySite = FileUtil::ExtractFileName($key);
				$sitesanydata->appendRow();
				$sitesanydata->addField('id', $keySite);
				$sitesanydata->addField('name', ucfirst($keySite));
			}
		}
		return $sitesanydata->getIterator();
	}
		
	//Parameters: string sitePath, string language, engine.Context context
	public static function createRepositoryForSite($hashedDir, $xslTemplate, $sitePath, $language, $context)
	{
		$repositorio = 	new XmlNukeDB($hashedDir, $sitePath . FileUtil::Slash() . "xml" . FileUtil::Slash(), $language, true);
		$processorFile = new XMLFilenameProcessor("index", $context);
		$index = $processorFile->FullName("index", "", $language) . $processorFile->Extension();
		$home = $processorFile->FullName("home", "", $language) . $processorFile->Extension();
		$notfound = $processorFile->FullName("notfound", "", $language) . $processorFile->Extension();
		$_all = $processorFile->FullName("_all", "", $language) . $processorFile->Extension();
		//try {
			if (!$repositorio->existsDocument($index))
			{
				$repositorio->saveDocumentStr($index, FileUtil::QuickFileRead($context->SiteRootPath() . "index.xml.template"));								
				$repositorio->saveDocumentStr($home, FileUtil::QuickFileRead($context->SiteRootPath() . "home.xml.template"));				
				$repositorio->saveDocumentStr($notfound, FileUtil::QuickFileRead($context->SiteRootPath() . "notfound.xml.template"));								
				$repositorio->saveDocumentStr($_all, "<?xml version=\"1.0\" encoding=\"utf-8\"?><page/>");	
			}
			else
			{
				$repositorio->recreateIndex();
			}
			$repositorio->saveIndex();
		//}
		//catch (Exception $e){}
		$xslFile = new XSLFilenameProcessor("index", $context);
		$indexXsl = $sitePath . FileUtil::Slash() . "xsl" . FileUtil::Slash() . $xslFile->FullName("", "index", $language) . $xslFile->Extension();
		$pageXsl = $sitePath . FileUtil::Slash() . "xsl" . FileUtil::Slash() . $xslFile->FullName("", "page", $language) . $xslFile->Extension();

		if (!FileUtil::Exists($indexXsl))
		{
			FileUtil::QuickFileWrite( $indexXsl, FileUtil::QuickFileRead($context->SiteRootPath() . "index.xsl.template"));
		}
		if (!FileUtil::Exists($pageXsl))
		{
			FileUtil::QuickFileWrite( $pageXsl, FileUtil::QuickFileRead($xslFile->SharedPath() . $xslTemplate));
		}
	}

}
?>