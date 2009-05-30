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

class Download extends BaseModule
{
	/**
	 * My Words
	 *
	 * @var LanguageCollection
	 */
	protected  $_myWords;
	/**
	 * Download
	 *
	 * @var AnydataSet
	 */
	protected $_download;
	/**
	 * File
	 *
	 * @var String
	 */
	protected $_file;
	/**
	 * Category
	 *
	 * @var String
	 */
	protected $_category;
	/**
	 * Paragraph
	 *
	 * @var XmlParagraphCollection
	 */
	protected $_paragraph;
	
	/**
	 * Default Constructor
	 *
	 * @return Download
	 */
	public function Download()
	{}

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
	 * Setup the module receiving external parameters and assing $iterator to private variables.
	 *
	 * @param XMLFilenameProcessor $xmlModuleName
	 * @param Context $context
	 * @param Object $customArgs
	 */
	public function Setup($xmlModuleName, $context, $customArgs)
	{
		parent::Setup($xmlModuleName, $context, $customArgs);
	}

	/**
	 * Return the LanguageCollection used in this module
	 *
	 * @return unknown
	 */
	public function WordCollection()
	{
		$myWords = parent::WordCollection();
		if (!$myWords->loadedFromFile())
		{
			// English Words
			$myWords->addText("en-us", "TITLE", "Module Download");
			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Download");
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
		$this->_myWords = $this->WordCollection();
		
		$document = new XmlnukeDocument($this->_myWords->Value("TITLE"), $this->_myWords->Value("ABSTRACT"));
		
		$blockcenter = new XmlBlockCollection($this->_myWords->Value("BLOCKTITLE"), BlockPosition::Center );
		$document->addXmlnukeObject($blockcenter);
		
		$this->_paragraph = new XmlParagraphCollection();
		$blockcenter->addXmlnukeObject($this->_paragraph);
		
		$this->_category = $this->_context->ContextValue("cat");
		$this->_file = $this->_context->ContextValue("file");

		$downloadFile = new AnydatasetFilenameProcessor("_download", $this->_context);
		$this->_download = new AnyDataSet( $downloadFile );
		
		if ($this->_file != "") 
		{
			$this->showForm();	
		}
		else if ($this->_category == "") 
		{
			$this->showCategories();
		}
		else
		{
			$this->showFiles();
		}
		return $document->generatePage();		
	}
	
	/**
	 * Show files
	 *
	 */
	public function showFiles()
	{
		$this->_paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("SELECTFILE"),true));
		
		$listCollection = new XmlListCollection(XmlListType::UnorderedList);
		$this->_paragraph->addXmlnukeObject($listCollection);
			 
