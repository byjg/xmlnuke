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

class Search extends BaseModule
{
	/**
	 * Error Object
	 *
	 * @var LoadErrorStructure
	 */
	public $_errorObject;
	
	/**
	 * Title Page
	 *
	 * @var String
	 */
	public $_titlePage = "no title";
	
	/**
	 * Abstract Page
	 *
	 * @var String
	 */
	public $_abstractPage = "no title";
	
	/**
	 * Text Search
	 *
	 * @var String
	 */
	public $txtSearch = "";
	
	/**
	 * Document
	 *
	 * @var XmlnukeDocument
	 */
	protected $_document;
	
	/**
	 * Default Constructor
	 *
	 * @return Search
	 */
	public function Search()
	{}
	
	/**
	 * Add custom setup elements
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
	 * Enter description here...
	 *
	 * @param unknown_type $customArg
	 */
	public function CustomSetup($customArg)
	{
				
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
			$myWords->addText("en-us", "TITLE", "Module Search");
		
			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Procura");
		}
		return $myWords;
	}

	/**
	 * Returns if use cache
	 *
	 * @return Bool
	 */
	public function useCache() 
	{
		return false;
	}

	/**
	 * Logic of your module
	 *
	 * @return PageXml
	 */
	public function CreatePage() 
	{		
		$myWords = $this->WordCollection();		

		$this->_titlePage = $myWords->Value("TITLE", $this->_context->ContextValue("SERVER_NAME") );
		$this->_abstractPage = $myWords->Value("ABSTRACT", $this->_context->ContextValue("SERVER_NAME") );
		
		$this->_document = new XmlnukeDocument($this->_titlePage, $this->_abstractPage);
		
		$this->txtSearch = $this->_context->ContextValue("txtSearch");

		if ($this->txtSearch != "")
		{
			$this->Form();
			$doc = $this->Find();
		}
		else
		{
			$this->Form();
		}
		return $this->_document->generatePage();
	}
	
	/**
	 * Show Form
	 *
	 */
	protected function Form()
	{
		$myWords = $this->WordCollection();
		
		$blockCenter = new XmlBlockCollection($myWords->Value("PAGETITLE"), BlockPosition::Center);
		$this->_document->addXmlnukeObject($blockCenter);
		
		$paragraph = new XmlParagraphCollection();
		$blockCenter->addXmlnukeObject($paragraph);
		
		$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("PAGETEXT")));
		
		$form_target = "module:search" ;
		
		$form = new XmlFormCollection($this->_context, $form_target, $myWords->Value("CAPTION"));
		$paragraph->addXmlnukeObject($form);
		$form->setJSValidate(true);

		$textbox = new XmlInputTextBox($myWords->Value("txtSearch"), "txtSearch", $this->txtSearch, 40);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		
		$checkbox = new XmlInputCheck($myWords->Value("chkAll"), "checkAll", "all");
		$checkbox->setChecked($this->_context->ContextValue("checkAll"));
		$form->addXmlnukeObject($checkbox);
		
		$button = new XmlInputButtons();
		$button->addSubmit($myWords->Value("SUBMIT") , "");
		$form->addXmlnukeObject($button);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param XmlnukeDocument $xmlnukeDoc
	 */
	protected function Find()
	{
		$myWords = $this->WordCollection();
		
		$xmlnukeDB = $this->_context->getXMLDataBase();
		
		$arr = $xmlnukeDB->searchDocuments($this->txtSearch, $this->_context->ContextValue("checkAll") != "");

		$blockCenter = new XmlBlockCollection($myWords->Value("BLOCKRESULT"), BlockPosition::Center);
		$this->_document->addXmlnukeObject($blockCenter);
		
		if ($arr == null)
		{
			$paragraph = new XmlParagraphCollection();
			$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("NOTFOUND")));
			$blockCenter->addXmlnukeObject($paragraph);
		}
		else
		{
			$nodeTitleList = array("/meta/title");
			$nodeAbstractList = array("/meta/abstract");

			$configSearchFile = new AnydatasetFilenameProcessor("_configsearch", $this->_context);
			$configSearch = new AnyDataSet( $configSearchFile );
			
			$iterator = $configSearch->getIterator();
			while ($iterator->hasNext())
			{
				$singleRow = $iterator->moveNext();
				$nodeTitleList[] = $sr->getField("nodetitle");
				$nodeAbstractList[] = $sr->getField("nodeabstract") ;
			}

			foreach($arr as $s)
			{
				$singleName = FilenameProcessor::StripLanguageInfo($s);

				try
				{
					$file = new XMLFilenameProcessor($singleName, $this->_context); 					
					$docResult = $this->_context->getXMLDataBase()->getDocument($file->FullQualifiedName(), null);
					$nodeResult = $this->getNode($nodeTitleList, $docResult);
					$titulo = ($nodeResult == null) ? $myWords->Value("NOTITLE") : $nodeResult->nodeValue;
					$nodeResult = $this->getNode($nodeAbstractList, $docResult);
					$abstr = ($nodeResult == null) ? "" : $nodeResult->nodeValue;
					
					$paragraph = new XmlParagraphCollection();
					$blockCenter->addXmlnukeObject($paragraph);
					
					$href = new XmlAnchorCollection("engine:xmlnuke?xml=$singleName", "");
					$href->addXmlnukeObject(new XmlnukeText($titulo));
					$paragraph->addXmlnukeObject($href);
					
					$paragraph->addXmlnukeObject(new XmlnukeText(" ["));
					
					$href = new XmlAnchorCollection("engine:xmlnuke?xml=$singleName&xsl=rawxml", "");
					$href->addXmlnukeObject(new XmlnukeText($myWords->Value("VIEWXML")));
					$paragraph->addXmlnukeObject($href);
					
					$paragraph->addXmlnukeObject(new XmlnukeText("]"));
					$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
					$paragraph->addXmlnukeObject(new XmlnukeText($abstr));
				}
				catch (Exception $e)
				{
					$paragraph = new XmlParagraphCollection();
					$paragraph->addXmlnukeObject(new XmlnukeText($s . " (" . $myWords->Value("NOTITLE") . ")"));
					$blockCenter->addXmlnukeObject($paragraph);
				}
			}
			$paragraph = new XmlParagraphCollection();
			$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("DOCFOUND", sizeof($arr) )));
			$blockCenter->addXmlnukeObject($paragraph);
		}
	}	
	
	
	/**
	 * Get Node
	 *
	 * @param Array $list
	 * @param DOMDocument $doc
	 * @return null
	 */
	private function getNode($list, $doc)
	{
		foreach($list as $item)
		{
			$result = XmlUtil::selectSingleNode($doc->documentElement, $item);

			if ($result != null)
			{
				return $result;
			}
		}
		return null;
	}
}
?>
