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
	
class ControlPanel extends NewBaseAdminModule
{
	/**
	 * Enter description here...
	 *
	 * @var string
	 */
	protected $_group;
	public function setGroup($value)
	{
		$this->_group = $value;
	}
	public function getGroup()
	{
		return $this->_group;
	}
	
	public function ControlPanel()
	{
	}

	public function useCache()
	{
		return false;
	}
	public function  getAccessLevel() 
        { 
              return AccessLevel::OnlyAuthenticated; 
        } 

	public function CreatePage() 
	{
		if ($this->_context->ContextValue("logout") != "")
		{
			$this->_context->redirectUrl("admin:controlpanel");
		}
		
		parent::CreatePage();
		$mywords = $this->WordCollection();
		
		$this->bindParameteres();
		
		$this->setTitlePage($mywords->Value("CONTROLPANEL"));
		$this->setHelp($mywords->Value("CONTROLPANEL_HELP"));
		
		$it = $this->GetAdminGroups($this->getGroup());
		if ($it->hasNext())
		{
			$sr = $it->moveNext();
			
			$xmlObj = new XmlnukeStringXML("<listmodules group=\"CP_" . $sr->getField("name") . "\" />");
			$this->defaultXmlnukeDocument->addXmlnukeObject($xmlObj);
			//Debug::PrintValue($sr->getField("name"));
		}
		else 
		{
			throw new Exception("Admin Group not found!");
		}
		
		$block = new XmlBlockCollection($mywords->Value("BLOCKINFO_TITLE"), BlockPosition::Center );
		$paragraph = new XmlParagraphCollection();
		$block->addXmlnukeObject($paragraph);
		
		$paragraph->addXmlnukeObject(new XmlnukeText($mywords->Value("INFO_USER", array($this->_context->authenticatedUser(), $this->_context->authenticatedUserId())), false, false, false, true));
		$paragraph->addXmlnukeObject(new XmlnukeText($mywords->Value("INFO_SITE", $this->_context->getSite(), false, false, false, true)));
		
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
		
		return $this->defaultXmlnukeDocument->generatePage();
	}
	
}
?>
