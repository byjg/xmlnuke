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

class UsersGroupsActions extends ModuleAction 
{
	
}
class ManageUsersGroups extends NewBaseAdminModule 
{
	/**
	 * Url to this module
	 *
	 * @var XmlnukeManageUrl
	 */
	protected $url;
	/**
	 * User info
	 *
	 * @var UsersBase
	 */
	protected $user;
	/**
	 * Word Collection
	 *
	 * @var LanguageCollection
	 */
	protected $myWords;
	
	public function ManageUsersGroups()
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
    
    public function Setup($xmlModuleName, $context, $customArgs) 
    {
    	parent::Setup($xmlModuleName, $context, $customArgs);
    	parent::CreatePage();
		$this->url = new XmlnukeManageUrl(URLTYPE::MODULE , $this->_xmlModuleName->ToString());
		$this->user = $this->getUsersDatabase();
		$this->myWords = $this->WordCollection();
		$this->setTitlePage($this->myWords->Value("TITLE"));
		$this->setHelp($this->myWords->Value("DESCRIPTION"));
		$this->myWords = $this->WordCollection();
    }
    
    /**
     * Page logic
     *
     * @return PageXml
     */
	public function CreatePage() 
	{
		$this->addMenuOption($this->myWords->Value("CREATE_ROLE"), $this->url->getUrl());
		switch ($this->_action)
		{
			case UsersGroupsActions::Create :
			$this->actionCreate();
			break;
			case UsersGroupsActions::CreateConfirm :
			$this->actionSave();
			break;
			case UsersGroupsActions::Delete :
			$this->actionDelete();
			break;
			case UsersGroupsActions::Edit :
			$this->actionEdit();
			break;
			case UsersGroupsActions::EditConfirm :
			$this->actionEditSave();
			break;
			default:
			$this->actionList();
			break;
		}
		
		return $this->defaultXmlnukeDocument->generatePage();
	}
	
