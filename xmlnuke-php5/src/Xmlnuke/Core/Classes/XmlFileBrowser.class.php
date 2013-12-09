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
namespace Xmlnuke\Core\Classes;

class  FileBrownserUserType
{
	const ADMIN = 1;
	const USER = 2;
}


/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Classes;

class  FileBrowserEditListType
{
	const SUBFOLDER = 1;
	const FILE = 2;
}

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Classes;

use DOMNode;
use Exception;
use Xmlnuke\Core\AnyDataset\ArrayDataSet;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\CustomButtons;
use Xmlnuke\Core\Enum\LanguageFileTypes;
use Xmlnuke\Core\Enum\MultipleSelectType;
use Xmlnuke\Core\Enum\UIAlert;
use Xmlnuke\Core\Exception\NotFoundException;
use Xmlnuke\Core\Locale\LanguageCollection;
use Xmlnuke\Core\Locale\LanguageFactory;
use Xmlnuke\Core\Processor\ForceFilenameLocation;
use Xmlnuke\Core\Processor\UploadFilenameProcessor;
use Xmlnuke\Util\FileUtil;

class  XmlFileBrowser extends XmlnukeDocumentObject
{
	/**
	 * @var FileBrownserUserType
	 */
	protected $_userType;	
	
	/**
	 * @var String
	 */
	protected $_currentFolder;
	
	/**
	 * @var String
	 */
	protected $_currentSubFolder;	
	
	/**
	 * @var Array
	 */
	protected $_subFoldersPermitted;	
	
	/**
	 * @var Bool
	 */
	protected $_folderNew;
	
	/**
	 * @var Bool
	 */
	protected $_folderView;
	
	/**
	 * @var Bool
	 */
	protected $_folderEdit;
	
	/**
	 * @var Bool
	 */
	protected $_folderDelete;

	/**
	 * @var Bool
	 */
	protected $_fileNew;
	
	/**
	 * @var Bool
	 */
	protected $_fileView;
	
	/**
	 * @var Bool
	 */
	protected $_fileEdit;	
	
	/**
	 * @var Bool
	 */
	protected $_fileDelete;	
	
	/**
	 * @var Bool
	 */
	protected $_fileUpload;	
	
	/**
	 * @var Array
	 */
	protected $_fileNewList;
	
	/**
	 * @var Array
	 */
	protected $_fileViewList;
	
	/**
	 * @var Array
	 */
	protected $_fileEditList;	

	/**
	 * @var Array
	 */
	protected $_fileDeleteList;	
	
	/**
	 * @var Array
	 */
	protected $_fileUploadList;
	
	/**
	 * @var Int
	 */
	protected $_fileMaxUpload;	
	
	/**
	 * @var String
	 */
	protected $_module;
	
	/**
	 * @var Context
	 */
	protected $_context;
	
	/**
	 * @var XmlBlockCollection
	 */
	protected $_blockTop;

	/**
	 * @var XmlTableColumnCollection
	 */
	protected $_leftCol;

	/**
	 * @var XmlTableColumnCollection
	 */
	protected $_rightCol;

	/**
	 * @var Action
	 */
	protected $_action;
	
	/**
	 * @var LanguageCollection
	 */
	protected $_lang;
	
	/**
	 * True or false, if exist directory to show in editList
	 *
	 * @var Bool
	 */
	protected $_existDirectory;
	
	/**
	 * Method Constructor
	 *
	 * @param String $root
	 * @param Action $action
	 * @param Context $context
	 * @return XmlFileBrowser
	 */
	public function __construct($root, $action, $context)
	{
		parent::__construct();

		$this->_context = $context;
		$this->_currentFolder = $root;
		$this->_action = $action;
		$this->_module = $context->getModule();

		//permission
		$this->_folderNew = false;
		$this->_folderView = false;
		$this->_folderEdit = false;
		$this->_folderDelete = false;
		$this->_fileNew = false;
		$this->_fileView = false;
		$this->_fileEdit = false;
		$this->_fileDelete = false;
		$this->_fileUpload = false;
		$this->_fileNewList = array();
		$this->_fileViewList = array();
		$this->_fileEditList = array();
		$this->_fileDeleteList = array();	
		$this->_fileUploadList = array();
		$this->_fileMaxUpload = 0;
		$this->_subFoldersPermitted = array();

		$this->_existDirectory = true;
		
		$this->_userType = FileBrownserUserType::USER;		
		
		$this->_lang = LanguageFactory::GetLanguageCollection(LanguageFileTypes::OBJECT, "com.xmlnuke.classes.xmlfilebrowser");
	}
	
	/**
	 * Set User Type
	 *
	 * @param FileBrownserUserType $userType
	 */
	public function setUserType($userType)
	{
		$this->_userType = $userType;
	}
	

