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
 * @package xmlnuke
 */
namespace Xmlnuke\Admin\Modules;

use Xmlnuke\Core\Admin\NewBaseAdminModule;
use Xmlnuke\Core\Classes\CrudField;
use Xmlnuke\Core\Classes\CrudFieldCollection;
use Xmlnuke\Core\Classes\XmlBlockCollection;
use Xmlnuke\Core\Classes\XmlnukeCrudAnydata;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Enum\BlockPosition;
use Xmlnuke\Core\Enum\INPUTTYPE;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;

class ConfigEmail extends NewBaseAdminModule
{
	public function ConfigEmail()
	{
	}

	public function useCache()
	{
		return false;
	}

	public function getAccessLevel() 
	{
		return AccessLevel::OnlyRole;
	}

	public function getRole()
	{
		return array( "MANAGER", "OPERATOR" );
	}
	
	public function CreatePage() 
	{
		parent::CreatePage(); // Doesnt necessary get PX, because PX is protected!
		
		$myWords = $this->WordCollection();
		
		$block = new XmlBlockCollection($myWords->Value("TITLE"), BlockPosition::Center);
		$this->setTitlePage($myWords->Value("TITLE"));
		$this->setHelp($myWords->Value("DESCRIPTION"));
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
				
		// configEmail File
		$configEmailFile = new AnydatasetFilenameProcessor("_configemail");

		$fields = new CrudFieldCollection();
		
		$field = CrudField::FactoryMinimal("destination_id", $myWords->Value("DESTINATIONBOX"), 20, true, true);
		$field->key = true;
		$field->dataType = INPUTTYPE::UPPERASCII;
		$fields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("name", $myWords->Value("LABEL_NAME"), 40, true, true);
		$field->maxLength = 100;
		$fields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("email", $myWords->Value("LABEL_EMAIL"), 40, true, true);
		$field->maxLength = 500;
		$field->dataType = INPUTTYPE::EMAIL;
		$fields->addCrudField($field);
		
		$processor = new XmlnukeCrudAnydata($this->_context, $fields, $myWords->Value("TITLE"), "module:Xmlnuke.Admin.ConfigEmail", null, $configEmailFile);
		$block->addXmlnukeObject($processor);
		
		return $this->defaultXmlnukeDocument;		
	}

}
?>