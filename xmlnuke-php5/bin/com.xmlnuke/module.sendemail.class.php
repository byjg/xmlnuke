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
 * SendEmail is a sample module descendant from BaseModule class.
 * This class shows how to create a simple module to send a email from Xmlnuke site.
 * Main features:
 * Receive external parameters;
 * Output different XML document for each parameter and action;
 * Smtp send mail
 * 
 * @see com.xmlnuke.module.IModule
 * @see com.xmlnuke.module.BaseModule
 */
class SendEmail extends BaseModule
{
	/**
	 * To Name Id
	 *
	 * @var String
	 */
	private $_toName_ID = "";
	
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
	 * Subject
	 *
	 * @var String
	 */
	private $_subject = "";
	
	/**
	 * Message
	 *
	 * @var String
	 */
	private $_message = "";

	/**
	 * Extra Field Message (extra_fields)
	 * field1=Caption1;field2=Caption2;...
	 *
	 * @var String
	 */
	private $_extraMessage = "";
	
	/**
	 * Redirect
	 *
	 * @var String
	 */
	private $_redirect = "";

	/**
	 * Default Constructor
	 *
	 * @return SendEmail
	 */
	public function SendEmail()
	{}

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
	 * @param Context $context
	 * @param Object $customArgs
	 */
	public function Setup($xmlModuleName, $context, $customArgs)
	{
		parent::Setup($xmlModuleName, $context, $customArgs);
		$this->_toName_ID = $this->_context->ContextValue("toname_id");
		$this->_fromName = $this->_context->ContextValue("name");
		$this->_fromEmail = $this->_context->ContextValue("email");
		$this->_subject = $this->_context->ContextValue("subject");
		$this->_message = $this->_context->ContextValue("message");
		$this->_redirect = $this->_context->ContextValue("redirect");
		$this->_extraMessage = "";
		$aux = $this->_context->ContextValue("extra_fields");
		if ($aux != "")
		{
			$fields = explode(";", $aux);
			foreach($fields as $key=>$field)
			{
				$detail = explode("=", $field);
				$valor = $this->_context->ContextValue($detail[0]);
				$this->_extraMessage .= $detail[1] . ": " . $valor . "\n";
			}
		}
	}

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
			$myWords->addText("en-us", "TITLE", "Module Send Email");

			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Envio de Email");
		}

		return $myWords;
	}

	/**
	 * CreatePage is called from module processor and decide the proper output XML.
	 *
	 * @return XML object
	 */
	public function CreatePage()
	{
		$myWords = $this->WordCollection();

		$ht = array();
		$hasError = false;

		if ($this->_fromName == "")
		{
			$ht[$myWords->Value("FLDNAME")] = $myWords->Value("ERRORBLANK");
			$hasError = true;
		}
		if ($this->_fromEmail == "")
		{
			$ht[$myWords->Value("FLDEMAIL")] = $myWords->Value("ERRORBLANK");
			$hasError = true;
		} 
		else
		{
			if (strrpos( $this->_fromEmail, "@") === false )
			{       
				$ht[$myWords->Value("FLDEMAIL")] = $myWords->Value("FLDEMAIL") . " " . $myWords->Value("ERRORINVALID");
				$hasError = true;
			}
		}
		if ($this->_subject == "")
		{
			$ht[$myWords->Value("FLDSUBJECT")] = $myWords->Value("ERRORBLANK");
			$hasError = true;
		}
		if ($this->_message == "")
		{
			$ht[$myWords->Value("FLDMESSAGE")] = $myWords->Value("ERRORBLANK");
			$hasError = true;
		}

		if  ($hasError)
		{
			return $this->CreatePageArgs($myWords->Value("MSGERROR"), $ht);
		}
		elseif (!XmlInputImageValidate::validateText($this->_context))
		{
			$document = new XmlnukeDocument($myWords->ValueArgs("TITLE", array( $this->_context->ContextValue("SERVER_NAME")) ), $myWords->ValueArgs("ABSTRACT", array( $this->_context->ContextValue("SERVER_NAME")) ));
			$blockcenter = new XmlBlockCollection($myWords->Value("MSGERROR"), BlockPosition::Center );
			$document->addXmlnukeObject($blockcenter);
			
			$form = new XmlFormCollection($this->_context, "module:sendemail", $myWords->Value("MSGERROR"));
			$form->addXmlnukeObject(new XmlInputCaption($myWords->Value("RETRYVALIDATE")));
			$form->addXmlnukeObject(new XmlInputHidden("toname_id", $this->_context->ContextValue("toname_id")));
			$form->addXmlnukeObject(new XmlInputHidden("name", $this->_context->ContextValue("name")));
			$form->addXmlnukeObject(new XmlInputHidden("email", $this->_context->ContextValue("email")));
			$form->addXmlnukeObject(new XmlInputHidden("subject", $this->_context->ContextValue("subject")));
			$form->addXmlnukeObject(new XmlInputHidden("message", $this->_extraMessage . $this->_context->ContextValue("message")));
			$form->addXmlnukeObject(new XmlInputHidden("redirect", $this->_context->ContextValue("redirect")));
			$form->addXmlnukeObject(new XmlInputImageValidate(""));
			$buttons = new XmlInputButtons();
			$buttons->addSubmit($myWords->Value("RETRY"), "");
			$form->addXmlnukeObject($buttons);
			$blockcenter->addXmlnukeObject($form);
			
			return $document->generatePage();
		}
		else
		{

			MailUtil::Mail($this->_context,
							MailUtil::getFullEmailName($this->_fromName, $this->_fromEmail),
							MailUtil::getEmailFromID($this->_context, $this->_toName_ID),
							$this->_subject,
							"",
							$this->_fromEmail,
							$this->_extraMessage . $this->_message);

			if ($this->_redirect != "")
			{
				//Redirect Here!!
				//Response.End
				return $document->generatePage();
			}
			else
			{
				$ht[$myWords->Value("FLDNAME")] = $this->_fromName . " [" . $this->_fromEmail . "]" ;
				$ht[$myWords->Value("FLDSUBJECT")] = $this->_subject;
				$ht[$myWords->Value("FLDMESSAGE")] = $this->_message ;

				return $this->CreatePageArgs($myWords->Value("MSGOK"), $ht);
			}
		}
	}

	/**
	 * Create the PageXml object from CreatePage() parameters
	 *
	 * @param String $title
	 * @param Array $ht
	 * @return PageXml
	 */
	private function CreatePageArgs($title, $ht)
	{
		$myWords = $this->WordCollection();
		
		$document = new XmlnukeDocument($myWords->ValueArgs("TITLE", array( $this->_context->ContextValue("SERVER_NAME")) ), $myWords->ValueArgs("ABSTRACT", array( $this->_context->ContextValue("SERVER_NAME")) ));
		
		$blockcenter = new XmlBlockCollection($myWords->Value("TITRESP"), BlockPosition::Center );
		$document->addXmlnukeObject($blockcenter);
		
		$paragraph = new XmlParagraphCollection();
		$blockcenter->addXmlnukeObject($paragraph);

		$paragraph->addXmlnukeObject(new XmlnukeText(" "));
		$paragraph->addXmlnukeObject(new XmlnukeText($title,true));
		$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		
		foreach ( $ht as $key => $value)
		{
			$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
			$paragraph->addXmlnukeObject(new XmlnukeText($key.":",true));
			$paragraph->addXmlnukeObject(new XmlnukeText(" ".$value));
		}

		$anchor = new XmlAnchorCollection("javascript:history.go(-1)","");
		$text = new XmlnukeText($myWords->Value("TXT_BACK"));
		$anchor->addXmlnukeObject($text);

		return $document->generatePage();
	}
}
?>
