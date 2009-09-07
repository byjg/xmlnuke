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
require_once("module.loginbase.class.php");

class ModuleActionLogin extends ModuleAction 
{
	const LOGIN = 'action.LOGIN';
	const FORGOTPASSWORD = 'action.FORGOTPASSWORD';
	const FORGOTPASSWORDCONFIRM = 'action.FORGOTPASSWORDCONFIRM';
}

/**
 * Login is a default module descendant from BaseModule class.
 * This class shows/edit the profile from the current user.
 * 
 * @see module.IModule.class.php
 * @see module.BaseModule.class.php
 * @subpackage xmlnuke.modules
 */
class LoginBasic extends LoginBase
{
	/**
	 * Users
	 *
	 * @var UsersAnyDataSet
	 */
	protected $_users;
	
	/**
	 * Module
	 *
	 * @var String
	 */
	private  $_module = "loginbasic";
	
	/**
	 * BlockCenter
	 *
	 * @var XmlBlockCollection
	 */
	protected $_blockCenter;
	
	/**
	 * Default constructor
	 *
	 * @return Login
	 */
	public function Login()
	{}

	/**
	 * Return the LanguageCollection used in this module
	 *
	 * @return LanguageCollection
	 */
	public function WordCollection()
	{
		$myWords = parent::WordCollection();

		if (!$myWords->loadedFromFile())
		{
			// English Words
			$myWords->addText("en-us", "TITLE", "Module Login");

			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Login");
		}

		return $myWords;
	}

	/**
	 * Returns if use cache
	 *
	 * @return bool
	 */
	public function useCache()
	{
		return false;
	}

	/**
	 * Create Page
	 *
	 * @return PageXml
	 */
	public function CreatePage()
	{
		$myWords = $this->WordCollection();
		
		$this->_users = $this->getUsersDatabase();
		
		$this->defaultXmlnukeDocument->setPageTitle($myWords->Value("TITLELOGIN"));
		
		$this->_blockCenter = new XmlBlockCollection( $myWords->Value("TITLELOGIN"), BlockPosition::Center );
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_blockCenter);
		
		$this->_urlReturn = $this->_context->ContextValue("ReturnUrl");
		
