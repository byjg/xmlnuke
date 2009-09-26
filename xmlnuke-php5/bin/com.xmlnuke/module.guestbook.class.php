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

class Guestbook extends  BaseModule
{
	/**
	 * Guestbook File
	 *
	 * @var AnydatasetFilenameProcessor
	 */
	private $guestbookFile;
	/**
	 * Cache File
	 *
	 * @var XMLCacheFilenameProcessor
	 */
	private $cacheFile;

	/**
	 * Default Constructor
	 *
	 * @return Guestbook
	 */
	public function Guestbook()
	{}

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
		$this->guestbookFile = new AnydatasetFilenameProcessor("guestbook", $context);
		$this->cacheFile = new XMLCacheFilenameProcessor("guestbook", $context);
	}

	/**
	 * Dynamic in$formation about use cache or not.
	 *
	 * @return Bool
	 */
	public function useCache()
	{
		if (strtolower($this->_action) == "write")
		{
			FileUtil::DeleteFilesFromPath($this->cacheFile);
			return false;
		}
		else
		{
			return parent::useCache(); // parent::useCache always return true, except when receive reset or nocache parameters
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
			$myWords->addText("en-us", "TITLE", "Module Guestbook");

			if (!$myWords->loadedFromFile())
			{

			}
		}
		return $myWords;
	}

	/**
	 * CreatePage is called from module processor only if doesnt use cache or doesnt exist cache file and decide the proper output XML.
	 *
	 * @return PageXml
	 */
	public function CreatePage()
	{
		$myWords = $this->WordCollection();

		$document = new XmlnukeDocument($myWords->Value("TITLE", $this->_context->ContextValue("SERVER_NAME")), $myWords->Value("ABSTRACT", $this->_context->ContextValue("SERVER_NAME") ));

		$guestbook = new AnyDataSet($this->guestbookFile);

		if (strtolower($this->_action) == "write")
		{
			if (XmlInputImageValidate::validateText($this->_context))
			{
				$message = $this->_context->ContextValue("txtMessage");
				$name = $this->_context->ContextValue("txtName");
				if(empty($name) || empty($message))
				{
					$blockCenter = new XmlBlockCollection($myWords->Value("ERRORTITLE"), BlockPosition::Center );
					$document->addXmlnukeObject($blockCenter);

					$paragraph = new XmlParagraphCollection();
					$blockCenter->addXmlnukeObject($paragraph);

					$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("ERRORMESSAGE"), true));
				}
				else
				{
					$this->addMessageToDB( $guestbook, $this->_context->ContextValue("txtName"), $this->_context->ContextValue("txtEmail"), $this->_context->ContextValue("txtMessage") );
				}
			}
			else
			{
				$blockCenter = new XmlBlockCollection($myWords->Value("ERRORTITLE"), BlockPosition::Center );
				$document->addXmlnukeObject($blockCenter);
				$paragraph = new XmlParagraphCollection();
				$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("OBJECTIMAGEINVALID"), true));
				$blockCenter->addXmlnukeObject($paragraph);
			}
		}

		$blockCenter = new XmlBlockCollection($myWords->Value("MYGUEST"), BlockPosition::Center );
		$document->addXmlnukeObject($blockCenter);

		$iterator = $guestbook->getIterator(null);
		while ($iterator->hasNext())
		{
			$singleRow = $iterator->moveNext();
			$this->defineMessage($blockCenter, $singleRow);
		}

		$blockCenter = new XmlBlockCollection($myWords->Value("SIGN"), BlockPosition::Center );
		$document->addXmlnukeObject($blockCenter);

		$paragraph = new XmlParagraphCollection();
		$blockCenter->addXmlnukeObject($paragraph);

		$form = new XmlFormCollection($this->_context, "module:guestbook?action=write", $myWords->Value("FILL"));
		$textbox = new XmlInputTextBox($myWords->Value("LABEL_NAME"), "txtName", "", 30);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$textbox =  new XmlInputTextBox($myWords->Value("LABEL_EMAIL"), "txtEmail", "", 30);
		$textbox->setDataType(INPUTTYPE::EMAIL );
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$memo = new XmlInputMemo($myWords->Value("LABEL_MESSAGE"), "txtMessage", "");
		$memo->setSize(40,4);
		$form->addXmlnukeObject($memo);

		$form->addXmlnukeObject(new XmlInputImageValidate($myWords->Value("TYPETEXTFROMIMAGE")));

		$button = new XmlInputButtons();
		$button->addSubmit($myWords->Value("TXT_SUBMIT"), "");
		$form->addXmlnukeObject($button);

		$paragraph->addXmlnukeObject($form);

		return $document->generatePage();
	}

	/**
	 * Auxiliary function do add data to Anydataset and save $it->
	 *
	 * @param AnyDataSet $anydata
	 * @param String $fromName
	 * @param String $fromMail
	 * @param String $message
	 */
	private function addMessageToDB($anydata, $fromName, $fromMail, $message)
	{
		$anydata->insertRowBefore(0);
		$anydata->addField("fromname", $fromName);
		$anydata->addField("frommail", $fromMail);
		$anydata->addField("message", $message);
		$anydata->addField("date", date("Y-m-d H:i:s"));
		$anydata->addField("ip", $this->_context->ContextValue("REMOTE_ADDR"));
		$anydata->Save($this->guestbookFile);

		try
		{
			MailUtil::Mail($this->_context,
							MailUtil::getFullEmailName($fromName, $fromMail),
							MailUtil::getEmailFromID($this->_context, "DEFAULT"),
							"[Xmlnuke Guestbook] Message Added",
							"", "",
							$message);
		}
		catch (Exception $e)
		{
			 // Just No actions
		}
	}

	/**
	 * Auxiliary function do setup each $message from $guestbook
	 *
	 * @param XmlBlockCollection $blockCenter
	 * @param SingleRow $singleRow
	 */
	private function defineMessage($blockCenter, $singleRow)
	{
		$paragraph = new XmlParagraphCollection();
		$blockCenter->addXmlnukeObject($paragraph);

		$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		$email = $singleRow->getField("frommail");
		$emailPos = strpos($email, '@');
		$text = $singleRow->getField("fromname") . " (xxxxx" . substr($email, $emailPos) . ")";
		$paragraph->addXmlnukeObject(new XmlnukeText($text, true));

		$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		$paragraph->addXmlnukeObject(new XmlnukeText($singleRow->getField("date"), false, true));

		$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		$paragraph->addXmlnukeObject(new XmlnukeText($singleRow->getField("message")));

		$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		$paragraph->addXmlnukeObject(new XmlnukeText($singleRow->getField("ip"), false, true));
	}
}

?>
