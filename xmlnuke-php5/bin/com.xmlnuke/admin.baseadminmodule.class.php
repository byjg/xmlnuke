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
*Base Admin Modules
*/
abstract class BaseAdminModule extends BaseModule
{
	/**
	*@var PageXML
	*/
	protected $_px;
	/**
	*@var DOMNode
	*/
	protected $_mainBlock;
	/**
	*@var DOMNode
	*/
	private $_help;
	/**
	*@var DOMNode
	*/
	private $_menu;
	/**
	*@param 
	*@return void 
	*@desc BaseAdminModule Constructor
	*/
	public function BaseAdminModule()
	{}
	
	/**
	*@return LanguageCollection 
	*@desc Implements some base XML options used for ALL Admin Modules.
	*/
	public function WordCollection()
	{	
		$lang = LanguageFactory::GetLanguageCollection($this->_context, LanguageFileTypes::ADMINMODULE, $this->_xmlModuleName->ToString());
		return $lang;
	}
	
	/**
	*@param 
	*@return PageXml 
	*@desc 
	*/
	public function CreatePage() 
	{
		//parent::CreatePage();
		$this->_px = new PageXml();
		
		$this->_px->setTitle("XMLNuke Administration Tool");
		$this->_px->setAbstract("XMLNuke Administration Tool");
	
		$this->_mainBlock = $this->_px->addBlockCenter("Menu");
		$this->_help = $this->_px->addParagraph($this->_mainBlock);

		return $this->_px;     
	}

	/**
	*@param 
	*@return bool 
	*@desc Admin Modules always requires authentication. This method is sealed.
	*/
	public function requiresAuthentication()
	{
		return true;
	}
	
	/**
	*@param string $strMenu
	*@param string $strLink
	*@param string $target
	*@return void
	*@desc 
	*/
	protected function addMenuOption($strMenu, $strLink, $target = null)
	{
		$this->_px->addMenuItem($strLink, $strMenu, "");
	}

	/**
	*@param string $strHelp
	*@return void
	*@desc 
	*/
	protected function setHelp($strHelp)
	{
		$this->_px->addText($this->_help, $strHelp);
	}

	/**
	*@param string $strTitle
	*@return void
	*@desc Title config
	*/
	protected function setTitlePage($strTitle)
	{
		//DOMNode
		$tit = XMLUtil::SelectSingleNode($this->_px->getDomObject()->documentElement,"blockcenter[title='Menu']/title");
		$this->_px->setTitle($this->_px->getTitle() . " - " . $strTitle);
		$this->_px->setAbstract($this->_px->getTitle());
		$tit->nodeValue = $strTitle;
	}

	/**
	*@param 
	*@return bool 
	*@desc 
	*/
	public function isAdmin()
	{
		return true;
	}

}

?>
