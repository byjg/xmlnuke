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
namespace Xmlnuke\Core\Admin;

use ByJG\AnyDataset\Repository\AnyDataset;
use Xmlnuke\Core\Classes\FileBrownserUserType;
use Xmlnuke\Core\Classes\PageXml;
use Xmlnuke\Core\Classes\XmlBlockCollection;
use Xmlnuke\Core\Classes\XmlFileBrowser;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Enum\BlockPosition;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;
use Xmlnuke\Core\Processor\ForceFilenameLocation;

class FileManagement extends NewBaseAdminModule
{
	public function FileManagement()
	{
	}

	public function useCache()
	{
		return false;
	}
	public function  getAccessLevel() 
    { 
          return AccessLevel::OnlyRole;
    } 

    public function getRole() 
    { 
           return "MANAGER"; 
    }
    
    /**
     * @var WordCollection
     */
    protected $_myWords;
    /**
     * @var XmlBlockCollection
     */
    protected $_block;

    /**
     * Create Page Method
     *
     * @return PageXml
     */
	public function CreatePage() 
	{
		parent::CreatePage(); 
	
		$this->_myWords = $this->WordCollection();
		
		$this->defaultXmlnukeDocument->addMenuItem("module:Xmlnuke.Admin.FileManagement",$this->_myWords->Value("FILEMANAGEMENT"),"");
		
		$this->_block = new XmlBlockCollection($this->_myWords->Value("FILEMANAGEMENT"), BlockPosition::Center);
		$this->setTitlePage($this->_myWords->Value("TITLE"));
		$this->setHelp($this->_myWords->Value("DESCRIPTION"));
		$this->defaultXmlnukeDocument->addXmlnukeObject($this->_block);
		
		
		//get the current folder
		$root = $this->_context->get("folder");
		
		$browser = new XmlFileBrowser($root, $this->_action, $this->_context);
		
		//SET FILEBROWSER ACESS LEVEL
		$processor = new AnydatasetFilenameProcessor("filemanagement");
		$processor->setFilenameLocation(ForceFilenameLocation::UseWhereExists );
		$anyDataSet = new AnyDataset($processor->FullQualifiedNameAndPath());

		$ignoreAdmin = false;

		$it = $anyDataSet->getIterator();
		while($it->hasNext())
		{
			$row = $it->moveNext();
			switch ($row->getField("type"))
			{
				case "INITIAL_DIR":
					$initial_dir = $row->getFieldArray("value");
					$browser->setSubFoldersPermitted($initial_dir);
					break;
				case "VALID_FILE_NEW":
					$file_new_list = $row->getFieldArray("value");
					$browser->setFileNewList($file_new_list);
					break;
				case "VALID_FILE_VIEW":
					$file_view_list = $row->getFieldArray("value");
					$browser->setFileViewList($file_view_list);
					break;
				case "VALID_FILE_EDIT":
					$file_edit_list = $row->getFieldArray("value");
					$browser->setFileEditList($file_edit_list);
					break;
				case "VALID_FILE_DELETE":
					$file_delete_list = $row->getFieldArray("value");
					$browser->setFileDeleteList($file_delete_list);
					break;
				case "VALID_FILE_UPLOAD":
					$file_upload_list = $row->getFieldArray("value");
					$browser->setFileUploadList($file_upload_list);
					break;
				case "VALID_FILE_MAX_UPLOAD":
					$file_max_upload = $row->getField("value");
					$browser->setFileMaxUpload($file_max_upload);
					break;
				case "PERMISSION":
					$browser->setFileNew($row->getField("file_new"));
					$browser->setFileView($row->getField("file_view"));
					$browser->setFileEdit($row->getField("file_edit"));
					$browser->setFileDelete($row->getField("file_delete"));
					$browser->setFileUpload($row->getField("file_upload"));
					$browser->setFolderNew($row->getField("folder_new"));
					$browser->setFolderView($row->getField("folder_view"));
					$browser->setFolderEdit($row->getField("folder_edit"));
					$browser->setFolderDelete($row->getField("folder_delete"));
					$ignoreAdmin = ($row->getField("ignore_admin")=="true");
					break;
			}
		}		
		
		if ($this->isUserAdmin() && !$ignoreAdmin)
			$browser->setUserType(FileBrownserUserType::ADMIN );
		
		$this->_block->addXmlnukeObject($browser);                                                                             
		
		return $this->defaultXmlnukeDocument->generatePage();
	}
}
?>