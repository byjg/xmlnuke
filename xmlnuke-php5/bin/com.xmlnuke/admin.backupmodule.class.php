<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  Acknowledgments to: Thiago Bellandi
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

require_once(PHPXMLNUKEDIR . "bin/com.xmlnuke/classes.tar.class.php");

class BackupModule extends NewBaseAdminModule
{
	const OP_EDITPROJECT = "prj";
	const OP_MANAGEBACKUP = "mbkp";
	const OP_CREATEBACKUP = "cbkp";

	const AC_NEW = "new";
	const AC_NEW_CONF = "newconf";
	const AC_EDIT = "edit";
	const AC_EDIT_CONF = "editconf";
	const AC_DELETE = "delete";
	const AC_DELETE_CONF = "deleteconf";
	const AC_VIEW = "view";
	const AC_VIEW_CONF = "viewconf";
	const AC_UPLOADFILE = "upload";
	const AC_DOWNLOAD = "dnld";

	const AC_CREATEBACKUP = "confcreate";

	const LINESECTIONPATTERN = '/^\[\s*([\w\n]*)\s*\:\s*([\w\n]*)\s*\:\s*([\w\n]*)\s*\:\s*(([\w\n\s]*\s*\=\s*[\w\n\s]*\s*\;?\s*)+)\s*\]$/';
	const LINEVALUEPATTERN = '/^([\w\n\s]*\s*\=\s*[\w\n\s]*)$/';

	/**
	 * @var Array
	 */
	protected $_fileList;


	public function BackupModule()
	{
	}

	/**
	 * Override. Function use cache or not.
	 *
	 * @return bool
	 */
	public function useCache()
	{
		return false;
	}

	/**
	 * Override. Valid Access Level.
	 *
	 * @return AccessLevel
	 */
	public function getAccessLevel()
    {
          return AccessLevel::CurrentSiteAndRole;
    }

    /**
     * Override. Valid roles
     *
     * @return string
     */
    public function getRole()
    {
           return "MANAGER";
    }


    /**
     * Main Language Collection
     *
     * @var LanguageCollection
     */
    protected $_myWords;

    /**
     * Create Page Method
     *
     * @return PageXml
     */
	public function CreatePage()
	{
		parent::CreatePage(); // Doesnt necessary get PX, because PX is protected!

		$this->_myWords = $this->WordCollection();

		$this->setHelp($this->_myWords->Value("HELPMODULE"));

		$this->defaultXmlnukeDocument->addMenuItem("admin:backupmodule?op=" . self::OP_EDITPROJECT, $this->_myWords->Value("MENU_PROJECTS"),"");
		$this->defaultXmlnukeDocument->addMenuItem("admin:backupmodule?op=" . self::OP_MANAGEBACKUP, $this->_myWords->Value("MENU_BACKUP"),"");
		$this->defaultXmlnukeDocument->addMenuItem("admin:backupmodule", $this->_myWords->Value("MENU_HOME"),"");

		$op = $this->_context->ContextValue("op");

		//Debug::PrintValue($this->_action);
		//Debug::PrintValue($op);

		switch ($op)
		{
			case self::OP_EDITPROJECT :
				$this->EditProjects();
				break;

			case self::OP_MANAGEBACKUP :
				$this->ManageBackups();
				break;

			case self::OP_CREATEBACKUP :
				$this->createProjectBackup();
				break;

			default:
				$this->ExplainModule();
				break;
		}

		return $this->defaultXmlnukeDocument->generatePage();
	}

