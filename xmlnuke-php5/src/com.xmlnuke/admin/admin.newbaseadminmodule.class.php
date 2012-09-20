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
 * Template pattern for xmlnuke administration modules 
 * @package xmlnuke
 */
abstract class NewBaseAdminModule extends BaseModule
{
	/**
	*@var PageXML
	*/
	protected $_px;
	/**
	*@var XmlBlockCollection
	*/
	protected $_mainBlock;
	/**
	*@var XmlParagraphCollection
	*/
	protected $_help;
	/**
	*@var XmlParagraphCollection
	*/
	protected $_menu;
	
	/**
	*@param 
	*@return void 
	*@desc BaseAdminModule Constructor
	*/
	public function NewBaseAdminModule()
	{}
	
	/**
	*@return LanguageCollection 
	*@desc Implements some base XML options used for ALL Admin Modules.
	*/
	public function WordCollection()
	{	
		if ($this->_words == null)
		{
			$this->_words = LanguageFactory::GetLanguageCollection($this->_context, LanguageFileTypes::ADMINMODULE, $this->_xmlModuleName->ToString());
		}
		return $this->_words;
	}
	
	/**
	*@param 
	*@return PageXml 
	*@desc 
	*/
	public function CreatePageOld() 
	{
		$this->_px = parent::CreatePage();
		
		$this->_px->setTitle("XMLNuke Administration Tool");
		$this->_px->setAbstract("XMLNuke Administration Tool");
	
		$this->_mainBlock = $this->_px->addBlockCenter("Menu");
		$this->_help = $this->_px->addParagraph($this->_mainBlock);
		$this->_menu =$this->_px->addParagraph($this->_mainBlock);
		
		$this->_px->addHref($this->_menu, "admin:engine?site=".$this->_context->getSite() . "&lang=" . $this->_context->Language()->getName(), "Menu", null);

		return $this->_px;     
	}
	/**
	*@return void 
	*@desc Create default visual elements to admin tools
	*/
	public function CreatePage() 
	{
		$this->_mainBlock = new XmlBlockCollection('Menu', BlockPosition::Center );
		$this->_help = new XmlParagraphCollection();
		//$this->_menu = new XmlParagraphCollection();
		$this->_mainBlock->addXmlnukeObject($this->_help);
		//$this->_mainBlock->addXmlnukeObject($this->_menu);
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_mainBlock);
		//$url = new XmlnukeManageUrl(URLTYPE::ADMIN, '');
		//$url->addParam('site', $this->_context->getSite());
		//$link = new XmlAnchorCollection($url->getUrl(), '');
		//$link->addXmlnukeObject(new XmlnukeText('Menu'));
		//$this->_menu->addXmlnukeObject($link);
		$lang = $this->CreateMenuAdmin();

		$this->defaultXmlnukeDocument->setPageTitle("XMLNuke");
		$this->defaultXmlnukeDocument->setAbstract($lang->Value("CONTROLPANEL_TITLE"));
		$this->defaultXmlnukeDocument->addMetaTag("controlpaneltitle", $lang->Value("CONTROLPANEL_TITLE"));
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
	protected function addMenuOption($strMenu, $strLink, $target ="")
	{
		$this->defaultXmlnukeDocument->addMenuItem($strLink, $strMenu, "");
	}

	/**
	*@param string $strHelp
	*@return void
	*@desc 
	*/
	protected function setHelp($strHelp)
	{
		$this->_help->addXmlnukeObject(new XmlnukeText($strHelp));
	}

	/**
	*@param string $strTitle
	*@return void
	*@desc Title config
	*/
	protected function setTitlePage($strTitle)
	{
		$this->_mainBlock->setTitle($strTitle);
	}

	/**
	 * Defines an admin module
	*@param 
	*@return bool 
	*@desc 
	*/
	public function isAdmin()
	{
		return true;
	}
	
