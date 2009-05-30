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

class SendPage extends BaseModule 
{
	/**
	 * To Name
	 *
	 * @var String
	 */
	private $_toName = "";
	
	/**
	 * To Email
	 *
	 * @var String
	 */
	private $_toEmail = "";
	
	/**
	 * From Name
	 *
	 * @var String
	 */
	private $_fromName = "";
	
	/**
	 * From Email
	 *
	 * @var String
	 */
	private $_fromEmail = "";
	
	/**
	 * Custom Message
	 *
	 * @var String
	 */
	private $_customMessage = "";
	
	/**
	 * Link
	 *
	 * @var String
	 */
	private $_link = "";
	
	/**
	 * Document
	 *
	 * @var XmlnukeDocument
	 */
	protected $_document;
	
	/**
	 * My Words
	 *
	 * @var LanguageCollection
	 */
	protected $_myWords;

	/**
	 * Default Constructor
	 *
	 * @return SendPage
	 */
	public function SendPage()
	{
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
	 * Setup the module receiving external parameters and assing it to private variables.
	 *
	 * @param XMLFilenameProcessor $xmlModuleName
	 * @param Conext $context
	 * @param Null $customArgs
	 */
	public function Setup($xmlModuleName, $context, $customArgs)
	{
		parent::Setup($xmlModuleName, $context, $customArgs);

		$this->_link = $this->_context->ContextValue("link");
		$this->_toName = $this->_context->ContextValue("toname");
		$this->_toEmail = $this->_context->ContextValue("tomail");
		$this->_fromName = $this->_context->ContextValue("fromname");
		$this->_fromEmail = $this->_context->ContextValue("frommail");
		$this->_customMessage = $this->_context->ContextValue("custommessage");
		if ($this->_link == "")
		{
			//$this->_link = str_replace("&","Z",$this->_context->ContextValue("HTTP_REFERER"));
			$this->_link = urlencode($this->_context->ContextValue("HTTP_REFERER"));
			if (stripos($this->_link,"sendpage")> 0)
			{
				$this->_link = "";
			}       
		}
	}

	/**
	 * eturn the LanguageCollection used in this module
	 *
	 * @return LanguageCollection
	 */
	public function WordCollection()
	{
		$myWords = parent::WordCollection();
		
		if (!($myWords->loadedFromFile()))
		{
			// English Words
			$myWords->addText("en-us", "TITLE", "Module Send page");

			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Envio de Páginas");
		}
		return $myWords;
	}

	/**
	 * CreatePage is called from module processor and decide the proper output XML.
	 *
	 * @param String $showAction
	 * @param unknown_type $showLink
	 * @param unknown_type $showMessage
	 * @return PageXml
	 */
	public function CreatePage()
	{
		$this->_myWords = $this->WordCollection();

		$this->_document = new XmlnukeDocument($this->_myWords->Value("TITLE", $this->_context->ContextValue("SERVER_NAME")),$this->_myWords->Value("ABSTRACT", $this->_context->ContextValue("SERVER_NAME")));

		if ($this->_link == "")
		{
			$this->goBack( $this->_myWords->Value("ERRORINVALID") );
		}
		elseif ($this->_action == "submit")
		{
			if (!XmlInputImageValidate::validateText($this->_context))
			{
					$this->goBack( $this->_myWords->Value("OBJECTIMAGEINVALID") );
			}
			else 
			{
				if ( ($this->_toName == "") || ($this->_toEmail == "") || ($this->_fromName == "") || ($this->_fromEmail == "") )
				{
					$this->goBack( $this->_myWords->Value("ERROR") );
				}
				else
				{
					$custMessage = $this->_myWords->Value("MESSAGE", array($this->_toName, $this->_toEmail, urldecode($this->_link), $this->_fromName, $this->_customMessage) );

					MailUtil::Mail
					(
						$this->_context,
						MailUtil::getFullEmailName($this->_fromName, $this->_fromEmail),
						MailUtil::getFullEmailName($this->_toName, $this->_toEmail),
						$this->_myWords->Value("SUBJECT"),
						"",
						$this->_fromEmail,$custMessage
					);

					$this->showMessage();
				}
			}
		}
		else
		{
			$this->showForm();
		}

		return $this->_document->generatePage();		
	}
	
	/**
	 * Show the form
	 *
	 */
	public function showForm()
	{
		$blockcenter = new XmlBlockCollection($this->_myWords->Value("MSGFILL"), BlockPosition::Center );
		$this->_document->addXmlnukeObject($blockcenter);

		$paragraph = new XmlParagraphCollection();
		$blockcenter->addXmlnukeObject($paragraph);

		$form = new XmlFormCollection($this->_context, "module:sendpage", $this->_myWords->Value("CAPTION"));
		$paragraph->addXmlnukeObject($form);

		$caption = new XmlInputCaption($this->_myWords->ValueArgs("INFO", array(urldecode($this->_link))));
		$form->addXmlnukeObject($caption);

		$hidden = new XmlInputHidden("action", "submit");
		$form->addXmlnukeObject($hidden);

		$hidden = new XmlInputHidden("link", $this->_link);
		$form->addXmlnukeObject($hidden);

		$textbox = new XmlInputTextBox($this->_myWords->Value("FLDNAME"), "fromname", "", 40);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$textbox = new XmlInputTextBox($this->_myWords->Value("FLDEMAIL"), "frommail", "", 40);
		$textbox->setDataType(INPUTTYPE::EMAIL );
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$textbox = new XmlInputTextBox($this->_myWords->Value("FLDTONAME"), "toname", "", 40);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$textbox = new XmlInputTextBox($this->_myWords->Value("FLDTOEMAIL"), "tomail", "", 40);
		$textbox->setDataType(INPUTTYPE::EMAIL );
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$memo = new XmlInputMemo($this->_myWords->Value("LABEL_MESSAGE"), "custommessage","");
		$form->addXmlnukeObject($memo);

		$form->addXmlnukeObject(new XmlInputImageValidate($this->_myWords->Value("TYPETEXTFROMIMAGE")));

		$button = new XmlInputButtons();
		$button->addSubmit($this->_myWords->Value("TXT_SUBMIT"), "");
		$form->addXmlnukeObject($button);
	}

	/**
	 * Go to the last page
	 *
	 */
	public function goBack($showMessage)
	{
		$blockcenter = new XmlBlockCollection($this->_myWords->Value("MSGERROR"), BlockPosition::Center );
		$this->_document->addXmlnukeObject($blockcenter);

		$paragraph = new XmlParagraphCollection();
		$blockcenter->addXmlnukeObject($paragraph);

		$paragraph->addXmlnukeObject(new XmlnukeText($showMessage,true));

		$anchor = new XmlAnchorCollection("javascript:history.go(-1)");
		$anchor->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("TXT_BACK")));
		$paragraph->addXmlnukeObject($anchor);
	}

	/**
	 * Show a message of the error
	 *
	 */
	public function showMessage()
	{
		$blockcenter = new XmlBlockCollection($this->_myWords->Value("MSGOK"), BlockPosition::Center);
		$this->_document->addXmlnukeObject($blockcenter);
				
		$paragraph = new XmlParagraphCollection();
		$blockcenter->addXmlnukeObject($paragraph);
				
		$paragraph->addXmlnukeObject(new XmlnukeText($_customMessage));
	}
}

?>
