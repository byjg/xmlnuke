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

/// <summary>
/// Summary description for com.
/// </summary>
class ManageXMLDBIndex extends BaseAdminModule
{
	public function ManageXMLDBIndex()
	{
	}

	public function useCache()
	{
		return false;
	}
	public function  getAccessLevel() 
        { 
              return AccessLevel::CurrentSiteAndRole; 
        } 

        public function getRole() 
        { 
               return array("MANAGER", "OPERATOR"); 
        }

	public function CreatePage() 
	{
		parent::CreatePage(); // Doesnt necessary get PX, because PX is protected!
		$myWords = $this->WordCollection();
		$this->setHelp($myWords->Value("DESCRIPTION"));
		$this->setTitlePage($myWords->Value("TITLE"));

		$action = strtolower($this->_action);

		$block = $this->_px->addBlockCenter($myWords->Value("WORKINGAREA"));
		//XmlNode paragraph;

		// Delete a Group Node
		if ($action == "")
		{
			$paragraph = $this->_px->addParagraph($block);
			$this->_px->addHref($paragraph, "admin:ManageXMLDBIndex?action=recreate", $myWords->Value("LINKRECREATE", strtolower($this->_context->Language()->getName()) ), null);
		}

		if ($action == "recreate")
		{
			$this->_context->getXMLDataBase()->recreateIndex();
			$this->_context->getXMLDataBase()->saveIndex();
			$paragraph = $this->_px->addParagraph($block);
			$this->_px->addBold($paragraph, $myWords->Value("RECREATE"));
		}

		return $this->_px;
	}

}
?>