	/**
	 * Contains specific instructions to generate all XML informations. 
	 * This method is processed only one time. Usually is the last method processed.
	 *
	 * @param DOMNode $current
	 */
	public function generateObject($current)
	{
		$block = new XmlnukeSpanCollection();

		$this->_blockTop = new XmlContainerCollection();
		$block->addXmlnukeObject($this->_blockTop);

		$table = new XmlTableCollection();
		$table->setStyle("border: 1px solid black; width: 100%");
		$block->addXmlnukeObject($table);

		$tableRow = new XmlTableRowCollection();
		$table->addXmlnukeObject($tableRow);

		$this->_leftCol = new XmlTableColumnCollection();
		$this->_leftCol->setStyle("width: 30%; vertical-align: top");
		$tableRow->addXmlnukeObject($this->_leftCol);

		$this->_rightCol = new XmlTableColumnCollection();
		$this->_rightCol->setStyle("width: 70%; vertical-align: top");
		$tableRow->addXmlnukeObject($this->_rightCol);

		$showFiles = false;

		switch ($this->_action)
		{
			case 'new':
				$this->formNew();
				break;
			case 'edit':
				$this->formEdit();
				break;
			case 'confirmedit':
				$this->confirmEdit();
				break;
			case 'view':
				$this->view();
				$showFiles = true;
				break;
			case 'delete':
				$this->delete();
				break;
			case 'uploadform':
				$this->formUpload();
				break;
			case 'uploadfile':
				$this->uploadFile();
				break;
			case 'create':
				$this->createNew();
				break;
			default:
				$showFiles = true;
		}

		$this->showBreadCumbs();
		$this->showDirectories();

		if ($showFiles)
		{
			$this->showFiles();
		}
		
		$block->generateObject($current);
		
		//Debug::PrintValue($this->_currentFolder);
	}