		$iteratorFilter = new IteratorFilter();
		$iteratorFilter->addRelation("TYPE", Relation::Equal, "FILE");
		$iteratorFilter->addRelation("cat_id", Relation::Equal, $this->_category);
		$iterator = $this->_download->getIterator($iteratorFilter);
		while ($iterator->hasNext())
		{
			$singleRow = $iterator->moveNext();
			
			$objectLineList = new XmlnukeSpanCollection();
			$listCollection->addXmlnukeObject($objectLineList);

			$objectLineList->addXmlnukeObject(new XmlnukeText($this->getField($singleRow, "name"),true));
			$objectLineList->addXmlnukeObject(new XmlnukeBreakLine());
			$objectLineList->addXmlnukeObject(new XmlnukeText($this->getField($singleRow, "description")));
			
			$objectLineList->addXmlnukeObject(new XmlnukeBreakLine());
			$objectLineList->addXmlnukeObject(new XmlnukeText("( "));
			$link = new XmlAnchorCollection("module:Download?file=".$singleRow->getField("file_id"));
			$link->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("SELECTFORDOWNLOAD"),true));
			$objectLineList->addXmlnukeObject($link);
			$objectLineList->addXmlnukeObject(new XmlnukeText(" | "));
			$link =  new XmlAnchorCollection($this->getField($singleRow, "seemore"));
			$link->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("MOREINFO"),true));
			$objectLineList->addXmlnukeObject($link);
			$objectLineList->addXmlnukeObject(new XmlnukeText(" )"));
		}

		$this->_paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		$this->_paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		$text = new XmlnukeText($this->_myWords->Value("TXT_BACK"));
		$link = new XmlAnchorCollection("module:Download");
		$link->addXmlnukeObject($text);
		$this->_paragraph->addXmlnukeObject($link);		
	}
	
	/**
	 * Show Categories
	 *
	 */
	public function showCategories()
	{
		$this->_paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("SELECTCATEGORY"),true));
			
		$listCollection = new XmlListCollection(XmlListType::UnorderedList);
		$this->_paragraph->addXmlnukeObject($listCollection);
		
		$iteratorFilter = new IteratorFilter();
		$iteratorFilter->addRelation("TYPE", Relation::Equal, "CATEGORY");
		$iterator = $this->_download->getIterator($iteratorFilter);
		while ($iterator->hasNext())
		{
			$singleRow = $iterator->moveNext();

			$objectList = new XmlnukeSpanCollection();
			$listCollection->addXmlnukeObject($objectList);
			
			$anchor =  new XmlAnchorCollection("module:Download?cat=" . $singleRow->getField("cat_id"));
			$anchor->addXmlnukeObject(new XmlnukeText($this->getField($singleRow, "name"),true));
			$objectList->addXmlnukeObject($anchor);
			$objectList->addXmlnukeObject(new XmlnukeBreakLine());
			$objectList->addXmlnukeObject(new XmlnukeText(" ".$this->getField($singleRow, "description")));
		}
	}
	
	/**
	 * Show Form
	 *
	 */
	public function showForm()
	{
		$iteratorFilter = new IteratorFilter();
		$iteratorFilter->addRelation("TYPE", Relation::Equal, "FILE");
		$iteratorFilter->addRelation("file_id", Relation::Equal, $this->_file);
		$iterator = $this->_download->getIterator($iteratorFilter);
		if ($iterator->hasNext())
		{
			$singleRow = $iterator->moveNext();
			if ($this->_action != "download")
			{
				$form = new XmlFormCollection($this->_context, "module:Download?file=" . $this->_file, $this->_myWords->Value("FORMTITLE"));					
				$caption = new XmlInputCaption($this->_myWords->Value("FORMWARNING"));
				$form->addXmlnukeObject($caption);
				
				$label = new XmlInputLabelField($this->_myWords->Value("FORMFILE"), $this->getField($singleRow, "name"));
				$form->addXmlnukeObject($label);
					
				$textbox = new XmlInputTextBox($this->_myWords->Value("LABEL_NAME"),"txtName","",40);
				$form->addXmlnukeObject($textbox);
				
				$textbox = new XmlInputTextBox($this->_myWords->Value("LABEL_EMAIL"),"txtEmail","",40);
				$textbox->setDataType(INPUTTYPE::EMAIL);
				$form->addXmlnukeObject($textbox);
					
				$hidden = new XmlInputHidden("action","download");
				$form->addXmlnukeObject($hidden);
					
				$button = new XmlInputButtons();
				$button->addSubmit($this->_myWords->Value("FORMSUBMIT"),"");
				$form->addXmlnukeObject($button);
								
				$this->_paragraph->addXmlnukeObject($form);
			}
			else
			{
				try
				{	
					$message = $this->_myWords->Value("EMAILMESSAGE", $singleRow->getField("name"));
					$emailto = MailUtil::getEmailFromID($this->_context, $singleRow->getField("emailto"));
					if ($emailto != "")
					{
						MailUtil::Mail($this->_context, MailUtil::getFullEmailName($this->_context->ContextValue("txtName"), $this->_context->ContextValue("txtEmail") ), $emailto, $this->_myWords->Value("EMAILSUBJECT", $singleRow->getField("name") ), "", "", $message);	
					}
				}
				catch(Exception $e)
				{
					 // Just No actions 
				}
				$this->_context->redirectUrl($singleRow->getField("url"));
			}
		}
		else
		{
			$this->_paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("FILEERROR")));
		}
	}
	
	/**
	 * Get Field
	 *
	 * @param SingleRow $singleRow
	 * @param String $fieldName
	 * @return String
	 */
	private function getField($singleRow, $fieldName)
	{
		$result = $singleRow->getField($fieldName . "_" . strtolower($this->_context->Language()->getName()));
		if ($result == "") 
		{
			$result = $singleRow->getField($fieldName);
		}
		return $result;
	}
}
        
?>
