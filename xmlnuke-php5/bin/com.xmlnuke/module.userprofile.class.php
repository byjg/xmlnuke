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
 * UserProfile is a default module descendant from BaseModule class.
 *
 * @see com.xmlnuke.module.IModule
 * @see com.xmlnuke.module.BaseModule
 * @see com.xmlnuke.module.ModuleFactory
 */
class UserProfile extends BaseModule
{
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	protected $_user;
	
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	protected $_users;
	
	/**
	 * Url
	 *
	 * @var String
	 */
	protected $_url;
	
	/**
	 * My Words
	 *
	 * @var LanguageCollection
	 */
	protected $_myWords;
	
	/**
	 * Paragraph
	 *
	 * @var XmlParagraphCollection
	 */
	protected $_paragraph;
	
	/**
	 * Default Constructor
	 *
	 * @return UserProfile
	 */
	public function UserProfile()
	{}

	/**
	 * Return the LanguageCollection used in this module
	 *
	 * @return LanguageCollection
	 */
	public function WordCollection()
	{
		$myWords = parent::WordCollection();
		return $myWords;
	}

	/**
	 * Returns if use cache
	 *
	 * @return False
	 */
	public function useCache()
	{
		return false;
	}

	/**
	 * Requires Authentication
	 *
	 * @return True
	 */
	public function requiresAuthentication()
	{
		return true;
	}

	/**
	 * Access Granted
	 *
	 * @return True
	 */
	public function accessGranted()
	{
		return true;
	}

	##PENDENTE $this->_context->authenticatedUser() PENDENTE USER
	/**
	 * Output error message
	 *
	 * @return PageXml
	 */
	public function CreatePage()
	{
		$this->_myWords = $this->WordCollection();
		
		$title = $this->_myWords->Value("TITLE", $this->_context->ContextValue("SERVER_NAME") );
		$abstract = $this->_myWords->Value("ABSTRACT", $this->_context->ContextValue("SERVER_NAME") );
		$document = new XmlnukeDocument($title, $abstract);
		
		$this->_url = "module:UserProfile";
				
		$this->_users = $this->getUsersDatabase();
		
		$this->_user = $this->_users->getUserName( $this->_context->authenticatedUser() );
		
		$blockCenterTitle = $this->_myWords->ValueArgs("TITLE", array ($this->_user->getField($this->_users->_UserTable->Username)));
		$blockcenter = new XmlBlockCollection($blockCenterTitle, BlockPosition::Center );
		$document->addXmlnukeObject($blockcenter);
		
		$this->_paragraph = new XmlParagraphCollection();
		$blockcenter->addXmlnukeObject($this->_paragraph);

		$action = $this->_context->ContextValue("action");

		switch ($action)
		{
			case "update":
				$this->update();
				break;
			case "changepassword":
				$this->changePWD();
				break;				
		}		
		
		$this->formUserInfo();
		
		$this->formPasswordInfo();
		
		$this->formRolesInfo();	

		return $document->generatePage();
	}