	/**
	 * Module to say a "Welcome Message"
	 *
	 */
	protected function ExplainModule()
	{
		$block = new XmlBlockCollection($this->_myWords->Value("MODULETITLE"), BlockPosition::Center );

		//show options to select
		$paragraph = new XmlParagraphCollection();
		$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("EXPLAINMODULE")));
		$block->addXmlnukeObject($paragraph);

		$paragraph = new XmlParagraphCollection();
		$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("EXPLAINMODULE_SELECT")));
		$block->addXmlnukeObject($paragraph);

		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
	}

	/**
	 * List the avaliable projects and enable create a TAR.GZ file with the selected options
	 * Accessible only by $op = OP_EDITPROJECT
	 *
	 */
	protected function EditProjects()
	{
		$projectName = "";
		$projectList = $this->getProjectList();
		$readonly = false;

		switch ($this->_action)
		{
			case self::AC_EDIT_CONF :
				$projectName = $this->_context->ContextValue("projname");
				$this->createProject($projectName, $this->_context->ContextValue("projdir"), $this->_context->ContextValue("projfiles"), $this->_context->ContextValue("projsetup"));

			case self::AC_VIEW :
				$readonly = ($this->_action == self::AC_VIEW) ;

			case self::AC_EDIT :
				if ($projectName == "")
					$projectName = $projectList[$this->_context->ContextValue("valueid")];

			case self::AC_NEW :
				$block = new XmlBlockCollection($this->_myWords->Value("BLOCK_EDITPROJECT", $projectName), BlockPosition::Center );
				$form = new XmlFormCollection($this->_context, "admin:backupmodule?action=" . self::AC_EDIT_CONF, $this->_myWords->Value("FORM_EDITPROJECT"));
				$form->addXmlnukeObject(new XmlInputHidden("op", self::OP_EDITPROJECT));
				$form->addXmlnukeObject(new XmlInputHidden("valueid", $this->_context->ContextValue("valueid")));

				$inputProjName = new XmlInputTextBox($this->_myWords->Value("INPUT_PROJECTNAME"), "projname", $projectName);
				$inputProjName->setReadOnly(($this->_action != self::AC_NEW) || $readonly);
				$form->addXmlnukeObject($inputProjName);

				$directoriesProj = new XmlInputMemo($this->_myWords->Value("INPUT_DIRECTORIES"), "projdir", $this->getProjectObject($projectName, "directory"));
				$directoriesProj->setWrap("OFF");
				$directoriesProj->setReadOnly($readonly);
				$form->addXmlnukeObject($directoriesProj);

				$filesProj = new XmlInputMemo($this->_myWords->Value("INPUT_FILES"), "projfiles", $this->getProjectObject($projectName, "file"));
				$filesProj->setWrap("OFF");
				$filesProj->setReadOnly($readonly);
				$form->addXmlnukeObject($filesProj);

				$form->addXmlnukeObject(new XmlInputLabelField(".", $this->_myWords->Value("CREATEPROJECTHELP1")));
				$form->addXmlnukeObject(new XmlInputLabelField(".", $this->_myWords->Value("CREATEPROJECTHELP2")));
				$form->addXmlnukeObject(new XmlInputLabelField(".", $this->_myWords->Value("CREATEPROJECTHELP3")));

				$setupProj = new XmlInputMemo($this->_myWords->Value("INPUT_SETUP"), "projsetup", $this->getProjectObject($projectName, "setup"));
				$setupProj->setWrap("OFF");
				$setupProj->setReadOnly($readonly);
				$form->addXmlnukeObject($setupProj);

				if ($this->_action != self::AC_VIEW)
				{
					$buttons = new XmlInputButtons();
					$buttons->addSubmit($this->_myWords->Value("TXT_CONFIRM"), "submit");
					$form->addXmlnukeObject($buttons);
				}

				$block->addXmlnukeObject($form);

				$this->defaultXmlnukeDocument->addXmlnukeObject($block);
				break;

			case self::AC_DELETE :
				$projectName = $projectList[$this->_context->ContextValue("valueid")];
				$project = new AnydatasetBackupFilenameProcessor($projectName, $this->_context);
				if (FileUtil::Exists($project->FullQualifiedNameAndPath()))
				{
					FileUtil::DeleteFile($project);
				}
				$this->_context->redirectUrl("admin:backupmodule?op=" . self::OP_EDITPROJECT );
				break;

			default:
				$block = new XmlBlockCollection($this->_myWords->Value("MODULETITLE"), BlockPosition::Center );
				$block->addXmlnukeObject($this->generateList(self::OP_EDITPROJECT, $this->_myWords->Value("PROJECT_LIST"), $this->_myWords->Value("PROJECT_NAME"), $projectList));
				$this->defaultXmlnukeDocument->addXmlnukeObject($block);
				break;
		}
	}

	/**
	 * List and uninstall installed backups, view and install new packages
	 *
	 */
	protected function ManageBackups()
	{


		switch ($this->_action)
		{
			case self::AC_VIEW :
				$this->viewBackup();
				break;

			case self::AC_NEW :
				$this->installBackup();
				break;

			case self::AC_DELETE :
				$this->viewBackupLog();
				break;

			case self::AC_DELETE_CONF :
				$this->uninstallBackup();
				break;

			case self::AC_UPLOADFILE :
				$this->confirmUpload();
				break;

			case self::AC_DOWNLOAD :
				$this->downloadPackage();
				break;

			default:
				$this->listBackups();
				break;
		}

	}



	//-------------------------------------------------------------------------------------------------------


	/**
	 * Get a list of available projects (both in Private and Shared path)
	 *
	 * @return string[]
	 */
	public function getProjectList()
	{
		$project = new AnydatasetBackupFilenameProcessor("", $this->_context);

		//personal list
		$tempProjectList = FileUtil::RetrieveFilesFromFolder($project->PrivatePath(), $project->Extension());

		$projectList = array();
		foreach ($tempProjectList as $projectName)
		{
			$temp_name = FileUtil::ExtractFileName($projectName);
			$projectList[] = $project->removeLanguage($temp_name);
		}

		if ($this->isAdmin())
		{
			//generic list
			$tempProjectList = FileUtil::RetrieveFilesFromFolder($project->SharedPath(), $project->Extension());

			foreach ($tempProjectList as $projectName)
			{
				$temp_name = FileUtil::ExtractFileName($projectName);
				$projectList[] = $project->removeLanguage($temp_name);
			}
		}


		return $projectList;
	}

	/**
	 * Return the project name based in a index. The project list is returned by getProjectList() function.
	 *
	 * @param int $valueid
	 * @return string
	 */
	protected function getProjectName($valueid)
	{
		$projectList = $this->getProjectList();
		return $projectList[$valueid];
	}

	/**
	 * Generic function to list an ArrayList. Your behavior is determined by the current Operation.
	 *
	 * @param string $op
	 * @param string $caption
	 * @param string $columnname
	 * @param string[] $filelist
	 * @param bool $readOnly
	 * @return XmlEditList
	 */
	private function generateList($op, $caption, $columnname, $filelist, $readOnly = false)
	{
		//set the buttons with true or false
		$new_button = false;
		$view = false;
		$edit = false;
		$delete = false;

		switch ($op)
		{
			case self::OP_EDITPROJECT :
				$new_button = true;
				$view = true;
				$edit = true;
				$delete = true;
				break;
		}

		$arrayDs = new ArrayDataSet($filelist);
		$arrayIt = $arrayDs->getIterator();

		$editlist = new XmlEditList($this->_context, $caption, "module:admin.backupmodule", $new_button, $view, $edit, $delete);
		$editlist->addParameter("op", $op);
		$editlist->setDataSource($arrayIt);

		$editlistfield = new EditListField(true);
		$editlistfield->fieldData = "id";
		$editlistfield->editlistName = "#";
		$editlist->addEditListField($editlistfield);

		$editlistfield = new EditListField(true);
		$editlistfield->fieldData = "value";
		$editlistfield->editlistName = $columnname;
		$editlist->addEditListField($editlistfield);

		switch ($op)
		{
			case self::OP_EDITPROJECT :
				$this->addCustomButton($editlist, self::OP_CREATEBACKUP, self::AC_CREATEBACKUP, "Create Backup", "common/editlist/ic_custom.gif", MultipleSelectType::ONLYONE, true);
				break;
			case "backups":
				$this->addCustomButton($editlist, "projects", "Install", "common/editlist/ic_blank.gif", MultipleSelectType::NONE, true);
				$this->addCustomButton($editlist, "projects", "Upload", "common/editlist/ic_blank.gif", MultipleSelectType::NONE, true);
				$this->addCustomButton($editlist, "projects", "Delete", "common/editlist/ic_excluir.gif", MultipleSelectType::NONE, true);
				break;
			case "installed":
				$this->addCustomButton($editlist, "projects", "Uninstall", "common/editlist/ic_blank.gif", MultipleSelectType::NONE, true);
				break;
		}

		if ($readOnly)
		{
			$editlist->setReadonly();
		}

		return $editlist;
	}

	/**
	 * Generic function to add a custom buttom to a XmlEditList
	 *
	 * @param XmlEditList $editlist
	 * @param string $op
	 * @param string $action
	 * @param string $caption
	 * @param string $icon
	 * @param MultipleSelectType $multipleSelectType
	 * @param bool $enable
	 * @param bool $readonly
	 */
	public function addCustomButton($editlist, $op, $action, $caption, $icon, $multipleSelectType, $enable = false, $readonly = false)
	{
		$customButton = new CustomButtons();
		$customButton->action = $action;
		$customButton->enabled = $enable;
		$customButton->alternateText = $caption;

		$url = new XmlnukeManageUrl(URLTYPE::MODULE, "admin.backupmodule");
		$url->addParam("op", $op);

		$customButton->url = htmlentities($url->getUrlFull($this->_context));
		$customButton->icon = $icon;
		$customButton->multiple = $multipleSelectType; //MultipleSelectType::NONE;

		$editlist->setCustomButton($customButton);
	}

	/**
	 * Return an ArrayList containing all backup objects of a specific type ("file" or "directory").
	 *
	 * @param string $projectName
	 * @param string $type
	 * @return string[]
	 */
	public function getProjectObject($projectName, $type)
	{
		if (empty($projectName))
		{
			return "";
		}

		$project = new AnydatasetBackupFilenameProcessor($projectName, $this->_context);
		$anyProject = new AnyDataSet($project);

		$itf = new IteratorFilter();
		$itf->addRelation("type", Relation::Equal, $type);
		$it = $anyProject->getIterator($itf);
		$value = "";
		if ($it->hasNext())
		{
			$sr = $it->moveNext();
			$valueArr = $sr->getFieldArray("object");
			if (is_array($valueArr))
			{
				foreach ($valueArr as $key=>$object)
				{
					$value = $value . $object . "\n";
				}
			}
		}

		return $value;
	}

	/**
	 * Open a project and give an AnyDataSet object
	 *
	 * @param string $projectName
	 * @return AnyDataSet
	 */
	public function openProject($projectName)
	{
		$projectProcessor = new AnydatasetBackupFilenameProcessor($projectName, $this->_context);
		$project = new AnyDataSet($projectProcessor);
		return $project;
	}

	/**
	 * Create an empty project or save a new one. If the project exits it will be deleted.
	 *
	 * @param string $projectName
	 * @param string[] $directory - List of directories
	 * @param string[] $file - List of files
	 * @param string[] $setup - List of setup files
	 */
	public function createProject($projectName, $directory, $file, $setup)
	{
		$project = new AnydatasetBackupFilenameProcessor($projectName, $this->_context);
		//if (FileUtil::Exists($project->FullQualifiedNameAndPath()))
		//{
		//	FileUtil::DeleteFile($project);
		//}

		$splitPattern = "/(.*)\r?\n/";
		//string[] valueArr = System.Text.RegularExpressions.Regex.Split("aaaa\nbbbbb\nccccc\r\ndddddd\neeeeee\r\n", "\r?\n");

		$anyProject = new AnyDataSet();

		$anyProject->appendRow();
		$anyProject->addField("type", "directory");
		$valueArr = preg_split($splitPattern, $directory, -1, PREG_SPLIT_DELIM_CAPTURE + PREG_SPLIT_NO_EMPTY);
		if (is_array($valueArr))
		{
			foreach ($valueArr as $key=>$value)
			{
				if (trim($value) != "")
					$anyProject->addField("object", str_replace("\r", "", $value) );
			}
		}

		$anyProject->appendRow();
		$anyProject->addField("type", "file");
		$valueArr = preg_split($splitPattern, $file, -1, PREG_SPLIT_DELIM_CAPTURE + PREG_SPLIT_NO_EMPTY);
		if (is_array($valueArr))
		{
			foreach ($valueArr as $key=>$value)
			{
				if (trim($value) != "")
					$anyProject->addField("object", str_replace("\r", "", $value));
			}
		}

		$error = array();
		$anyProject->appendRow();
		$anyProject->addField("type", "setup");
		$valueArr = preg_split($splitPattern, $setup, -1, PREG_SPLIT_DELIM_CAPTURE + PREG_SPLIT_NO_EMPTY);
		if (is_array($valueArr))
		{
			foreach ($valueArr as $key=>$value)
			{
				$value = str_replace("\r", "", $value);
				if (trim($value) != "")
				{
					if ( (!preg_match(self::LINESECTIONPATTERN, $value, $array)) && (!preg_match(self::LINEVALUEPATTERN, $value, $array)) )
					{
						$error[] = $this->_myWords->Value("ERRORDESCCREATEPROJECT", $value);
					}
					$anyProject->addField("object", $value);
				}
			}
		}

		if (sizeof($error) > 0)
		{
			$block = new XmlBlockCollection($this->_myWords->Value("BLOCKERRORCREATEPROJECT"), BlockPosition::Center);
			$this->defaultXmlnukeDocument->addXmlnukeObject($block);

			$listErr = new XmlEasyList(EasyListType::UNORDEREDLIST, "", $this->_myWords->Value("LISTERRORCREATEPROJECT"), $error);
			$block->addXmlnukeObject($listErr);
		}
		else
		{
			$anyProject->Save($project);
		}
	}


	protected function listBackups()
	{
		//list the file backups
		$block = new XmlBlockCollection($this->_myWords->Value("TITLE_BACKUPLIST"), BlockPosition::Center);

		$table = new XmlTableCollection();
		$block->addXmlnukeObject($table);

		$backupList = $this->getBackupList();

		// Header
		$tr = new XmlTableRowCollection();
		$table->addXmlnukeObject($tr);

		$td = new XmlTableColumnCollection();
		$td->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("BACKUP_COMMANDS"), true));
		$tr->addXmlnukeObject($td);

		$td = new XmlTableColumnCollection();
		$td->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("BACKUP_NAME"), true));
		$tr->addXmlnukeObject($td);

		foreach ($backupList as $backup)
		{
			$backupProp = explode("*", $backup);

			$tr = new XmlTableRowCollection();
			$table->addXmlnukeObject($tr);

			$td = new XmlTableColumnCollection();
			if ($backupProp[0] == "I")
			{
				$href = new XmlAnchorCollection("admin:backupmodule?bkp=" . $backupProp[1] . "&amp;op=" . self::OP_MANAGEBACKUP  . "&amp;action=" . self::AC_DELETE);
				$href->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("BACKUP_UNINSTALL")));
				$td->addXmlnukeObject($href);
			}
			else
			{
				$href = new XmlAnchorCollection("admin:backupmodule?bkp=" . $backupProp[1] . "&amp;op=" . self::OP_MANAGEBACKUP  . "&amp;action=" . self::AC_VIEW);
				$href->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("BACKUP_INSTALL")));
				$td->addXmlnukeObject($href);
			}
			$td->addXmlnukeObject(new XmlnukeText(" | ", true));
			$href = new XmlAnchorCollection("admin:backupmodule?bkp=" . $backupProp[1] . "&amp;op=" . self::OP_MANAGEBACKUP  . "&amp;action=" . self::AC_DOWNLOAD);
			$href->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("BACKUP_DOWNLOAD")));
			$td->addXmlnukeObject($href);
			$tr->addXmlnukeObject($td);

			$td = new XmlTableColumnCollection();
			$td->addXmlnukeObject(new XmlnukeText($backupProp[1]));
			$tr->addXmlnukeObject($td);
		}

		// List form option
		$form = new XmlFormCollection($this->_context, "module:admin.backupmodule", $this->_myWords->Value("UPLOADBACKUPFILE"));
		$block->addXmlnukeObject($form);

		$file = new XmlInputFile($this->_myWords->Value("CAPTION_FILETOUPLOAD"),"filebackup");
		$form->addXmlnukeObject($file);

		$form->addXmlnukeObject(new XmlInputHidden("op", self::OP_MANAGEBACKUP));
		$form->addXmlnukeObject(new XmlInputHidden("action", self::AC_UPLOADFILE));

		$button = new XmlInputButtons();
		$button->addSubmit($this->_myWords->Value("BTN_CONFIRMUPLOAD"),"");
		$form->addXmlnukeObject($button);

		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
	}

	public function confirmUpload()
	{
		$block = new XmlBlockCollection($this->_myWords->Value("TITLE_FINISHUPLOAD"), BlockPosition::Center);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);

		$backup = new BackupFilenameProcessor("",$this->_context);
		$filepath = $backup->PrivatePath();

		$fileProcessor = new UploadFilenameProcessor("*.*", $this->_context);
		$fileProcessor->setFilenameLocation(ForceFilenameLocation::DefinePath, $filepath);
		$fileProcessor->setValidExtension($backup->Extension());

		$result = $this->_context->processUpload($fileProcessor, false, 'filebackup');

		//verify if the file is a backup file
		if (!is_array($result))
		{
			$msg = $this->_myWords->Value("UPLOADERROR_INVALIDFILE");
			$block->addXmlnukeObject(new XmlnukeText($msg,true,false,false,true));
		}
		else
		{
			$msg = $this->_myWords->Value("UPLOADSUCCESSFULL", $filename);
			$block->addXmlnukeObject(new XmlnukeText($msg,true,false,false,true));
		}
	}

	protected function downloadPackage()
	{
		$bkp = $this->_context->ContextValue("bkp");
		$backupFile = new BackupFilenameProcessor($bkp, $this->_context);
		FileUtil::ResponseCustomContentFromFile("application/x-compressed", $backupFile->FullQualifiedNameAndPath());
	}

	protected function getBackupList()
	{
		$backup = new BackupFilenameProcessor("", $this->_context);
		$backupList = array();

		// Installed Backups
		$backupLogProcessor = new AnydatasetBackupLogFilenameProcessor("backup", $this->_context);
		$anyDataSet = new AnyDataSet($backupLogProcessor);
		$it = $anyDataSet->getIterator();
		while($it->hasNext())
		{
			$row = $it->moveNext();
			$backupList[] = "I*" . $row->getField("project");
		}

		//personal list
		$tempBackupList = FileUtil::RetrieveFilesFromFolder($backup->PrivatePath(), $backup->Extension());

		foreach ($tempBackupList as $backupName)
		{
			$temp_name = str_replace($backup->Extension(), "", FileUtil::ExtractFileName($backupName));
			$found = false;
			foreach ($backupList as $tmpBkp)
			{
				if ( ($tmpBkp == "I*" . $temp_name) || ($tmpBkp == "N*" . $temp_name) )
				{
					$found = true;
					break;
				}
			}
			if (!$found)
			{
				$backupList[] = "N*" . $backup->removeLanguage($temp_name);
			}
		}

		if ($this->isAdmin())
		{
			$tempBackupList = FileUtil::RetrieveFilesFromFolder($backup->SharedPath(), $backup->Extension());

			foreach ($tempBackupList as $backupName)
			{
				$temp_name = str_replace($backup->Extension(), "", FileUtil::ExtractFileName($backupName));
				$found = false;
				foreach ($backupList as $tmpBkp)
				{
					$tmpBkp = FileUtil::ExtractFileName($tmpBkp);
					if ( ($tmpBkp == "I*" . $temp_name) || ($tmpBkp == "N*" . $temp_name) )
					{
						$found = true;
						break;
					}
				}
				if (!$found)
				{
					$backupList[] = "N*" . $backup->removeLanguage($temp_name);
				}
			}
		}


		return $backupList;
	}



	//--------------------------------------------------------
	// CREATE BACKUP
	//--------------------------------------------------------

	/**
	 * Generate the Project Backup
	 *
	 */
	public function createProjectBackup()
	{
		$block = new XmlBlockCollection($this->_myWords->Value("TITLE_CREATEBACKUP"), BlockPosition::Center );
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);

		$projectName = $this->getProjectName($this->_context->ContextValue("valueid"));

		$anyDataSet = $this->openProject($projectName);

		$files = array();

		$it = $anyDataSet->getIterator();

		// Test if exists a previous versions. Old versions must be deleted.
		$backupProcessor = new BackupFilenameProcessor($projectName, $this->_context);
		if (FileUtil::Exists($backupProcessor))
		{
			FileUtil::DeleteFile($backupProcessor);
		}

		// Get the files and directories to be backuped.
		$tmpFile = null;
		while ($it->hasNext())
		{
			$row = $it->moveNext();
			switch ($row->getField("type"))
			{
				case 'file':
					$array = $row->getFieldArray("object");
					if ($array)
					{
						foreach ($array as $field => $value)
						{
							$files[] = $value;
						}
					}
					break;
				case 'directory':
					$array = $row->getFieldArray("object");
					if ($array)
					{
						foreach ($array as $field => $value)
						{
							$files[] = $value;
						}
					}
					break;
				case 'setup':
					$array = $row->getFieldArray("object");
					$tmpFile = $this->getTempFileName($backupProcessor);
					FileUtil::QuickFileWrite($tmpFile, join("\n", $array) );
					$files[] = $tmpFile;
					break;
			}
		}

		// Start the process
		$tarFile = $backupProcessor->FullQualifiedNameAndPath();
		ini_set("max_execution_time",300);

		//create the tar file
		$tar = new Tar($tarFile, 'gz'); // Possible Values: null, 'gz', 'bz2'
		$result = $tar->create($files);

		//verify if the file was created, or not created
		if (!$result)
			$text = $this->_myWords->Value("BACKUPNOTCREATED");
		else
			$text = $this->_myWords->Value("BACKUPCREATED");

		$block->addXmlnukeObject(new XmlnukeText($text,true));
		$block->addXmlnukeObject(new XmlnukeBreakLine());
		$block->addXmlnukeObject(new XmlnukeBreakLine());

		//if successfull, show the files in tar file
		if ($result)
		{
			$tar->listFiles($block);
		}
		else
		{
			$tar->showErrors($block);
		}

		// Delete the temp file
		if (!is_null($tmpFile))
		{
			FileUtil::DeleteFileString($tmpFile);
		}
	}

	protected function getTempFileName($backupProcessor)
	{
		$tmpFile = $backupProcessor->PathSuggested() . "." . $backupProcessor->ToString() . ".tmp";
		return str_replace($this->_context->SystemRootPath(), "", $tmpFile);
	}

	/**
	 * View the Backup File
	 *
	 */
	public function viewBackup ()
	{
		$backupName = $this->_context->ContextValue("bkp");

		$block = new XmlBlockCollection($this->_myWords->Value("TITLE_BACKUPCONTENTS", $backupName), BlockPosition::Center);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);

		$backupProcessor = new BackupFilenameProcessor($backupName, $this->_context);
		$backupProcessor->setFilenameLocation(ForceFilenameLocation::UseWhereExists);

		$form = new XmlFormCollection($this->_context, "admin:backupmodule", "");
		$form->addXmlnukeObject(new XmlInputHidden("op", self::OP_MANAGEBACKUP));
		$form->addXmlnukeObject(new XmlInputHidden("action", self::AC_NEW));
		$form->addXmlnukeObject(new XmlInputHidden("bkp", $backupName));
		$button = new XmlInputButtons();
		$button->addSubmit($this->_myWords->Value("BTN_BACKUPINSTALL", $backupName), "btn");
		$form->addXmlnukeObject($button);

		$block->addXmlnukeObject($form);
		$tar = new Tar($backupProcessor->FullQualifiedNameAndPath());
		$tar->listFiles($block);
		$block->addXmlnukeObject($form);
	}


	/**
	 * View the Backup File
	 *
	 */
	public function viewBackupLog ()
	{
		$backupName = $this->_context->ContextValue("bkp");

		$block = new XmlBlockCollection($this->_myWords->Value("TITLE_BACKUPLOGCONTENTS", $backupName), BlockPosition::Center);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);

		$backupProcessor = new BackupFilenameProcessor($backupName, $this->_context);
		$backupProcessor->setFilenameLocation(ForceFilenameLocation::UseWhereExists);

		$form = new XmlFormCollection($this->_context, "admin:backupmodule", "");
		$form->addXmlnukeObject(new XmlInputHidden("op", self::OP_MANAGEBACKUP));
		$form->addXmlnukeObject(new XmlInputHidden("action", self::AC_DELETE_CONF ));
		$form->addXmlnukeObject(new XmlInputHidden("bkp", $backupName));
		$button = new XmlInputButtons();
		$button->addSubmit($this->_myWords->Value("BTN_BACKUPUNINSTALL", $backupName), "btn");
		$form->addXmlnukeObject($button);

		$block->addXmlnukeObject($form);

		$anyDataSetFile = new AnydatasetBackupLogFilenameProcessor("backup", $this->_context);
		$anyDataSet = new AnyDataSet($anyDataSetFile);

		$error = false;

		//Delete project
		$itf = new IteratorFilter();
		$itf->addRelation("project", Relation::Equal, $backupName);
		$it = $anyDataSet->getIterator();
		while($it->hasNext())
		{
			$row = $it->moveNext();

			$directories = $row->getFieldArray("directory");
			if ($directories)
			{
				foreach ($directories as $value)
				{
					$block->addXmlnukeObject(new XmlnukeText("Directory: " . $value),true,false,false,true);
					$block->addXmlnukeObject(new XmlnukeBreakLine());
				}
			}

			$files = $row->getFieldArray("file");
			if ($files)
			{
				foreach ($files as $value)
				{
					$block->addXmlnukeObject(new XmlnukeText("File: " . $value),true,false,false,true);
					$block->addXmlnukeObject(new XmlnukeBreakLine());
				}
			}

			$setup = $row->getFieldArray("setup");
			if ($setup)
			{
				foreach ($setup as $value)
				{
					$block->addXmlnukeObject(new XmlnukeText("Setup: " . $value),true,false,false,true);
					$block->addXmlnukeObject(new XmlnukeBreakLine());
				}
			}
		}

		$block->addXmlnukeObject($form);
	}


	/**
	 * Install the Tar Backup
	 *
	 */
	public function installBackup ()
	{
		$backupName = $this->_context->ContextValue("bkp");

		$block = new XmlBlockCollection($this->_myWords->Value("TITLE_INSTALLBACKUP", $backupName), BlockPosition::Center);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);

		//install directories and files in tar files
		$backupProcessor = new BackupFilenameProcessor($backupName, $this->_context);
		$tar = new Tar($backupProcessor->FullQualifiedNameAndPath());
		$tar->extract(".");   // <<<===-------------------------------

		if (($tar->error) || ($tar->warning))
			$text = $this->_myWords->Value("BACKUP_INSTALL_ERROR");
		else
			$text = $this->_myWords->Value("BACKUP_INSTALL_SUCCESS");

		$block->addXmlnukeObject(new XmlnukeText($text,true));
		$block->addXmlnukeObject(new XmlnukeBreakLine());
		$block->addXmlnukeObject(new XmlnukeBreakLine());

		$tar->showErrors($block);

		if ((!$tar->error) && (!$tar->warning))
		{
			//set the status of backup installation in config file
			$anyDataSetLogFile = new AnydatasetBackupLogFilenameProcessor("backup", $this->_context);
			$anyDataSetLog = new AnyDataSet($anyDataSetLogFile);
			$anyDataSetLog->appendRow();
			$anyDataSetLog->addField("project", $backupName);
			$anyDataSetLog->addField("date", date("Y-m-d H:i:s"));
			//save the directories installed
			$directories = $tar->getDirectories();
			if ($directories)
			{
				foreach ($directories as $directory)
				{
					$anyDataSetLog->addField("directory", $directory);
				}
			}
			//save the files installed
			$files = $tar->getFiles();
			if ($files)
			{
				foreach ($files as $file)
				{
					$anyDataSetLog->addField("file", $file);
				}
			}

			$tmpFile = "./" . $this->getTempFileName($backupProcessor);
			if (FileUtil::Exists($tmpFile))
			{
				$anydataRfl = null;
				$singleRowRfl = null;
				$lines = preg_split("/\n/", FileUtil::QuickFileRead($tmpFile));
				foreach ($lines as $line)
				{
					$anyDataSetLog->addField("setup", $line);
					$arrMatch = array();
					if (preg_match(self::LINESECTIONPATTERN, $line, $arrMatch, PREG_OFFSET_CAPTURE))
					{
						$anydataRfl = $this->getAnyDataByReflection($arrMatch[1][0], $arrMatch[2][0], $arrMatch[3][0]);
						$singleRowRfl = $this->getRowByReflection($anydataRfl, $arrMatch[4][0]);
					}
					elseif (preg_match(self::LINEVALUEPATTERN, $line, $arrMatch, PREG_OFFSET_CAPTURE))
					{
						$fieldValue = explode("=", $line);
						if (!is_null($singleRowRfl))
						{
							$singleRowRfl->AddField(trim($fieldValue[0]), trim($fieldValue[1]));
						}
						elseif (!is_null($anydataRfl))
						{
							$anydataRfl->addField(trim($fieldValue[0]), trim($fieldValue[1]));
						}
						else
						{
							throw new Exception($this->_myWords->Value("WRONGLINEDEF", $line));
						}
						$anydataRfl->Save();
					}
					else
					{
						throw new Exception($this->_myWords->Value("SETUPERROR", $line));
					}
				}
				FileUtil::DeleteFileString($tmpFile);
			}

			$anyDataSetLog->Save();
		}
	}


	protected function getAnyDataByReflection($strProcessor, $singleName, $location)
	{
		$class = new ReflectionClass($strProcessor);
		//$fileProcessorRfl = new AnyDataSetFilenameProcessor("a", $this->_context);
		$fileProcessorRfl = $class->newInstance($singleName, $this->_context);
		switch ($location)
		{
			case "private":
				$fileProcessorRfl->setFilenameLocation(ForceFilenameLocation::PrivatePath);
				break;
			case "shared":
				$fileProcessorRfl->setFilenameLocation(ForceFilenameLocation::SharedPath);
				break;
			default:
				$fileProcessorRfl->setFilenameLocation(ForceFilenameLocation::UseWhereExists);
				break;
		}

		return new AnyDataSet($fileProcessorRfl);
	}

	protected function getRowByReflection($anydataRfl, $strFilter)
	{
		$filter = explode(";", $strFilter);

		$itf = new IteratorFilter();
		foreach ($filter as $key=>$value)
		{
			$filterValue = explode("=", $value);
			$itf->addRelation(trim($filterValue[0]), Relation::Equal, trim($filterValue[1]));
		}
		$it = $anydataRfl->getIterator($itf);
		if ($it->hasNext())
		{
			return $it->moveNext();
		}
		else
		{
			$anydataRfl->appendRow();
			foreach ($filter as $key=>$value)
			{
				$filterValue = explode("=", $value);
				$anydataRfl->addField(trim($filterValue[0]), trim($filterValue[1]));
				$anydataRfl->Save();
			}
			return null;
		}
	}

	/**
	 * Uninstall Backup
	 *
	 */
	public function uninstallBackup()
	{
		$projectName = $this->_context->ContextValue("bkp");

		$block = new XmlBlockCollection($this->_myWords->Value("TITLE_UNINSTALLBACKUP", $projectName), BlockPosition::Center);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);

		$anyDataSetFile = new AnydatasetBackupLogFilenameProcessor("backup", $this->_context);
		$anyDataSet = new AnyDataSet($anyDataSetFile);

		$error = false;

		//Delete project
		$itf = new IteratorFilter();
		$itf->addRelation("project", Relation::Equal, $projectName);
		$it = $anyDataSet->getIterator();
		while($it->hasNext())
		{
			$row = $it->moveNext();

			$block->addXmlnukeObject(new XmlnukeBreakLine());
			$block->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("UNINSTALL_LOG"),true,false,false,true));

			$directories = $row->getFieldArray("directory");
			$files = $row->getFieldArray("file");
			$setup = $row->getFieldArray("setup");

			//delete all installed files
			if ($files)
			{
				foreach ($files as $file)
				{
					$file = "." . FileUtil::Slash() . FileUtil::AdjustSlashes($file);
					$block->addXmlnukeObject(new XmlnukeText("File: " . $file));
					try
					{
						if (FileUtil::Exists($file))
						{
							FileUtil::DeleteFileString($file);
							$block->addXmlnukeObject(new XmlnukeText(" OK",false,false,false,true));
						}
						else
						{
							$block->addXmlnukeObject(new XmlnukeText(" MISSING",true,false,false,true));
						}
					}
					catch (Exception $e)
					{
						$block->addXmlnukeObject(new XmlnukeText(" ERROR " . $e->getMessage(),true,false,false,true));
						$error = true;
					}
				}
			}


			//delete all installed directories
			if ($directories)
			{
				rsort($directories);
				foreach ($directories as $directory)
				{
					$directory = ".". FileUtil::Slash() . FileUtil::AdjustSlashes($directory);
					$block->addXmlnukeObject(new XmlnukeText("Directory: " . $directory));
					try
					{
						if (FileUtil::Exists($directory))
						{
							FileUtil::DeleteDirectory($directory);
							$block->addXmlnukeObject(new XmlnukeText(" OK",false,false,false,true));
						}
						else
						{
							$block->addXmlnukeObject(new XmlnukeText(" MISSING",true,false,false,true));
						}
					}
					catch (Exception $e)
					{
						$block->addXmlnukeObject(new XmlnukeText(" ERROR " . $e->getMessage(),true,false,false,true));
						$error = true;
					}
				}
			}

			if ($setup)
			{
				$anydataRfl = null;
				$singleRowRfl = null;
				foreach ($setup as $line)
				{
					$arrMatch = array();
					if (preg_match(self::LINESECTIONPATTERN, $line, $arrMatch, PREG_OFFSET_CAPTURE))
					{
						$anydataRfl = $this->getAnyDataByReflection($arrMatch[1][0], $arrMatch[2][0], $arrMatch[3][0]);
						$singleRowRfl = $this->getRowByReflection($anydataRfl, $arrMatch[4][0]);
						$block->addXmlnukeObject(new XmlnukeText("Opened: " . $arrMatch[1][0] . "(" . $arrMatch[2][0] . ") in " . $arrMatch[3][0]));
						$block->addXmlnukeObject(new XmlnukeText(" OK",false,false,false,true));
					}
					elseif (preg_match(self::LINEVALUEPATTERN, $line, $arrMatch, PREG_OFFSET_CAPTURE))
					{
						$fieldValue = explode("=", $line);
						$block->addXmlnukeObject(new XmlnukeText("Delete Row: " . $line));
						if (!is_null($singleRowRfl))
						{
							$singleRowRfl->removeFieldNameValue(trim($fieldValue[0]), trim($fieldValue[1]));
							$block->addXmlnukeObject(new XmlnukeText(" OK",false,false,false,true));
							$anydataRfl->Save();
						}
						else
						{
							$block->addXmlnukeObject(new XmlnukeText(" MISSING",false,false,false,true));
						}
					}
					else
					{
						throw new Exception($this->_myWords->Value("SETUPERROR", $line));
					}
				}
			}
		}


		if (!$error)
		{
			$anyDataSetFile = new AnydatasetBackupLogFilenameProcessor("backup", $this->_context);
			$anyDataSet = new AnyDataSet($anyDataSetFile);
			$it = $anyDataSet->getIterator();
			while($it->hasNext())
			{
				$row = $it->moveNext();
				if ($row->getField("project") == $projectName)
				{
					$anyDataSet->removeRow($row);
					$anyDataSet->Save();
				}
			}
		}

		$block->addXmlnukeObject(new XmlnukeBreakLine());
	}
}
?>
