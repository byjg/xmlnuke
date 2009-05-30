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
 * XSLTheme is a default module descendant from BaseModule class.
 * 
 * @see com.xmlnuke.module.IModule
 * @see com.xmlnuke.module.BaseModule
 * @see com.xmlnuke.module.ModuleFactory
 */
class XSLTheme extends BaseModule
{
	/**
	 * Default Constructor
	 *
	 * @return XSLTheme
	 */
	public function XSLTheme()
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
			$myWords->addText("en-us", "TITLE", "Module XSL Theme");
			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Temas Xsl");
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
	 * Output error message
	 *
	 * @return PageXml object
	 */
	public function CreatePage() 
	{ 
		$myWords = $this->WordCollection();
		
		$this->defaultXmlnukeDocument = new XmlnukeDocument($myWords->Value("TITLE"),$myWords->Value("ABSTRACT"));
		
		$blockcenter = new XmlBlockCollection($myWords->Value("TITLE"), BlockPosition::Center );
		$this->defaultXmlnukeDocument->addXmlnukeObject($blockcenter);
		
		$paragraph = new XmlParagraphCollection();
		$blockcenter->addXmlnukeObject($paragraph);

		$xslUsed = array();

		$xsl = new XSLFilenameProcessor("", $this->_context);
		$filelist = FileUtil::RetrieveFilesFromFolder($xsl->PrivatePath(), $xsl->Extension());
		$this->generateList($myWords->Value("LISTPERSONAL"), $paragraph, $filelist, $xslUsed, $xsl);

		$filelist = FileUtil::RetrieveFilesFromFolder($xsl->SharedPath(), $xsl->Extension());	
		$this->generateList($myWords->Value("LISTGENERIC"), $paragraph, $filelist, $xslUsed, $xsl);
		
		return $this->defaultXmlnukeDocument->generatePage();
	}

	/**
	 * Create and show the list of Xsl Templates 
	 *
	 * @param String $caption
	 * @param XmlParagraphCollection $paragraph
	 * @param Array $filelist
	 * @param Array $xslUsed
	 * @param XSLFilenameProcessor $xsl
	 */
	private function generateList($caption, $paragraph, $filelist, $xslUsed, $xsl)
	{
		$paragraph->addXmlnukeObject(new XmlnukeText($caption,true));
				
		$listCollection = new XmlListCollection(XmlListType::UnorderedList );
		$paragraph->addXmlnukeObject($listCollection);
			
		foreach($filelist as $file)
		{
			$xslname = FileUtil::ExtractFileName($file);
			$xslname = $xsl->removeLanguage($xslname);
			if(!in_array ($xslname, $xslUsed))				
			{
				$objectList = new XmlnukeSpanCollection();
				$listCollection->addXmlnukeObject($objectList);
			
				$xslUsed[] = $xslname;					
				if ($xslname == "index")
				{
					$anchor = new XmlAnchorCollection("engine:xmlnuke?xml=index&xsl=index");
					$anchor->addXmlnukeObject(new XmlnukeText($xslname,true));
					$objectList->addXmlnukeObject($anchor);
				}
				else
				{
					$anchor = new XmlAnchorCollection("module:XSLTheme?xsl=" . $xslname);
					$anchor->addXmlnukeObject(new XmlnukeText($xslname,true));
					$objectList->addXmlnukeObject($anchor);
				}
			}
		}
	}			
}
?>