	/**
	 * Update
	 *
	 */
	protected function update()
	{
		$this->_user->setField($this->_users->_UserTable->Name, $this->_context->ContextValue("name") );
		$this->_user->setField($this->_users->_UserTable->Email, $this->_context->ContextValue("email") );
		$this->_paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("UPDATEOK"), true));
		$this->_users->Save();
	}
	
	/**
	 * Change Pwd
	 *
	 */
	protected function changePWD()
	{
		if ($this->_user->getField($this->_users->_UserTable->Password) != $this->_users->getSHAPassword($this->_context->ContextValue("oldpassword")))
		{
			$this->_paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("CHANGEPASSOLDPASSFAILED") , true));
		}
		else
		{
			if ($this->_context->ContextValue("newpassword") != $this->_context->ContextValue("newpassword2"))
			{
				$this->_paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("CHANGEPASSNOTMATCH"), true));
			}
			else
			{
				$this->_user->setField($this->_users->_UserTable->Password, $this->_users->getSHAPassword($this->_context->ContextValue("newpassword")) );
				$this->_paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("CHANGEPASSOK"), true));
				$this->_users->Save();
			}
		}
	}
	
	/**
	 * Show the info of user in the form
	 *
	 */
	protected function formUserInfo()
	{
		$form = new XmlFormCollection($this->_context, $this->_url, $this->_myWords->Value("UPDATETITLE"));
		$this->_paragraph->addXmlnukeObject($form);		
		
		$hidden = new XmlInputHidden("action", "update");
		$form->addXmlnukeObject($hidden);
		
		$labelField = new XmlInputLabelField($this->_myWords->Value("LABEL_LOGIN"), $this->_user->getField($this->_users->_UserTable->Username));
		$form->addXmlnukeObject($labelField);
		
		$textBox = new XmlInputTextBox($this->_myWords->Value("LABEL_NAME"), "name",$this->_user->getField($this->_users->_UserTable->Name));
		$form->addXmlnukeObject($textBox);
		
		$textBox = new XmlInputTextBox($this->_myWords->Value("LABEL_EMAIL"), "email", $this->_user->getField($this->_users->_UserTable->Email));
		$form->addXmlnukeObject($textBox);
		
		$button = new XmlInputButtons();
		$button->addSubmit($this->_myWords->Value("TXT_UPDATE"),"");
		$form->addXmlnukeObject($button);
	}
	
	/**
	 * Form Password Info
	 *
	 */
	protected function formPasswordInfo()
	{
		$form = new XmlFormCollection($this->_context, $this->_url, $this->_myWords->Value("CHANGEPASSTITLE"));
		$this->_paragraph->addXmlnukeObject($form);
		
		$hidden = new XmlInputHidden("action", "changepassword");
		$form->addXmlnukeObject($hidden);
		
		$textbox = new XmlInputTextBox($this->_myWords->Value("CHANGEPASSOLDPASS"), "oldpassword","");
		$textbox->setInputTextBoxType(InputTextBoxType::PASSWORD );
		$form->addXmlnukeObject($textbox);
		
		$textbox = new XmlInputTextBox($this->_myWords->Value("CHANGEPASSNEWPASS"), "newpassword","");
		$textbox->setInputTextBoxType(InputTextBoxType::PASSWORD );
		$form->addXmlnukeObject($textbox);
		
		$textbox = new XmlInputTextBox($this->_myWords->Value("CHANGEPASSNEWPASS2"), "newpassword2","");
		$textbox->setInputTextBoxType(InputTextBoxType::PASSWORD );
		$form->addXmlnukeObject($textbox);
		
		$button = new XmlInputButtons();
		$button->addSubmit($this->_myWords->Value("TXT_CHANGE"),"");
		$form->addXmlnukeObject($button);
	}
	
	/**
	 * Form Roles Info
	 *
	 */
	protected function formRolesInfo()
	{		
		$form = new XmlFormCollection($this->_context, $this->_url, $this->_myWords->Value("OTHERTITLE"));
		$this->_paragraph->addXmlnukeObject($form);

		$easyList = new XmlEasyList(EasyListType::SELECTLIST , "", $this->_myWords->Value("OTHERSITE"), $this->_users->returnUserProperty($this->_context->authenticatedUserId(), UserProperty::Site));
		$form->addXmlnukeObject($easyList);
		
		$easyList = new XmlEasyList(EasyListType::SELECTLIST , "", $this->_myWords->Value("OTHERROLE"), $this->_users->returnUserProperty($this->_context->authenticatedUserId(), UserProperty::Role));
		$form->addXmlnukeObject($easyList);
		
//		$px->addSelect4($form, $this->_myWords->Value("OTHERSITE"), "", $this->_users->returnUserProperty($this->_context->authenticatedUserId(), UserProperty::Site));
//		$this->_px->addSelect4($form, , "", $this->_users->returnUserProperty($this->_context->authenticatedUserId(), UserProperty::Role));
	}
}
?>