	/**
	 * Return true if the current user is an administrator.
	 *
	 * @return bool
	 */
	public function isUserAdmin()
	{
		$user = $this->getUsersDatabase();
		$sr = $user->getUserId($this->_context->authenticatedUserId());
		return ($sr->getField($user->getUserTable()->Admin) == "yes");
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $group
	 * @return IIterator
	 */
	protected function GetAdminGroups($group = "")
	{
		$keys = array_keys($this->GetAdminModulesList());
		if ($group != "")
		{
			if (in_array($group, $keys))
			{
				$keys = array($group);
			}
			else
			{
				$keys = array();
			}
		}
		
		$arr = new ArrayDataSet($keys, "name");
		return $arr->getIterator();
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $group
	 * @return array()
	 */
	protected function GetAdminModules($group)
	{
		$arr = $this->GetAdminModulesList();

		return $arr[$group];
	}
	
	protected $_adminModulesList = null;

	protected function GetAdminModulesList()
	{
		if ($this->_adminModulesList == null)
		{
			$this->_adminModulesList = array();

			// Nodes
			$rowNode = "group/module";
			$colNode = array();
			$colNode["group"] = "../@name";
			$colNode["name"] = "@name";
			$colNode["icon"] = "icon";
			$colNode["url"] = "url";
			$colNode["command"] = "@command";

			// Read from Generic XML
			$xmlProcessor = new AdminModulesXMLFilenameProcessor();
			for($i=0;$i<2;$i++)
			{
				if ($i==0)
				{
					$xmlProcessor->setFilenameLocation(ForceFilenameLocation::SharedPath);
				}
				else
				{
					$xmlProcessor->setFilenameLocation(ForceFilenameLocation::PrivatePath);
				}

				$configFile = $xmlProcessor->FullQualifiedNameAndPath();
				if (FileUtil::Exists($configFile))
				{
					$config = FileUtil::QuickFileRead($configFile);
					$dataset = new XmlDataSet($this->_context, $config, $rowNode, $colNode);
					foreach ($dataset->getIterator() as $sr)
					{
						if (array_key_exists($sr->getField("group"), $this->_adminModulesList) || ($i==0))
						{
							if ($sr->getField("command") != "@hidegroup")
							{
								$this->_adminModulesList[$sr->getField("group")][$sr->getField("name")] = array($sr->getField("icon"), $sr->getField("url"));
							}
							else
							{
								unset($this->_adminModulesList[$sr->getField("group")]);
							}
						}
						else
						{
							$x = array();
							$x[$sr->getField("group")][$sr->getField("name")] = array($sr->getField("icon"), $sr->getField("url"));
							$this->_adminModulesList = $x + $this->_adminModulesList;
						}
					}
				}
			}
		}
		return $this->_adminModulesList;
	}
		
	/**
	 * Enter description here...
	 *
	 * @param XmlnukeDocument $xmlDoc
	 */
	protected function CreateMenuAdmin()
	{
		// Load Language file for Module Object
		$lang = LanguageFactory::GetLanguageCollection($this->_context, LanguageFileTypes::ADMININTERNAL, null);
		
		// Create a Menu Item for GROUPS and MODULES. 
		// This menu have CP_ before GROUP NAME
		$itGroup = $this->GetAdminGroups();
		
		while ($itGroup->hasNext())
		{
			$srGroup = $itGroup->moveNext();
			$this->defaultXmlnukeDocument->addMenuGroup($lang->Value("GROUP_" . strtoupper($srGroup->getField("name"))), "CP_" . $srGroup->getField("name"));
			
			$arrModules = $this->GetAdminModules($srGroup->getField("name"));
			
			foreach ($arrModules as $key=>$value)
			{
				$url = $value[1];
				$name = $key;
				$icon = $value[0];

				if ($url != "")
				{
					$this->defaultXmlnukeDocument->addMenuItem(
						$url,
						$lang->Value("MODULE_TITLE_" . strtoupper($name)),
						$lang->Value("MODULE_ABSTRACT_" . strtoupper($name)),
						"CP_" . $srGroup->getField("name"),
						$icon);
				}
			}
		}

		return $lang;
	}	
}

?>