		switch ($this->_action) 
		{
			case ModuleActionLogin::LOGIN :
				$this->MakeLogin();
				break;
			case ModuleActionLogin::FORGOTPASSWORD :
				$this->ForgotPassword();
				break;
			case ModuleActionLogin::FORGOTPASSWORDCONFIRM :
				$this->ForgotPasswordConfirm();
				break;
			default:
				$this->FormLogin();
				break;
		}
		return $this->defaultXmlnukeDocument->generatePage();
	}
	
	/**
	 * Make Login
	 *
	 */
	protected function MakeLogin()
	{
		$myWords = $this->WordCollection();
		$user = $this->_users->validateUserName($this->_context->ContextValue("loguser"), $this->_context->ContextValue("password"));
		if ($user == null)
		{
			$container = new XmlnukeUIAlert($this->_context, UIAlert::BoxAlert);
			$container->setAutoHide(5000);
			$container->addXmlnukeObject(new XmlnukeText($myWords->Value("LOGINFAIL"), true));
			$this->_blockCenter->addXmlnukeObject($container);
			$this->FormLogin();
		}
		else
		{
			$this->updateInfo($user->getField($this->_users->_UserTable->Username), $user->getField($this->_users->_UserTable->Id));
		}
	}

	/**
	 * Form Login
	 *
	 */
	protected function FormLogin()
	{
		$myWords = $this->WordCollection();
		
		$paragraph = new XmlParagraphCollection();
		$this->_blockCenter->addXmlnukeObject($paragraph);
		
		$url = new XmlnukeManageUrl(URLTYPE::MODULE, $this->_module);
		$url->addParam('action', ModuleActionLogin::LOGIN);
		$url->addParam('ReturnUrl', $this->_urlReturn);
		
		$form = new XmlFormCollection($this->_context, $url->getUrl() , $myWords->Value("LOGINTITLE"));
		$form->setJSValidate(true);
		$paragraph->addXmlnukeObject($form);
		
		$textbox = new XmlInputTextBox($myWords->Value("LABEL_NAME"), 'loguser', $this->_context->ContextValue("loguser"), 20);
		$textbox->setInputTextBoxType(InputTextBoxType::TEXT );
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		
		$textbox = new XmlInputTextBox($myWords->Value("LABEL_PASSWORD"), 'password', '', 20);
		$textbox->setInputTextBoxType(InputTextBoxType::PASSWORD );
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		
		$button = new XmlInputButtons();
		$button->addSubmit($myWords->Value("TXT_LOGIN"), 'submit_button');
		$form->addXmlnukeObject($button);		
		
		$label = new XmlInputLabelObjects($myWords->Value("LOGINPROBLEMSMESSAGE"));
		$url = new XmlnukeManageUrl(URLTYPE::MODULE, $this->_module);
		$url->addParam('action', ModuleActionLogin::FORGOTPASSWORD);
		$url->addParam('ReturnUrl', $this->_urlReturn);
		
		$link = new XmlAnchorCollection($url->getUrl(), null);
		$link->addXmlnukeObject(new XmlnukeText($myWords->Value("LOGINFORGOTMESSAGE")));
		$label->addXmlnukeObject($link);
		$form->addXmlnukeObject($label);
	}

	/**
	 * Forgot Password
	 *
	 */
	protected function ForgotPassword()
	{
		$myWords = $this->WordCollection();
		
		$paragraph = new XmlParagraphCollection();
		$this->_blockCenter->addXmlnukeObject($paragraph);
		
		$url = new XmlnukeManageUrl(URLTYPE::MODULE, $this->_module);
		$url->addParam('action', ModuleActionLogin::FORGOTPASSWORDCONFIRM);
		$url->addParam('ReturnUrl', $this->_urlReturn);
		
		$form = new XmlFormCollection($this->_context, $url->getUrl() , $myWords->Value("FORGOTPASSTITLE"));
		$paragraph->addXmlnukeObject($form);
		
		$textbox = new XmlInputTextBox($myWords->Value("LABEL_EMAIL"), 'email', $this->_context->ContextValue("email"), 40);
		$textbox->setInputTextBoxType(InputTextBoxType::TEXT );
		$textbox->setDataType(INPUTTYPE::EMAIL);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		
		$button = new XmlInputButtons();
		$button->addSubmit($myWords->Value("FORGOTPASSBUTTON"), 'submit_button');
		$form->addXmlnukeObject($button);
	}
	
	/**
	 * Forgot Password Confirm
	 *
	 */
	protected function ForgotPasswordConfirm()
	{
		$myWords = $this->WordCollection();
		
		$container = new XmlnukeUIAlert($this->_context, UIAlert::BoxInfo);
		$container->setAutoHide(5000);
		$this->_blockCenter->addXmlnukeObject($container);
		
		$user = $this->_users->getUserEMail( $this->_context->ContextValue("email") );
		
		if (is_null($user))
		{
			$container->addXmlnukeObject(new XmlnukeText($myWords->Value("FORGOTUSERFAIL"), true));			
			$this->ForgotPassword();
		}
		else
		{
			$newpassword = $this->getRandomPassword();
			$user->setField($this->_users->_UserTable->Password, $this->_users->getSHAPassword($newpassword));
			$this->sendWelcomeMessage($myWords, $user->getField($this->_users->_UserTable->Name), $user->getField($this->_users->_UserTable->Username), $user->getField($this->_users->_UserTable->Email), $newpassword );
			$this->_users->Save();
			$container->addXmlnukeObject(new XmlnukeText($myWords->Value("FORGOTUSEROK"), true));
			$this->FormLogin();
		}
	}
}
?>
