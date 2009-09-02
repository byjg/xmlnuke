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

class FileBrownserUserType
{
	const ADMIN = 1;
	const USER = 2;
}


class FileBrowserEditListType
{
	const SUBFOLDER = 1;
	const FILE = 2;
}

class XmlFileBrowser extends XmlnukeDocumentObject
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
	protected $_block;
	
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
	public function XmlFileBrowser($root, $action, $context)
	{
		parent::XmlnukeDocumentObject();

		$this->_context = $context;
		$this->_currentFolder = $root;
		$this->_action = $action;
		$this->_module = $context->ContextValue("module");

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
		
		$this->_lang = LanguageFactory::GetLanguageCollection($this->_context, LanguageFileTypes::OBJECT, __FILE__);
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
		$this->_block = new XmlnukeSpanCollection();
		
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
		}

		$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
		$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
		
		$this->showFolder();
		
		$this->_block->generateObject($current);
		
		//Debug::PrintValue($this->_currentFolder);
	}
	
	
	/**
	 * Show the Current Folder
	 *
	 */
	private function showFolder()
	{	
		//ROOT FOLDER
		$anchor = new XmlAnchorCollection("module:".$this->_module);
		$anchor->addXmlnukeObject(new XmlnukeText("Root"));
		$this->_block->addXmlnukeObject($anchor);		
		
		if ($this->_currentFolder != "")
			$this->_block->addXmlnukeObject(new XmlnukeText(" / "));				
			
		//TREE FOLDERS
		$treeFolders = $this->getTreeFolder();
		
		//CURRENT SUB FOLDER
		$anchor = new XmlAnchorCollection("module:".$this->_module."&folder=".$this->_currentSubFolder);
		$anchor->addXmlnukeObject(new XmlnukeText($this->_currentSubFolder));
		$this->_block->addXmlnukeObject($anchor);		
		
		if ($this->_currentFolder != "")
			$this->_block->addXmlnukeObject(new XmlnukeText(" / "));
		
		$fullFolder = "";
		foreach ($treeFolders as $folder)
		{
			if ($fullFolder == "")
				$fullFolder =  $this->_currentSubFolder . FileUtil::Slash() . $folder;
			else
				$fullFolder .= FileUtil::Slash().$folder; 
				
			$anchor = new XmlAnchorCollection("module:".$this->_module."&folder=".$fullFolder);
			$anchor->addXmlnukeObject(new XmlnukeText($folder));
			$this->_block->addXmlnukeObject($anchor);
			$this->_block->addXmlnukeObject(new XmlnukeText(" ".FileUtil::Slash()." "));
		}
				
		$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
		$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
		

		//EDIT LIST SUB FOLDERS
		$subFolders = $this->getSubFoldersFromCurrent();	
			
		if ($this->_existDirectory)
			$this->showEditlist($subFolders, $this->_lang->Value("SUBFOLDERS"),  $this->_lang->Value("SUBFOLDERS"), FileBrowserEditListType::SUBFOLDER );
		
		$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
		$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
		
		
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
			
			if ($this->_existDirectory)					
				$this->showEditlist($files, $this->_lang->Value("FILES"), $this->_lang->Value("FILES"), FileBrowserEditListType::FILE );
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
	private function showEditlist($values, $title, $field, $type)
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
			$url = new XmlnukeManageUrl(URLTYPE::MODULE , "admin.FileManagement");
			$url->addParam("type", $type);
			$url->addParam("folder", $this->_currentFolder);
			$customButton->url = htmlentities($url->getUrlFull($this->_context));
			$customButton->icon = "common/editlist/ic_custom.gif";
			$customButton->multiple = MultipleSelectType::NONE;
			$editlist->setCustomButton($customButton);
			$readOnly = false;
		}

		if ($readOnly)
		{
			$editlist->setReadonly();
		}
		
		$this->_block->addXmlnukeObject($editlist);
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
			$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
			
			$this->_existDirectory = false;
			
			$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
			$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
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
			$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
			
			$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
			$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
		}		

		return $files;
	}	
	
	
	/**
	 * Sow a new form to create a new form
	 *
	 */
	private function formNew()
	{	
		$type = $this->_context->ContextValue("type");
		
		if ($type == FileBrowserEditListType::SUBFOLDER)
		{	
			if (($this->getFolderNew()) && ($this->_currentFolder))
			{
				$form = new XmlFormCollection($this->_context, "module:".$this->_module, $this->_lang->Value("NEWDIRECTORY"));
				$form->addXmlnukeObject(new XmlInputHidden("action","create"));
				$form->addXmlnukeObject(new XmlInputHidden("type",$this->_context->ContextValue("type")));
				$form->addXmlnukeObject(new XmlInputHidden("folder",$this->_currentFolder));
				
				$textBox = new XmlInputTextBox($this->_lang->Value("TXT_NAME"),"name","");
				$textBox->setRequired(true);
				$form->addXmlnukeObject($textBox);
				
				$button = new XmlInputButtons();
				$button->addSubmit($this->_lang->Value("CONFIRM"),"");
				$form->addXmlnukeObject($button);
				
				$this->_block->addXmlnukeObject($form);
			}
			else
			{
				$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("NEWFOLDERNOTPERMITTED"),true));
			}
		}
		else//FILE
		{		
			if ($this->getFileNew())
			{			
				$form = new XmlFormCollection($this->_context, "module:".$this->_module, $this->_lang->Value("NEWFILE"));
				
				$form->addXmlnukeObject(new XmlInputHidden("action","create"));
				$form->addXmlnukeObject(new XmlInputHidden("type",$this->_context->ContextValue("type")));
				$form->addXmlnukeObject(new XmlInputHidden("folder",$this->_currentFolder));
				
				$textBox = new XmlInputTextBox($this->_lang->Value("TXT_NAME"),"filename","",40);
				$textBox->setRequired(true);
				$form->addXmlnukeObject($textBox);
				
				$textMemo = new XmlInputMemo($this->_lang->Value("FILE"), "filecontent", "");
				$textMemo->setSize(70,20);
				$form->addXmlnukeObject($textMemo);
				
				$button = new XmlInputButtons();
				$button->addSubmit($this->_lang->Value("CONFIRM"),"");
				$form->addXmlnukeObject($button);
				
				$this->_block->addXmlnukeObject($form);
			}
			else
			{
				$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("NEWFILENOTPERMITTED"),true));				
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
			$form = new XmlFormCollection($this->_context, "module:".$this->_module, $this->_lang->Value("UPLOAD"));
				
			$form->addXmlnukeObject(new XmlInputHidden("action","uploadfile"));
			$form->addXmlnukeObject(new XmlInputHidden("folder", $this->_currentFolder));
				
			$file = new XmlInputFile($this->_lang->Value("FILE"),"form_file");
			$form->addXmlnukeObject($file);
				
			$button = new XmlInputButtons();
			$button->addSubmit($this->_lang->Value("SEND"),"");
			$form->addXmlnukeObject($button);
				
			$this->_block->addXmlnukeObject($form);	
		}
		else
		{
			$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("UPLOADNOTPERMITTED"),true));				
		}
	}
	
	/**
	 * Upload a File
	 *
	 */
	private function uploadFile()
	{	
		$dir = $this->_currentFolder.FileUtil::Slash();
			
		$fileProcessor = new UploadFilenameProcessor("", $this->_context);
		$fileProcessor->setFilenameLocation(ForceFilenameLocation::DefinePath, $this->_context->SystemRootPath() . $dir);
			
		$result = $this->_context->processUpload($fileProcessor, false);
		
		//if ($this->extensionIsPermitted($this->_fileUploadList, $filename))
		if (is_array($result) && (sizeof($result)) >= 1)
		{
			$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("FILE")." ".$result[0]." ".$this->_lang->Value("SENDED")));
		}
		else
		{
			$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("EXTENSIONNOTPERMITTED"),true));
		}
	}	
	
	/**
	 * Create a new subfolder or a new file
	 *
	 */
	private function createNew()
	{
		$type = $this->_context->ContextValue("type");
		$id = $this->_context->ContextValue("valueid");
		$name = $this->_context->ContextValue("name");
		
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
				$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
				
				$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
				$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
			}
		}
		else //CREATE FILE
		{
			$filename = $this->_context->ContextValue("filename");
			$filecontent = $this->_context->ContextValue("filecontent");
			$filePath = $this->_currentFolder . FileUtil::Slash();
			
			try 
			{
				FileUtil::QuickFileWrite($filePath.$filename, $filecontent);
			}
			catch (Exception $e)
			{
				$erro = new NotFoundException($e->getMessage());
				$erro = $erro->getMessage();
				$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
				
				$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
				$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
			}
		}
	}
	
	/**
	 * Show the Form Edit
	 *
	 */
	private function formEdit()
	{	
		$id = $this->_context->ContextValue("valueid");
		if (!$id)
			$id = 0;
		
		$type = $this->_context->ContextValue("type");
		
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
				
				$this->_block->addXmlnukeObject($form);
			}
			else
			{
				$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("EDITFOLDERNOTPERMITTED"),true));				
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
					$form->addXmlnukeObject(new XmlInputHidden("type",$this->_context->ContextValue("type")));
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
						$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
						
						$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
						$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
					}
					
					$textMemo = new XmlInputMemo("", "filecontent", $filecontent);
					$textMemo->setSize(110,20);
					$form->addXmlnukeObject($textMemo);
					
					$button = new XmlInputButtons();
					$button->addSubmit($this->_lang->Value("UPDATE"),"");
					$form->addXmlnukeObject($button);
					
					$this->_block->addXmlnukeObject($form);
				}
				else
				{
					$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("EXTENSIONEDITNOTPERMITTED"),true));				
				}
			}
			else
			{
				$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("EDITFILENOTPERMITTED"),true));				
			}			
		}
	}
	
	/**
	 * Show the confirm edit
	 *
	 */
	private function confirmEdit()
	{			
		$type = $this->_context->ContextValue("type");
		
		$old_name = $this->_currentFolder . FileUtil::Slash() . $this->_context->ContextValue("old_name");
		$old_name = $this->realPathName($old_name);

		$new_name = dirname($old_name) . FileUtil::Slash() . $this->_context->ContextValue("new_name");

		$filecontent = $this->_context->ContextValue("filecontent");	
		
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
			$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
				
			$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
			$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
		}	
	}
	
	/**
	 * View the SubFolder
	 *
	 */
	private function view()
	{	
		$id = $this->_context->ContextValue("valueid");
		$type = $this->_context->ContextValue("type");
		
		
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
				$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("VIEWFOLDERNOTPERMITTED"),true));				
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
				$this->_block->addXmlnukeObject($textBox);				
				$this->_block->addXmlnukeObject(new XmlnukeBreakLine());			

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
					$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("IMAGE").":",false,false,false,true));
					$img = new XmlnukeImage(str_replace("\\", "/", $this->_currentFolder.FileUtil::Slash().$filename) );
					$this->_block->addXmlnukeObject($img);	
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
						$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
						
						$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
						$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
					}
									
					$textMemo = new XmlInputMemo($this->_lang->Value("FILE"), "filecontent", $filecontent);
					$textMemo->setSize(110,20);
					$this->_block->addXmlnukeObject($textMemo);
				}
			}
			else 
			{
				$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("VIEWFILENOTPERMITTED"),true));
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
		$type = $this->_context->ContextValue("type");
		$id = $this->_context->ContextValue("valueid");
		
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
					$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
					
					$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
					$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
				}
			}
			else
			{
				$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("DELETEFOLDERNOTPERMITTED"),true));					
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
					$this->_block->addXmlnukeObject(new XmlnukeText($erro,true));
					
					$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
					$this->_block->addXmlnukeObject(new XmlnukeBreakLine());
				}
			}
			else
			{
				$this->_block->addXmlnukeObject(new XmlnukeText($this->_lang->Value("DELETEFILENOTPERMITTED"),true));				
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
		
		$tempNames = explode(".", $filename);
		
		foreach ($tempNames as $name)
		{}
		$ext = ".".$name;
			
		//Debug::PrintValue("Ext: ".$ext);
		
		foreach ($PermittedExtensionList as $extension)
		{
			//Debug::PrintValue("Extension: ".$extension);
			//Debug::PrintValue("= ".$ext);
			if ($extension == $ext)
				return true;
		}
		
				//Debug::PrintValue("False");
		return false;	
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