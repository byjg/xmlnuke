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
 * NotFound is a default module descendant from BaseModule class.
 * This class runs only if the requested module not found.
 * 
 * @see com.xmlnuke.module.IModule
 * @see com.xmlnuke.module.BaseModule
 * @see com.xmlnuke.module.ModuleFactory
 */
class NotFound extends BaseModule
{
	/**
	 * Error message
	 *
	 * @var String
	 */
	private $_ErrorMessage;

	/**
	 * Default Constructor
	 *
	 * @return NotFound
	 */
	public function NotFound()
	{}

	/**
	 * This method receive a external error message and show it.
	 *
	 * @param Object $customArg
	 */
	public function CustomSetup($customArg)
	{
		$this->_ErrorMessage = $customArg;
	}

	/**
	 * Return the LanguageCollection used in this module
	 *
	 * @return LanguageCollection
	 */
	public function WordCollection()
	{
		//LanguageCollection
		$myWords = parent::WordCollection();

		if (!$myWords->loadedFromFile())
		{
			// English Words
			$myWords->addText("en-us", "TITLE", "Module Not Found");

			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Modulo Não Encontrado");
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

		$this->defaultXmlnukeDocument = new XmlnukeDocument($myWords->Value("TITLE"),"");
		
		$blockcenter = new XmlBlockCollection($myWords->Value("TITLE"), BlockPosition::Center );
		$this->defaultXmlnukeDocument->addXmlnukeObject($blockcenter);
		
		$paragraph = new XmlParagraphCollection();
		$blockcenter->addXmlnukeObject($paragraph);
		
		$paragraph->addXmlnukeObject(new XmlnukeText($myWords->ValueArgs("MESSAGE", array($this->_ErrorMessage) )));

		return $this->defaultXmlnukeDocument->generatePage();
	}
}
?>