	protected function showBreadCumbs()
	{
		//ROOT FOLDER
		$anchor = new XmlAnchorCollection("module:".$this->_module);
		$anchor->addXmlnukeObject(new XmlnukeText("Root"));
		$this->_blockTop->addXmlnukeObject($anchor);

		if ($this->_currentFolder != "")
			$this->_blockTop->addXmlnukeObject(new XmlnukeText(" / "));

		//TREE FOLDERS
		$treeFolders = $this->getTreeFolder();

		//CURRENT SUB FOLDER
		$anchor = new XmlAnchorCollection("module:".$this->_module."&folder=".$this->_currentSubFolder);
		$anchor->addXmlnukeObject(new XmlnukeText($this->_currentSubFolder));
		$this->_blockTop->addXmlnukeObject($anchor);

		if ($this->_currentFolder != "")
			$this->_blockTop->addXmlnukeObject(new XmlnukeText(" / "));

		// Show Bread Cumbs
		$fullFolder = "";
		foreach ($treeFolders as $folder)
		{
			if ($fullFolder == "")
				$fullFolder =  $this->_currentSubFolder . FileUtil::Slash() . $folder;
			else
				$fullFolder .= FileUtil::Slash().$folder;

			$anchor = new XmlAnchorCollection("module:".$this->_module."&folder=".$fullFolder);
			$anchor->addXmlnukeObject(new XmlnukeText($folder));
			$this->_blockTop->addXmlnukeObject($anchor);
			$this->_blockTop->addXmlnukeObject(new XmlnukeText(" ".FileUtil::Slash()." "));
		}
	}

	
	private function showDirectories()
	{
		//EDIT LIST SUB FOLDERS
		$subFolders = $this->getSubFoldersFromCurrent();

		if ($this->_existDirectory)
		{
			$this->showEditlist($this->_leftCol, $subFolders, $this->_lang->Value("SUBFOLDERS"),  $this->_lang->Value("SUBFOLDERS"), FileBrowserEditListType::SUBFOLDER );
		}
		else
		{
			$this->_leftCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("DIRECTORY_DOES_NOT_EXISTS")));
		}
	}

	/**
	 * Show the Current Folder
	 *
	 */
	private function showFiles()
	{

		$show = false;
		//EDIT LIST FILES
		if (($this->_currentFolder != "") || ($this->_userType == FileBrownserUserType::ADMIN ))
		{
			$files = array();
			$tempFiles = $this->getFilesFromFolder();
			
			if ($tempFiles)
			{
				foreach ($tempFiles as $file)
					if ($this->extensionIsPermitted($this->_fileViewList, $file))
						$files[] = $file;
			}

			$show = $this->_existDirectory;
			
		}

		if ($show)
		{
			$this->showEditlist($this->_rightCol, $files, $this->_lang->Value("FILES"), $this->_lang->Value("FILES"), FileBrowserEditListType::FILE );
		}
		else
		{
			$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("DIRECTORY_EMPTY")));
		}
	}
	
	/**
	 * Show the EditList of Sub Folders or files of currenct folder
	 * 
	 * @param Array $values
	 * @param String $title
	 * @param String $field
	 * @param FileBrowserEditListType $type
	 */
	private function showEditlist($block, $values, $title, $field, $type)
	{	
		if ($this->_currentFolder != "")
		{
			$tempValues = $values;
			$values = array();
			
			if ($tempValues)
			{
				foreach ($tempValues as $value)
					$values[] = $this->getSingleName($value);
			}
		}

		$arrayDs = new ArrayDataSet($values);
		$arrayIt = $arrayDs->getIterator();		
		
		if ($type == FileBrowserEditListType::FILE)
		{
			$new = $this->getFileNew();
			$view = $this->getFileView();
			$edit = $this->getFileEdit();
			$delete = $this->getFileDelete();
		}
		else 
		{
			$new = $this->getFolderNew();
			$view = $this->getFolderView();
			$edit = $this->getFolderEdit();
			$delete = $this->getFolderDelete();
		}
		$readOnly = (!$new && !$view && !$edit && !$delete);
		
		$editlist= new XmlEditList($this->_context, $this->_currentFolder, "module:".$this->_module, $new, $view, $edit, $delete);
		$editlist->addParameter("type", $type);
		$editlist->addParameter("folder", $this->_currentFolder);
		$editlist->setDataSource($arrayIt);
		
		$editlistfield = new EditListField();
		$editlistfield->fieldData = "id";
		$editlistfield->editlistName = "#";
		$editlist->addEditListField($editlistfield);
		
		$editlistfield = new EditListField();
		$editlistfield->fieldData = "value";
		$editlistfield->editlistName = $field;
		$editlist->addEditListField($editlistfield);
		
		if ( ($type == FileBrowserEditListType::FILE) && $this->getFileUpload())
		{
			$customButton = new CustomButtons();
			$customButton->action = "uploadform";
			$customButton->enabled = true;
			$customButton->alternateText = "UPLOAD";
			$customButton->icon = "common/editlist/ic_custom.gif";
			$customButton->multiple = MultipleSelectType::NONE;
			$editlist->setCustomButton($customButton);
			$readOnly = false;
		}

		if ($readOnly)
		{
			$editlist->setReadonly();
		}
		
		$block->addXmlnukeObject($editlist);
	}
	
	/**
	 * Get the Tree of Current Folder
	 *
	 * @return Array
	 */
	private function getTreeFolder()
	{
		//get all tree folder of current before folder until them
		$slash = FileUtil::Slash();
		if ($slash == "\\") $slash = "\\\\";  // For Windows version only.
		$tempFolders = explode($slash, $this->_currentFolder);
		
		//verify the subfolders that was found in current folder
		$fullFolder = "";
		foreach ($tempFolders as $folder)
		{
			if ($fullFolder == "")
				$fullFolder = $folder; 
			else
				$fullFolder .= FileUtil::Slash() . $folder;
				
			//verify if the fullfolder is a Permitted Sub Folder
			$found = false;
			foreach ($this->getSubFoldersPermitted() as $folderPermitted)
			{
				if ($fullFolder == $folderPermitted)
				{
					$this->_currentSubFolder = $folderPermitted;
					$found = true;
					break;
				}
			}
			
			//if found the Permitted sub folder
			if ($found)
			{
				$folders = array();
			}
						
			if (!$found)
			{
				$folders[$fullFolder] = $folder;
			}
		}
		
		return $folders;		
	}
	
	/**
	 * Get All The Sub Folders of Folders
	 *
	 * @return Array
	 */
	private function getSubFoldersFromCurrent()
	{
		$directories = null;
		//try get the subfolders
		try 
		{
			//get the subfolders
			if ($this->_currentFolder != "") 
			{
				$tempDirectories = FileUtil::RetrieveSubFolders($this->_currentFolder);
			}
			else 
			{
				$tempDirectories = $this->getSubFoldersPermitted();
			}
			
			if ($tempDirectories)
			{
				foreach ($tempDirectories as $folder)
				{
					//get the last folder
					$slash = FileUtil::Slash();
					if ($slash == "\\") $slash = "\\\\"; // For Windows Only.
					$folders = explode($slash, $folder);
					foreach ($folders as $tempFolder)
					{}			
					
					//if the folder not beginner with ., get them
					if ($tempFolder[0] != ".")
						$directories[] = $folder;
				}
			}
		}
		catch (Exception $e) //if was ocurred an error
		{
			$erro = new NotFoundException($e->getMessage());
			$erro = $erro->getMessage();

			$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
			$uiAlert->addXmlnukeObject($erro);
			$this->_blockTop->addXmlnukeObject($uiAlert);
			
			$this->_existDirectory = false;			
		}		
		
		return $directories;
	}
	
	/**
	 * Verify if folder is Permitted subfolders from root folder
	 *
	 * @param String $folder
	 * @return Bool
	 */
	private function isSubFolderPermitted($folder)
	{
		if ($this->_userType == FileBrownserUserType::ADMIN )
			return true;
		
		$found = false;
		foreach ($this->_PermittedSubFolders as $PermittedFolder)
		{
			$PermittedFolder = $this->_root . FileUtil::Slash() . $PermittedFolder;
			if ($folder == $PermittedFolder)
			{
				$found = true;
				break;
			}
		}
		
		return $found;
	}
	
	/**
	 * Get All The Files of Folder
	 *
	 * @return Array
	 */
	private function getFilesFromFolder()
	{
		$files = null;
		//Debug::PrintValue("GetSubFolders: ". $this->_currentFolder);
		try 
		{
			$folder = $this->_currentFolder;
			if ($folder == "") 
			{
				if ($this->_userType == FileBrownserUserType::ADMIN ) 
				{
					$folder = $this->_context->SystemRootPath();
				}
				else 
				{
					return array();
				}
			}

			$tempFiles = FileUtil::RetrieveFilesFromFolder($folder,"");
			
			//remove the files that was blocked for user
			foreach ($tempFiles as $file)
			{
				if ($this->extensionIsPermitted($this->_fileViewList, $file))
				{
					if ($this->_currentFolder == "")
					{
						$files[] = basename($file);
					}
					else 
					{
						$files[] = $file;
					}
				}
			}
		}
		catch (Exception $e)
		{
			$erro = new NotFoundException($e->getMessage());
			$erro = $erro->getMessage();

			$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
			$uiAlert->addXmlnukeObject($erro);
			$this->_blockTop->addXmlnukeObject($uiAlert);			
		}		

		return $files;
	}	
	
	
	/**
	 * Sow a new form to create a new form
	 *
	 */
	private function formNew()
	{	
		$type = $this->_context->get("type");
		
		if ($type == FileBrowserEditListType::SUBFOLDER)
		{	
			if (($this->getFolderNew()) && ($this->_currentFolder))
			{
				$form = new XmlFormCollection($this->_context, "module:".$this->_module, $this->_lang->Value("NEWDIRECTORY"));
				$form->addXmlnukeObject(new XmlInputHidden("action","create"));
				$form->addXmlnukeObject(new XmlInputHidden("type",$this->_context->get("type")));
				$form->addXmlnukeObject(new XmlInputHidden("folder",$this->_currentFolder));
				
				$textBox = new XmlInputTextBox($this->_lang->Value("TXT_NAME"),"name","");
				$textBox->setRequired(true);
				$form->addXmlnukeObject($textBox);
				
				$button = new XmlInputButtons();
				$button->addSubmit($this->_lang->Value("TXT_CONFIRM"),"");
				$form->addXmlnukeObject($button);
				
				$this->_rightCol->addXmlnukeObject($form);
			}
			else
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("NEWFOLDERNOTPERMITTED"),true));
			}
		}
		else//FILE
		{		
			if ($this->getFileNew())
			{			
				$form = new XmlFormCollection($this->_context, "module:".$this->_module, $this->_lang->Value("NEWFILE"));
				
				$form->addXmlnukeObject(new XmlInputHidden("action","create"));
				$form->addXmlnukeObject(new XmlInputHidden("type",$this->_context->get("type")));
				$form->addXmlnukeObject(new XmlInputHidden("folder",$this->_currentFolder));
				
				$textBox = new XmlInputTextBox($this->_lang->Value("TXT_NAME"),"filename","",40);
				$textBox->setRequired(true);
				$form->addXmlnukeObject($textBox);
				
				$textMemo = new XmlInputMemo($this->_lang->Value("LABEL_CONTENT"), "filecontent", "");
				$textMemo->setSize(70,20);
				$form->addXmlnukeObject($textMemo);
				
				$button = new XmlInputButtons();
				$button->addSubmit($this->_lang->Value("TXT_CONFIRM"),"");
				$form->addXmlnukeObject($button);
				
				$this->_rightCol->addXmlnukeObject($form);
			}
			else
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("NEWFILENOTPERMITTED"),true));
			}
		}
	}
	
	/**
	 * Show the Form Upload
	 *
	 */
	private function formUpload()
	{	
		if ($this->getFileUpload())
		{	
			$form = new XmlFormCollection($this->_context, "module:".$this->_module, $this->_lang->Value("FORM_UPLOAD"));
				
			$form->addXmlnukeObject(new XmlInputHidden("action","uploadfile"));
			$form->addXmlnukeObject(new XmlInputHidden("folder", $this->_currentFolder));

			for($i=1;$i<=5;$i++)
			{
				$file = new XmlInputFile($this->_lang->Value("TXT_FILE", $i),"form_file$i");
				$form->addXmlnukeObject($file);
			}
				
			$button = new XmlInputButtons();
			$button->addSubmit($this->_lang->Value("TXT_SUBMIT"),"");
			$form->addXmlnukeObject($button);
				
			$this->_rightCol->addXmlnukeObject($form);
		}
		else
		{
			$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("UPLOADNOTPERMITTED"),true));
		}
	}
	
	/**
	 * Upload a File
	 *
	 */
	private function uploadFile()
	{	
		$dir = $this->_currentFolder.FileUtil::Slash();
			
		$fileProcessor = new UploadFilenameProcessor("");
		$fileProcessor->setFilenameLocation(ForceFilenameLocation::DefinePath, $this->_context->SystemRootPath() . $dir);

		$fileList = $this->_context->getUploadFileNames();
		//Debug::PrintValue($fileList);

		foreach ($fileList as $field=>$file)
		{
			if ($file == "")
			{
				continue;
			}
			elseif ($this->extensionIsPermitted($this->_fileUploadList, $file))
			{
				$result = $this->_context->processUpload($fileProcessor, false, $field);
				if (is_array($result) && (sizeof($result)) >= 1)
				{
					$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("FILEUPLOAD_OK", basename(file)), true));
				}
				else
				{
					$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("FILEUPLOAD_ERR", basename(file)), true));
				}
			}
			else
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("EXTENSIONNOTPERMITTED", basename($file)),true));
			}
		}
	}	
	
	/**
	 * Create a new subfolder or a new file
	 *
	 */
	private function createNew()
	{
		$type = $this->_context->get("type");
		$id = $this->_context->get("valueid");
		$name = $this->_context->get("name");
		
		if ($type == FileBrowserEditListType::SUBFOLDER ) //SUBFOLDERS
		{
			$folder = $this->_currentFolder.FileUtil::Slash().$name;
			try 
			{
				FileUtil::ForceDirectories($folder);
			}
			catch (Exception $e)
			{
				$erro = new NotFoundException($e->getMessage());
				$erro = $erro->getMessage();

				$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
				$uiAlert->addXmlnukeObject($erro);
				$this->_blockTop->addXmlnukeObject($uiAlert);
			}
		}
		else //CREATE FILE
		{
			$filename = $this->_context->get("filename");
			$filecontent = $this->_context->get("filecontent");
			$filePath = $this->_currentFolder . FileUtil::Slash();
			
			try 
			{
				FileUtil::QuickFileWrite($filePath.$filename, $filecontent);
			}
			catch (Exception $e)
			{
				$erro = new NotFoundException($e->getMessage());
				$erro = $erro->getMessage();

				$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
				$uiAlert->addXmlnukeObject($erro);
				$this->_blockTop->addXmlnukeObject($uiAlert);
			}
		}
	}
	
	/**
	 * Show the Form Edit
	 *
	 */
	private function formEdit()
	{	
		$id = $this->_context->get("valueid");
		if (!$id)
			$id = 0;
		
		$type = $this->_context->get("type");
		
		if ($type == FileBrowserEditListType::SUBFOLDER)
		{
			if (($this->getFolderEdit()) && ($this->_currentFolder)) 
			{
				$form = new XmlFormCollection($this->_context, "module:".$this->_module, $this->_lang->Value("FOLDEREDIT"));
				
				$form->addXmlnukeObject(new XmlInputHidden("action","confirmedit"));
				$form->addXmlnukeObject(new XmlInputHidden("type", $type));
				$form->addXmlnukeObject(new XmlInputHidden("folder", $this->_currentFolder));
				
				$subfolders = $this->getSubFoldersFromCurrent();
				$subfolder = $this->getSingleName($subfolders[$id]);
				
				$textBox = new XmlInputTextBox($this->_lang->Value("NEWNAME"),"new_name", $subfolder);
				$textBox->setRequired(true);
				$form->addXmlnukeObject($textBox);
	
				$form->addXmlnukeObject(new XmlInputHidden("old_name", $subfolder));			
				
				$button = new XmlInputButtons();
				$button->addSubmit($this->_lang->Value("TXT_UPDATE"),"");
				$form->addXmlnukeObject($button);
				
				$this->_rightCol->addXmlnukeObject($form);
			}
			else
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("EDITFOLDERNOTPERMITTED"),true));
			}		
		}
		else
		{
			if ($this->getFileEdit())
			{
				$files = $this->getFilesFromFolder();
				
				if ($id == 0)
					$filesrc = $files[0];
				else
					$filesrc = $files[$id];
					
				if ($this->extensionIsPermitted($this->_fileEditList, $filesrc))
				{
					$form = new XmlFormCollection($this->_context, "module:".$this->_module, $this->_lang->Value("EDITFILE"));
					
					$form->addXmlnukeObject(new XmlInputHidden("action","confirmedit"));
					$form->addXmlnukeObject(new XmlInputHidden("type",$this->_context->get("type")));
					$form->addXmlnukeObject(new XmlInputHidden("folder",$this->_currentFolder));
					
					$filename = $this->getSingleName($filesrc);
					$textBox = new XmlInputTextBox($this->_lang->Value("TXT_NAME"),"new_name",$filename,40);
					$textBox->setRequired(true);
					$form->addXmlnukeObject($textBox);
					
					$form->addXmlnukeObject(new XmlInputHidden("old_name", $filename));
					
					try 
					{
						$filecontent =  FileUtil::QuickFileRead($filesrc);
					}
					catch (Exception $e)
					{
						$erro = new NotFoundException($e->getMessage());
						$erro = $erro->getMessage();

						$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
						$uiAlert->addXmlnukeObject($erro);
						$this->_blockTop->addXmlnukeObject($uiAlert);
					}
					
					$textMemo = new XmlInputMemo("", "filecontent", $filecontent);
					$textMemo->setSize(110,20);
					$form->addXmlnukeObject($textMemo);
					
					$button = new XmlInputButtons();
					$button->addSubmit($this->_lang->Value("UPDATE"),"");
					$form->addXmlnukeObject($button);
					
					$this->_rightCol->addXmlnukeObject($form);
				}
				else
				{
					$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("EXTENSIONEDITNOTPERMITTED"),true));
				}
			}
			else
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("EDITFILENOTPERMITTED"),true));
			}			
		}
	}
	
	/**
	 * Show the confirm edit
	 *
	 */
	private function confirmEdit()
	{			
		$type = $this->_context->get("type");
		
		$old_name = $this->_currentFolder . FileUtil::Slash() . $this->_context->get("old_name");
		$old_name = $this->realPathName($old_name);

		$new_name = dirname($old_name) . FileUtil::Slash() . $this->_context->get("new_name");

		$filecontent = $this->_context->get("filecontent");	
		
		try 
		{
			if ($type == FileBrowserEditListType::SUBFOLDER) //SUBFOLDER
			{
				FileUtil::RenameDirectory($old_name, $new_name);			
			}
			else //FILE
			{
				if ($new_name != $old_name)
				{
					FileUtil::RenameFile($old_name, $new_name);
				}
				FileUtil::QuickFileWrite($new_name, $filecontent);
			}
		}
		catch (Exception $e)
		{
			$erro = new NotFoundException($e->getMessage());
			$erro = $erro->getMessage();

			$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
			$uiAlert->addXmlnukeObject($erro);
			$this->_blockTop->addXmlnukeObject($uiAlert);
		}	
	}
	
	/**
	 * View the SubFolder
	 *
	 */
	private function view()
	{	
		$id = $this->_context->get("valueid");
		$type = $this->_context->get("type");
		
		
		if ($type == FileBrowserEditListType::SUBFOLDER )
		{
			if ($this->getFolderView()) 
			{
				$dir = $this->getSubFoldersFromCurrent();
				
				if ($id == 0)
					$this->_currentFolder = $dir[0];
				else
					$this->_currentFolder = $dir[$id];
			}
			else
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("VIEWFOLDERNOTPERMITTED"),true));
			}
		}
		else
		{		
			if ($this->getFileView())
			{		
				$files = $this->getFilesFromFolder();
				
				if ($id == 0)
					$filesrc = $files[0];
				else
					$filesrc = $files[$id];
					
				$filename = $this->getSingleName($filesrc);
				
				$textBox = new XmlInputTextBox($this->_lang->Value("TXT_NAME"),"filename",$filename,60);
				$textBox->setReadOnly(true);
				$this->_rightCol->addXmlnukeObject($textBox);
				$this->_rightCol->addXmlnukeObject(new XmlnukeBreakLine());

				$img = false;
				$ext = $this->getExtension($filename);
				switch (strtolower($ext))
				{
					case "jpg":
					case "bmp":
					case "png":
					case "gif":
					case "jpeg":
						//Debug::PrintValue("sim");
						$img = true;
						break;
				}
				
				
				if ($img)
				{
					$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("IMAGE").":",false,false,false,true));
					$img = new XmlnukeImage(str_replace("\\", "/", $this->_currentFolder.FileUtil::Slash().$filename) );
					$this->_rightCol->addXmlnukeObject($img);
				}
				else 
				{
					try 
					{	
						ini_set("memory_limit","16M");
						$filecontent =  FileUtil::QuickFileRead($filesrc);
					}
					catch (Exception $e)
					{
						$erro = new NotFoundException($e->getMessage());
						$erro = $erro->getMessage();

						$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
						$uiAlert->addXmlnukeObject($erro);
						$this->_blockTop->addXmlnukeObject($uiAlert);
					}
									
					$textMemo = new XmlInputMemo($this->_lang->Value("FILE"), "filecontent", $filecontent);
					$textMemo->setSize(110,20);
					$this->_rightCol->addXmlnukeObject($textMemo);
				}
			}
			else 
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("VIEWFILENOTPERMITTED"),true));
			}
		}
	}
	
	/**
	 * Get single name of folder or file
	 *
	 * @param String $src_name
	 * @return String
	 */
	private function getSingleName($src_name)
	{
		$slash = FileUtil::Slash();
		if ($slash == "\\") $slash = "\\\\"; // For Windows Only.
		$srcs = explode($slash, $src_name);
		
		foreach ($srcs as $name)
		{ }
		
		return $name;
	}	
	
	/**
	 * Get extension of filename
	 *
	 * @param String $filename
	 * @return String
	 */
	private function getExtension($filename)
	{
		$srcs = explode(".", $filename);
		
		foreach ($srcs as $extension)
		{ }
		
		return $extension;
	}		
	
	/**
	 * Delete a Folder or File
	 *
	 */
	private function delete()
	{
		$type = $this->_context->get("type");
		$id = $this->_context->get("valueid");
		
		if (!$id)
			$id = 0;
		
		if ($type == FileBrowserEditListType::SUBFOLDER) //SUBFOLDERS
		{
			if (($this->getFolderDelete()) && ($this->_currentFolder))
			{
				$folders = $this->getSubFoldersFromCurrent();
				try 
				{
					FileUtil::ForceRemoveDirectories($folders[$id]);
				}
				catch (Exception $e)
				{
					$erro = new NotFoundException($e->getMessage());
					$erro = $erro->getMessage();

					$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
					$uiAlert->addXmlnukeObject($erro);
					$this->_blockTop->addXmlnukeObject($uiAlert);
				}
			}
			else
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("DELETEFOLDERNOTPERMITTED"),true));
			}
		}
		else //FILE
		{
			if ($this->getFileDelete())
			{
				$files = $this->getFilesFromFolder();
				try 
				{
					FileUtil::DeleteFileString($files[$id]);
				}
				catch (Exception $e)
				{
					$erro = new NotFoundException($e->getMessage());
					$erro = $erro->getMessage();

					$uiAlert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog);
					$uiAlert->addXmlnukeObject($erro);
					$this->_blockTop->addXmlnukeObject($uiAlert);
				}
			}
			else
			{
				$this->_rightCol->addXmlnukeObject(new XmlnukeText($this->_lang->Value("DELETEFILENOTPERMITTED"),true));
			}
		}
	}
	
	/**
	 * Verfify if extension is a Permitted extension
	 *
	 * @param Array $PermittedExtensionList
	 * @param String $filename
	 * @return Bool
	 */
	private function extensionIsPermitted($PermittedExtensionList, $filename)
	{
		if ($this->_userType == FileBrownserUserType::ADMIN )
			return true;

		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		//Debug::PrintValue("Ext: ".$ext);

		$valid = "-" . implode("-", $PermittedExtensionList) . "-";

		return (preg_match("/-\.?" . $ext . "-/", $valid));
	}
	
	
	
	//METHODS SETS AND GETS
	
	//CREATE NEW FOLDER
	/**
	 * Set if is Permitted create a new folder
	 *
	 * @param Bool $folderNew
	 */
	public function setFolderNew($folderNew)
	{
		$this->_folderNew = ($folderNew == "true") ? true : false;	
	}
	
	/**
	 * Get if is Permitted create a new folder
	 *
	 * @return Bool
	 */
	public function getFolderNew()
	{
		return $this->_folderNew || ($this->_userType == FileBrownserUserType::ADMIN);
	}

	
	//VIEW FOLDERS
	/**
	 * Set if is Permitted View a folder
	 *
	 * @param Bool $folderView
	 */
	public function setFolderView($folderView)
	{
		$this->_folderView = ($folderView == "true") ? true : false;	
	}
	
	/**
	 * Get if is Permitted View a folder
	 *
	 * @return Bool
	 */
	public function getFolderView()
	{
		return $this->_folderView || ($this->_userType == FileBrownserUserType::ADMIN);
	}
	
	//Edit FOLDERS
	/**
	 * Set if is Permitted Edit a folder
	 *
	 * @param Bool $folderEdit
	 */
	public function setFolderEdit($folderEdit)
	{
		$this->_folderEdit = ($folderEdit == "true") ?  true : false;
	}
	
	/**
	 * Get if is Permitted Edit a folder
	 *
	 * @return Bool
	 */
	public function getFolderEdit()
	{
		return $this->_folderEdit || ($this->_userType == FileBrownserUserType::ADMIN);
	}
	
	//Delete folderS
	/**
	 * Set if is Permitted Delete a folder
	 *
	 * @param Bool $folderDelete
	 */
	public function setFolderDelete($folderDelete)
	{
		$this->_folderDelete = ($folderDelete == "true") ? true : false;
	}
	
	/**
	 * Get if is Permitted Delete a folder
	 *
	 * @return Bool
	 */
	public function getFolderDelete()
	{
		return $this->_folderDelete || ($this->_userType == FileBrownserUserType::ADMIN);
	}	
	
	
	//CREATE NEW File
	/**
	 * Set if is Permitted create a new File
	 *
	 * @param Bool $fileNew
	 */
	public function setFileNew($fileNew)
	{
		$this->_fileNew = ($fileNew == "true") ?  true : false;	
	}
	
	/**
	 * Get if is Permitted create a new file
	 *
	 * @return Bool
	 */
	public function getFileNew()
	{
		return $this->_fileNew || ($this->_userType == FileBrownserUserType::ADMIN);
	}

	
	//VIEW FileS
	/**
	 * Set if is Permitted View a File
	 *
	 * @param Bool $fileView
	 */
	public function setFileView($fileView)
	{
		$this->_fileView = ($fileView == "true") ? true : false;
	}
	
	/**
	 * Get if is Permitted View a File
	 *
	 * @return Bool
	 */
	public function getFileView()
	{
		return $this->_fileView || ($this->_userType == FileBrownserUserType::ADMIN);
	}
	
	//Edit FileS
	/**
	 * Set if is Permitted Edit a File
	 *
	 * @param Bool $fileEdit
	 */
	public function setFileEdit($fileEdit)
	{
		$this->_fileEdit = ($fileEdit == "true") ?  true : false;
	}
	
	/**
	 * Get if is Permitted Edit a File
	 *
	 * @return Bool
	 */
	public function getFileEdit()
	{
		return $this->_fileEdit || ($this->_userType == FileBrownserUserType::ADMIN);
	}
	
	//Delete FileS
	/**
	 * Set if is Permitted Delete a File
	 *
	 * @param Bool $FileDelete
	 */
	public function setFileDelete($fileDelete)
	{
		$this->_fileDelete = ($fileDelete == "true") ? true : false;
	}
	
	/**
	 * Get if is Permitted Delete a File
	 *
	 * @return Bool
	 */
	public function getFileDelete()
	{
		return $this->_fileDelete || ($this->_userType == FileBrownserUserType::ADMIN);
	}	
	
	//Upload FileS
	/**
	 * Set if is Permitted upload a File
	 *
	 * @param Bool $fileUpload
	 */
	public function setFileUpload($fileUpload)
	{
		$this->_fileUpload = ($fileUpload == "true") ?  true : false;
	}
	
	/**
	 * Get if is Permitted Upload a File
	 *
	 * @return Bool
	 */
	public function getFileUpload()
	{
		return $this->_fileUpload || ($this->_userType == FileBrownserUserType::ADMIN);
	}
	
	
	
	//Arrays

	//SubFolders Permitted
	/**
	 * Set teh subFolders Permitted in root folder
	 *
	 * @param Array $subFoldersPermitted
	 */
	public function setSubFoldersPermitted($subFoldersPermitted = array())
	{
		$this->_subFoldersPermitted = $subFoldersPermitted;
	}
	
	/**
	 * Get the subfolders Permitted in root folder
	 *
	 * @return Array
	 */
	public function getSubFoldersPermitted()
	{
		if ($this->_userType == FileBrownserUserType::ADMIN)
		{
			$folderResp = array();
			
			$folders  = FileUtil::RetrieveSubFolders($this->_context->SystemRootPath());
			if (!is_null($folders))
			{
				foreach ($folders as $folder)
				{
					$folderResp[] = basename($folder);
				}
			}
			return $folderResp;
		}
		else 
		{
			return $this->_subFoldersPermitted;
		}
	}
	
	//File New List
	/**
	 * Set if is Permitted Delete a File
	 *
	 * @param Array $FileDelete
	 */
	public function setFileNewList($fileNewList = array())
	{
		$this->_fileNewList = $fileNewList;
	}
	
	/**
	 * Get if is Permitted Delete a File
	 *
	 * @return Array
	 */
	public function getFileNewList()
	{
		return $this->_fileNewList;
	}
	
	
	//File New List	
	/**
	 * Set if is Permitted Delete a File
	 *
	 * @param Array $FileDelete
	 */
	public function setFileViewList($fileViewList = array())
	{
		$this->_fileViewList = $fileViewList;
	}
	
	/**
	 * Get if is Permitted Delete a File
	 *
	 * @return Array
	 */
	public function getFileViewList()
	{
		return $this->_fileViewList;
	}
	
	//File Edit List	
	/**
	 * Set if is Permitted Delete a File
	 *
	 * @param Array $FileDelete
	 */
	public function setFileEditList($fileEditList = array())
	{
		$this->_fileEditList = $fileEditList;
	}
	
	/**
	 * Get if is Permitted Delete a File
	 *
	 * @return Array
	 */
	public function getFileEditList()
	{
		return $this->_fileEditList;
	}	

	//File Delete List	
	/**
	 * Set if is Permitted Delete a File
	 *
	 * @param Array $FileDelete
	 */
	public function setFileDeleteList($fileDeleteList = array())
	{
		$this->_fileDeleteList = $fileDeleteList;
	}
	
	/**
	 * Get if is Permitted Delete a File
	 *
	 * @return Array
	 */
	public function getFileDeleteList()
	{
		return $this->_fileDeleteList;
	}		

	//File Upload List	
	/**
	 * Set if is Permitted Upload a File
	 *
	 * @param Array $FileUpload
	 */
	public function setFileUploadList($fileUploadList = array())
	{
		$this->_fileUploadList = $fileUploadList;
	}
	
	/**
	 * Get if is Permitted Upload a File
	 *
	 * @return Array
	 */
	public function getFileUploadList()
	{
		return $this->_fileUploadList;
	}
	
	//File Max Upload	
	/**
	 * Set the max size for upload file
	 *
	 * @param Int $fileMaxUpload
	 */
	public function setFileMaxUpload($fileMaxUpload = 0)
	{
		$this->_fileMaxUpload = $fileMaxUpload;
	}
	
	/**
	 * Get the max size for upload file
	 *
	 * @return Int
	 */
	public function getFileMaxUpload()
	{
		return $this->_fileMaxUpload;
	}
	
	/**
	 * Get the real path name from a specified PATH
	 *
	 * @param string $path
	 */
	private function realPathName($path)
	{
		return realpath($path);
	}
}

?>