	protected function actionCreate()
	{
		$this->_mainBlock = new XmlBlockCollection($this->myWords->Value("BLOCK_TITLE_NEW"), BlockPosition::Center );
		$para = new XmlParagraphCollection();
		$this->url->addParam("action", UsersGroupsActions::CreateConfirm );
		$form = new XmlFormCollection($this->_context, $this->url->getUrl(), "");
		$textbox = new XmlInputTextBox($this->myWords->Value("FORM_NAME"), "textbox_role", "");
		$sitesArray["_all"] = $this->myWords->Value("FORM_ALLSITES");
		$sitesArray[$this->_context->getSite()] = ucfirst($this->_context->getSite());
		$select = new XmlEasyList(EasyListType::SELECTLIST , "select_sites", $this->myWords->Value("FORM_SELECTSITE"), $sitesArray, "_all");
		$button = new XmlInputButtons();
		$button->addSubmit("OK", "");
		$form->addXmlnukeObject($select);
		$form->addXmlnukeObject($textbox);
		$form->addXmlnukeObject($button);
		$para->addXmlnukeObject($form);
		$this->_mainBlock->addXmlnukeObject($para);
		$this->AddListLink($this->_mainBlock);
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_mainBlock);
	}
	
	protected function actionEdit()
	{
		$selectedRole = $this->_context->ContextValue("valueid");
		$selectedSite = $this->_context->ContextValue("editsite");
		$it = $this->user->getRolesIterator($selectedSite, $selectedRole);
		$sr = $it->moveNext();
		$selectedSite = $sr->getField($this->user->_RolesTable->Site);
		$this->_mainBlock = new XmlBlockCollection($this->myWords->Value("BLOCK_TITLE_EDIT"), BlockPosition::Center );
		$para = new XmlParagraphCollection();
		$this->url->addParam("action", UsersGroupsActions::EditConfirm );
		$form = new XmlFormCollection($this->_context, $this->url->getUrl(), "");
		$form->addXmlnukeObject(new XmlInputHidden("valueid", $selectedRole));
		$form->addXmlnukeObject(new XmlInputHidden("editsite", $selectedSite));
		$textbox = new XmlInputTextBox($this->myWords->Value("FORM_NAME"), "textbox_role", $selectedRole);
		$button = new XmlInputButtons();
		$button->addSubmit("OK", "");
		$form->addXmlnukeObject(new XmlInputLabelField($this->myWords->Value("FORM_FROMSITE"), ($selectedSite == "_all"? $this->myWords->Value("TEXT_ALLSITES"): $selectedSite)));
		$form->addXmlnukeObject($textbox);
		$form->addXmlnukeObject($button);
		$para->addXmlnukeObject($form);
		$this->_mainBlock->addXmlnukeObject($para);
		$this->AddListLink($this->_mainBlock);
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_mainBlock);
	}
	
	protected function actionEditSave()
	{
		$this->_mainBlock = new XmlBlockCollection($this->myWords->Value("BLOCK_TITLE_EDIT"), BlockPosition::Center );
		$para = new XmlParagraphCollection();
		$selectedRole = $this->_context->ContextValue("valueid");
		$selectedSite = $this->_context->ContextValue("editsite");
		$newRole = strtoupper($this->_context->ContextValue("textbox_role"));
		try
		{
			$this->user->editRolePublic($selectedSite, $selectedRole, $newRole);
			$para->addXmlnukeObject(new XmlnukeText($this->myWords->Value("MSG_EDITED"), true));		
		}
		catch (Exception $ex)
		{
			$para->addXmlnukeObject(new XmlnukeText($this->myWords->Value("MSG_ALREADYEXISTS"), true));
		}
		$this->_mainBlock->addXmlnukeObject($para);
		$this->AddListLink($this->_mainBlock);
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_mainBlock);
	}
	
	protected function actionDelete()
	{
		$this->_mainBlock = new XmlBlockCollection($this->myWords->Value("BLOCK_TITLE_DELETE"), BlockPosition::Center );
		$para = new XmlParagraphCollection();
		$selectedRole = $this->_context->ContextValue("valueid");
		$selectedSite = $this->_context->ContextValue("editsite");
		$it = $this->user->getRolesIterator($selectedSite, $selectedRole);
		$sr = $it->moveNext();
		$selectedSite = $sr->getField($this->user->_RolesTable->Site);
		$this->user->editRolePublic($selectedSite, $selectedRole);
		$para->addXmlnukeObject(new XmlnukeText($this->myWords->Value("MSG_DELETED"), true));
		$this->_mainBlock->addXmlnukeObject($para);
		$this->AddListLink($this->_mainBlock);
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_mainBlock);
	}
	
	protected function actionSave()
	{
		$this->_mainBlock = new XmlBlockCollection($this->myWords->Value("BLOCK_TITLE_NEW"), BlockPosition::Center );
		$para = new XmlParagraphCollection();
		$newRole = strtoupper($this->_context->ContextValue("textbox_role"));
		
		try 
		{
			$this->user->addRolePublic($this->_context->ContextValue("select_sites"), $newRole);
			$para->addXmlnukeObject(new XmlnukeText( $this->myWords->Value("MSG_CREATE") , true));
		}
		catch (Exception $ex)
		{
			$para->addXmlnukeObject(new XmlnukeText($this->myWords->Value("MSG_ALREADYEXISTS"), true));
		}
		
		$this->_mainBlock->addXmlnukeObject($para);
		$this->AddListLink($this->_mainBlock);
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_mainBlock);
	}
	protected function actionList()
	{
		$this->_mainBlock = new XmlBlockCollection($this->myWords->Value("BLOCK_TITLE_LIST"), BlockPosition::Center );
		
		$para = new XmlParagraphCollection();
		$this->_mainBlock->addXmlnukeObject($para);
		
		$this->AddEditListToSite($this->_mainBlock, $this->_context->getSite(), $this->getRolesFromSite($this->_context->getSite()));
		$this->url->addParam("action", UsersGroupsActions::Create);
		$this->url->addParam("site", $this->_context->getSite());
		
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_mainBlock);
	}

	
	/**
	 * Get all rules from a site
	 *
	 * @param string $site
	 * @return AnyDataSet
	 */
	protected function getRolesFromSite($site)
	{
		$newDataSet = new AnyDataSet();
		$it = $this->user->getRolesIterator($site);
		while ($it->hasNext()) {
			$sr = $it->moveNext();
			$dataArray = $sr->getFieldArray($this->user->_RolesTable->Role);
			if (sizeof($dataArray) > 0) {
				foreach ($dataArray as $roles) {
					$siteName = $sr->getField($this->user->_RolesTable->Site);
					if ($siteName == "_all") {
						$siteName = $this->myWords->Value("TEXT_ALLSITES");
					}
					$newDataSet->appendRow();
					$newDataSet->addField($this->user->_RolesTable->Site, $siteName);
					$newDataSet->addField($this->user->_RolesTable->Role, $roles);
				}	
			}
		}
		return $newDataSet;
	}
	/**
	 * Add a go back link to list roles
	 *
	 * @param XmlBlockCollection $this->_mainBlock
	 */
	protected function AddListLink($block)
	{
		$para = new XmlParagraphCollection();
		$this->_mainBlock->addXmlnukeObject($para);
		$this->url->addParam("action", UsersGroupsActions::Listing);
		$link = new XmlAnchorCollection($this->url->getUrl(), "");
		$link->addXmlnukeObject(new XmlnukeText($this->myWords->Value("LINK_LISTROLES")));
		$para->addXmlnukeObject($link);
	}
	/**
	 * Add EditList to site
	 *
	 * @param XmlBlockCollection $this->_mainBlock
	 * @param string $site
	 * @param AnyDataSet $dataset
	 */
	protected function AddEditListToSite($block, $site, $dataset)
	{
		$para = new XmlParagraphCollection();
		$this->_mainBlock->addXmlnukeObject($para);
		$this->url->addParam("editsite", $site);
		$this->url->addParam("site", $this->_context->getSite());
		
		$editList = new XmlEditList($this->_context, $this->myWords->Value("EDITLIST_TITLE", $site), $this->url->getUrl(), true, false, true, true);
		$editList->setDataSource($dataset->getIterator());
		
		$listField = new EditListField();
		$listField->editlistName = "";
		$listField->fieldData = "role";
		$editList->addEditListField($listField);
		
		$listField = new EditListField();
		$listField->editlistName = $this->myWords->Value("EDITLIST_ROLES");
		$listField->fieldData = $this->user->_RolesTable->Role;
		$editList->addEditListField($listField);
		
		$listField = new EditListField();
		$listField->editlistName = $this->myWords->Value("EDITLIST_SITES");
		$listField->fieldData = $this->user->_RolesTable->Site;
		$editList->addEditListField($listField);
		$para->addXmlnukeObject($editList);
	}
}
?>
