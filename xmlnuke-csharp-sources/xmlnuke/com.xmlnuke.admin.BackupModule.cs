/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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

using System;
using System.Collections;
using System.Text;
using System.Text.RegularExpressions;
using System.Reflection;

using com.xmlnuke.module;
using com.xmlnuke.international;
using com.xmlnuke.anydataset;
using com.xmlnuke.classes;
using com.xmlnuke.engine;
using com.xmlnuke.processor;
using com.xmlnuke.util;

namespace com.xmlnuke.admin
{
	public class BackupModule : NewBaseAdminModule
	{
		const string OP_EDITPROJECT = "prj";
		const string OP_MANAGEBACKUP = "mbkp";
		const string OP_CREATEBACKUP = "cbkp";

		const string AC_NEW = "new";
		const string AC_NEW_CONF = "newconf";
		const string AC_EDIT = "edit";
		const string AC_EDIT_CONF = "editconf";
		const string AC_DELETE = "delete";
		const string AC_DELETE_CONF = "deleteconf";
		const string AC_VIEW = "view";
		const string AC_VIEW_CONF = "viewconf";
		const string AC_UPLOADFILE = "upload";
		const string AC_DOWNLOAD = "dnld";

		const string AC_CREATEBACKUP = "confcreate";

		const string LINESECTIONPATTERN = @"^\[\s*([\w\n]*)\s*\:\s*([\w\n]*)\s*\:\s*([\w\n]*)\s*\:\s*(([\w\n\s]*\s*\=\s*[\w\n\s]*\s*\;?\s*)+)\s*\]";
		const string LINEVALUEPATTERN = @"^([\w\n\s]*\s*\=\s*[\w\n\s]*)";

		public BackupModule()
		{
		}

		/**
		 * Override. Function use cache or not.
		 *
		 * @return bool
		 */
		public override bool useCache()
		{
			return false;
		}

		/**
		 * Override. Valid Access Level.
		 *
		 * @return AccessLevel
		 */
		public override AccessLevel getAccessLevel()
		{
			return AccessLevel.CurrentSiteAndRole;
		}

		/**
		 * Override. Valid roles
		 *
		 * @return string
		 */
		public override string[] getRole()
		{
			return new string[] { "MANAGER" };
		}


		/**
		 * Create Page Method
		 *
		 * @return PageXml
		 */
		public override IXmlnukeDocument CreatePage()
		{
			base.CreatePage(); // Doesnt necessary get PX, because PX is protected!

			this._myWords = this.WordCollection();

			this.setHelp(this._myWords.Value("HELPMODULE"));

			this.defaultXmlnukeDocument.addMenuItem("admin:backupmodule?op=" + OP_EDITPROJECT, this._myWords.Value("MENU_PROJECTS"), "");
			this.defaultXmlnukeDocument.addMenuItem("admin:backupmodule?op=" + OP_MANAGEBACKUP, this._myWords.Value("MENU_BACKUP"), "");
			this.defaultXmlnukeDocument.addMenuItem("admin:backupmodule", this._myWords.Value("MENU_HOME"), "");

			string op = this._context.ContextValue("op");

			Debug.Print(this._action);
			Debug.Print(op);

			switch (op)
			{
				case OP_EDITPROJECT:
					this.EditProjects();
					break;

				case OP_MANAGEBACKUP:
					this.ManageBackups();
					break;

				case OP_CREATEBACKUP:
					this.createProjectBackup();
					break;

				default:
					this.ExplainModule();
					break;
			}

			return this.defaultXmlnukeDocument.generatePage();
		}

		/**
		 * Module to say a "Welcome Message"
		 *
		 */
		protected void ExplainModule()
		{
			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("MODULETITLE"), BlockPosition.Center);

			//show options to select
			XmlParagraphCollection paragraph = new XmlParagraphCollection();
			paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("EXPLAINMODULE")));
			block.addXmlnukeObject(paragraph);

			paragraph = new XmlParagraphCollection();
			paragraph.addXmlnukeObject(new XmlnukeText(this._myWords.Value("EXPLAINMODULE_SELECT")));
			block.addXmlnukeObject(paragraph);

			this.defaultXmlnukeDocument.addXmlnukeObject(block);
		}

		/**
		 * List the avaliable projects and enable create a TAR.GZ file with the selected options
		 * Accessible only by op = OP_EDITPROJECT
		 *
		 */
		protected void EditProjects()
		{
			string projectName = "";
			ArrayList projectList = this.getProjectList();
			bool readOnly = false;

			switch (this._action)
			{
				case AC_EDIT_CONF:
				case AC_VIEW:
				case AC_EDIT:
				case AC_NEW:
					if (this._action == AC_EDIT_CONF)
					{
						projectName = this._context.ContextValue("projname");
						this.createProject(projectName, this._context.ContextValue("projdir"), this._context.ContextValue("projfiles"), this._context.ContextValue("projsetup"));
					}

					readOnly = (this._action == AC_VIEW);

					if ((projectName == "") && (this._action == AC_EDIT))
						projectName = projectList[Convert.ToInt32(this._context.ContextValue("valueid"))].ToString();

					XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("BLOCK_EDITPROJECT", projectName), BlockPosition.Center);
					XmlFormCollection form = new XmlFormCollection(this._context, "admin:backupmodule?action=" + AC_EDIT_CONF, this._myWords.Value("FORM_EDITPROJECT"));
					form.addXmlnukeObject(new XmlInputHidden("op", OP_EDITPROJECT));
					form.addXmlnukeObject(new XmlInputHidden("valueid", this._context.ContextValue("valueid")));

					XmlInputTextBox inputProjName = new XmlInputTextBox(this._myWords.Value("INPUT_PROJECTNAME"), "projname", projectName);
					inputProjName.setReadOnly((this._action != AC_NEW) || readOnly);
					form.addXmlnukeObject(inputProjName);

					XmlInputMemo directoriesProj = new XmlInputMemo(this._myWords.Value("INPUT_DIRECTORIES"), "projdir", this.getProjectObject(projectName, "directory"));
					directoriesProj.setWrap("OFF");
					directoriesProj.setReadOnly(readOnly);
					form.addXmlnukeObject(directoriesProj);

					XmlInputMemo filesProj = new XmlInputMemo(this._myWords.Value("INPUT_FILES"), "projfiles", this.getProjectObject(projectName, "file"));
					filesProj.setWrap("OFF");
					filesProj.setReadOnly(readOnly);
					form.addXmlnukeObject(filesProj);

					form.addXmlnukeObject(new XmlInputLabelField(".", this._myWords.Value("CREATEPROJECTHELP1")));
					form.addXmlnukeObject(new XmlInputLabelField(".", this._myWords.Value("CREATEPROJECTHELP2")));
					form.addXmlnukeObject(new XmlInputLabelField(".", this._myWords.Value("CREATEPROJECTHELP3")));

					XmlInputMemo setupProj = new XmlInputMemo(this._myWords.Value("INPUT_SETUP"), "projsetup", this.getProjectObject(projectName, "setup"));
					setupProj.setWrap("OFF");
					setupProj.setReadOnly(readOnly);
					form.addXmlnukeObject(setupProj);

					if (this._action != AC_VIEW)
					{
						XmlInputButtons buttons = new XmlInputButtons();
						buttons.addSubmit(this._myWords.Value("TXT_CONFIRM"), "submit");
						form.addXmlnukeObject(buttons);
					}

					block.addXmlnukeObject(form);

					this.defaultXmlnukeDocument.addXmlnukeObject(block);
					break;

				case AC_DELETE:
					projectName = projectList[Convert.ToInt32(this._context.ContextValue("valueid"))].ToString();
					AnydatasetBackupFilenameProcessor project = new AnydatasetBackupFilenameProcessor(projectName, this._context);
					if (FileUtil.Exists(project.FullQualifiedNameAndPath()))
					{
						FileUtil.DeleteFile(project);
					}
					this._context.redirectUrl("admin:backupmodule?op=" + OP_EDITPROJECT);
					break;

				default:
					block = new XmlBlockCollection(this._myWords.Value("MODULETITLE"), BlockPosition.Center);
					block.addXmlnukeObject(this.generateList(OP_EDITPROJECT, this._myWords.Value("PROJECT_LIST"), this._myWords.Value("PROJECT_NAME"), projectList));
					this.defaultXmlnukeDocument.addXmlnukeObject(block);
					break;
			}
		}

		/**
		 * List and uninstall installed backups, view and install new packages
		 *
		 */
		protected void ManageBackups()
		{
			switch (this._action)
			{
				case AC_VIEW:
					this.viewBackup();
					break;

				case AC_NEW:
					this.installBackup();
					break;

				case AC_DELETE:
					this.viewBackupLog();
					break;

				case AC_DELETE_CONF:
					this.uninstallBackup();
					break;

				case AC_UPLOADFILE:
					this.confirmUpload();
					break;

				case AC_DOWNLOAD:
					this.downloadPackage();
					break;

				default:
					this.listBackups();
					break;
			}

		}



		//-------------------------------------------------------------------------------------------------------


		/**
		 * Get a list of available projects (both in Private and Shared path)
		 *
		 * @return string[]
		 */
		public ArrayList getProjectList()
		{
			AnydatasetBackupFilenameProcessor project = new AnydatasetBackupFilenameProcessor("", this._context);

			//personal list
			string[] tempProjectList = FileUtil.RetrieveFilesFromFolder(project.PrivatePath(), "*" + project.Extension());

			ArrayList projectList = new ArrayList();
			foreach (string projectName in tempProjectList)
			{
				string temp_name = FileUtil.ExtractFileName(projectName);
				projectList.Add(project.removeLanguage(temp_name));
			}

			if (this.isUserAdmin())
			{
				//generic list
				tempProjectList = FileUtil.RetrieveFilesFromFolder(project.SharedPath(), "*" + project.Extension());

				foreach (string projectName in tempProjectList)
				{
					string temp_name = FileUtil.ExtractFileName(projectName);
					projectList.Add(project.removeLanguage(temp_name));
				}
			}

			return projectList;
		}

		/**
		 * Return the project name based in a index. The project list is returned by getProjectList() function.
		 *
		 * @param int valueid
		 * @return string
		 */
		protected string getProjectName(int valueid)
		{
			ArrayList projectList = this.getProjectList();
			return (string)projectList[valueid];
		}

		/**
		 * Generic function to list an ArrayList. Your behavior is determined by the current Operation.
		 *
		 * @param string op
		 * @param string caption
		 * @param string columnname
		 * @param string[] filelist
		 * @param bool readOnly
		 * @return XmlEditList
		 */
		private XmlEditList generateList(string op, string caption, string columnname, ArrayList filelist, bool readOnly)
		{
			//set the buttons with true or false		
			bool new_button = false;
			bool view = false;
			bool edit = false;
			bool delete = false;

			switch (op)
			{
				case OP_EDITPROJECT:
					new_button = true;
					view = true;
					edit = true;
					delete = true;
					break;
			}

			ArrayDataSet arrayDs = new ArrayDataSet(filelist);
			IIterator arrayIt = arrayDs.getIterator();

			XmlEditList editlist = new XmlEditList(this._context, caption, "admin:backupmodule", new_button, view, edit, delete);
			editlist.addParameter("op", op);
			editlist.setDataSource(arrayIt);

			EditListField editlistfield = new EditListField(true);
			editlistfield.fieldData = "id";
			editlistfield.editlistName = "#";
			editlist.addEditListField(editlistfield);

			editlistfield = new EditListField(true);
			editlistfield.fieldData = "value";
			editlistfield.editlistName = columnname;
			editlist.addEditListField(editlistfield);

			switch (op)
			{
				case OP_EDITPROJECT:
					this.addCustomButton(editlist, OP_CREATEBACKUP, AC_CREATEBACKUP, "Create Backup", "common/editlist/ic_custom.gif", MultipleSelectType.ONLYONE);
					break;
			}

			editlist.setReadOnly(readOnly);

			return editlist;
		}

		private XmlEditList generateList(string op, string caption, string columnname, ArrayList filelist)
		{
			return this.generateList(op, caption, columnname, filelist, false);
		}

		/**
		 * Generic function to add a custom buttom to a XmlEditList
		 *
		 * @param XmlEditList editlist
		 * @param string op
		 * @param string action
		 * @param string caption
		 * @param string icon
		 * @param MultipleSelectType multipleSelectType
		 * @param bool enable
		 * @param bool readOnly
		 */
		public void addCustomButton(XmlEditList editlist, string op, string action, string caption, string icon, MultipleSelectType multipleSelectType, bool enable, bool readOnly)
		{
			CustomButtons customButton = new CustomButtons();
			customButton.action = action;
			customButton.enabled = enable;
			customButton.alternateText = caption;

			XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.MODULE, "com.xmlnuke.admin.backupmodule");
			url.addParam("op", op);

			customButton.url = url.getUrlFull(this._context);
			customButton.icon = icon;
			customButton.multiple = multipleSelectType; //MultipleSelectType.NONE;

			editlist.setCustomButton(customButton);
		}
		public void addCustomButton(XmlEditList editlist, string op, string action, string caption, string icon, MultipleSelectType multipleSelectType)
		{
			this.addCustomButton(editlist, op, action, caption, icon, multipleSelectType, true, false);
		}

		/**
		 * Return an ArrayList containing all backup objects of a specific type ("file" or "directory").
		 *
		 * @param string projectName
		 * @param string type
		 * @return string[]
		 */
		public string getProjectObject(string projectName, string type)
		{
			if (projectName == "")
			{
				return "";
			}

			AnydatasetBackupFilenameProcessor project = new AnydatasetBackupFilenameProcessor(projectName, this._context);
			AnyDataSet anyProject = new AnyDataSet(project);

			IteratorFilter itf = new IteratorFilter();
			itf.addRelation("type", Relation.Equal, type);
			IIterator it = anyProject.getIterator(itf);
			string value = "";
			if (it.hasNext())
			{
				SingleRow sr = it.moveNext();
				string[] valueArr = sr.getFieldArray("object");
				value = string.Join("\n", valueArr);
			}

			return value;
		}

		/**
		 * Open a project and give an AnyDataSet object
		 *
		 * @param string projectName
		 * @return AnyDataSet
		 */
		public AnyDataSet openProject(string projectName)
		{
			AnydatasetBackupFilenameProcessor projectProcessor = new AnydatasetBackupFilenameProcessor(projectName, this._context);
			AnyDataSet project = new AnyDataSet(projectProcessor);
			return project;
		}

		/**
		 * Create an empty project or save a new one. If the project exits it will be deleted.
		 *
		 * @param string projectName
		 * @param string[] directory - List of directories
		 * @param string[] file - List of files
		 * @param string[] setup - List of setup files
		 */
		public void createProject(string projectName, string directory, string file, string setup)
		{
			AnydatasetBackupFilenameProcessor project = new AnydatasetBackupFilenameProcessor(projectName, this._context);
			//if (FileUtil.Exists(project.FullQualifiedNameAndPath()))
			//{
			//	FileUtil.DeleteFile(project);
			//}

			char splitPattern = '\n';
			//string[] valueArr = System.Text.RegularExpressions.Regex.Split("aaaa\nbbbbb\nccccc\r\ndddddd\neeeeee\r\n", "\r?\n");

			AnyDataSet anyProject = new AnyDataSet();

			anyProject.appendRow();
			anyProject.addField("type", "directory");
			string[] valueArr = directory.Split(splitPattern);
			foreach (string value in valueArr)
			{
				if (value != "")
					anyProject.addField("object", value.Replace("\r", ""));
			}

			anyProject.appendRow();
			anyProject.addField("type", "file");
			valueArr = file.Split(splitPattern);
			foreach (string value in valueArr)
			{
				if (value != "")
					anyProject.addField("object", value.Replace("\r", ""));
			}

			ArrayList error = new ArrayList();
			anyProject.appendRow();
			anyProject.addField("type", "setup");
			valueArr = setup.Split(splitPattern);
			foreach (string valueItem in valueArr)
			{
				string value = valueItem.Replace("\r", "");
				if (value != "")
				{
					if ((!Regex.Match(value, LINESECTIONPATTERN).Success) && (!Regex.Match(value, LINEVALUEPATTERN).Success))
					{
						error.Add(this._myWords.Value("ERRORDESCCREATEPROJECT", value));
					}
					anyProject.addField("object", value);
				}
			}

			if (error.Count > 0)
			{
				XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("BLOCKERRORCREATEPROJECT"), BlockPosition.Center);
				this.defaultXmlnukeDocument.addXmlnukeObject(block);

				XmlEasyList listErr = new XmlEasyList(EasyListType.UNORDEREDLIST, "", this._myWords.Value("LISTERRORCREATEPROJECT"), error);
				block.addXmlnukeObject(listErr);
			}
			else
			{
				anyProject.Save(project);
			}
		}


		protected void listBackups()
		{
			//list the file backups		
			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("TITLE_BACKUPLIST"), BlockPosition.Center);

			XmlTableCollection table = new XmlTableCollection();
			block.addXmlnukeObject(table);

			ArrayList backupList = this.getBackupList();

			// Header
			XmlTableRowCollection tr = new XmlTableRowCollection();
			table.addXmlnukeObject(tr);

			XmlTableColumnCollection td = new XmlTableColumnCollection();
			td.addXmlnukeObject(new XmlnukeText(this._myWords.Value("BACKUP_COMMANDS"), true));
			tr.addXmlnukeObject(td);

			td = new XmlTableColumnCollection();
			td.addXmlnukeObject(new XmlnukeText(this._myWords.Value("BACKUP_NAME"), true));
			tr.addXmlnukeObject(td);

			foreach (object obackup in backupList)
			{
				string backup = (string)obackup;
				string[] backupProp = backup.Split('*');

				tr = new XmlTableRowCollection();
				table.addXmlnukeObject(tr);

				td = new XmlTableColumnCollection();
				XmlAnchorCollection href;
				if (backupProp[0] == "I")
				{
					href = new XmlAnchorCollection("admin:backupmodule?bkp=" + backupProp[1] + "&op=" + OP_MANAGEBACKUP + "&action=" + AC_DELETE);
					href.addXmlnukeObject(new XmlnukeText(this._myWords.Value("BACKUP_UNINSTALL")));
					td.addXmlnukeObject(href);
				}
				else
				{
					href = new XmlAnchorCollection("admin:backupmodule?bkp=" + backupProp[1] + "&op=" + OP_MANAGEBACKUP + "&action=" + AC_VIEW);
					href.addXmlnukeObject(new XmlnukeText(this._myWords.Value("BACKUP_INSTALL")));
					td.addXmlnukeObject(href);
				}
				td.addXmlnukeObject(new XmlnukeText(" | ", true));
				href = new XmlAnchorCollection("admin:backupmodule?bkp=" + backupProp[1] + "&op=" + OP_MANAGEBACKUP + "&action=" + AC_DOWNLOAD);
				href.addXmlnukeObject(new XmlnukeText(this._myWords.Value("BACKUP_DOWNLOAD")));
				td.addXmlnukeObject(href);
				tr.addXmlnukeObject(td);

				td = new XmlTableColumnCollection();
				td.addXmlnukeObject(new XmlnukeText(backupProp[1]));
				tr.addXmlnukeObject(td);
			}

			// List form option
			XmlFormCollection form = new XmlFormCollection(this._context, "admin:backupmodule", this._myWords.Value("UPLOADBACKUPFILE"));
			block.addXmlnukeObject(form);

			XmlInputFile file = new XmlInputFile(this._myWords.Value("CAPTION_FILETOUPLOAD"), "filebackup");
			form.addXmlnukeObject(file);

			form.addXmlnukeObject(new XmlInputHidden("op", OP_MANAGEBACKUP));
			form.addXmlnukeObject(new XmlInputHidden("action", AC_UPLOADFILE));

			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(this._myWords.Value("BTN_CONFIRMUPLOAD"), "");
			form.addXmlnukeObject(button);

			this.defaultXmlnukeDocument.addXmlnukeObject(block);
		}

		public void confirmUpload()
		{
			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("TITLE_FINISHUPLOAD"), BlockPosition.Center);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			BackupFilenameProcessor backup = new BackupFilenameProcessor("", this._context);
			string filepath = backup.PrivatePath();

			UploadFilenameProcessor fileProcessor = new UploadFilenameProcessor("", this._context);
			fileProcessor.PathForced = filepath;
			//fileProcessor.setValidExtension(backup.Extension());

			// Save the file
			ArrayList result = this._context.processUpload(fileProcessor, false, "filebackup");

			//verify if the file is a backup file
			if (result.Count == 0)
			{
				string msg = this._myWords.Value("UPLOADERROR_INVALIDFILE");
				block.addXmlnukeObject(new XmlnukeText(msg, true, false, false, true));
			}
			else
			{
				string msg = this._myWords.Value("UPLOADSUCCESSFULL", result[0].ToString());
				block.addXmlnukeObject(new XmlnukeText(msg, true, false, false, true));
			}
		}

		protected void downloadPackage()
		{
			string bkp = this._context.ContextValue("bkp");
			BackupFilenameProcessor backupFile = new BackupFilenameProcessor(bkp, this._context);
			FileUtil.ResponseCustomContentFromFile("application/x-compressed", backupFile.FullQualifiedNameAndPath());
		}

		protected ArrayList getBackupList()
		{
			BackupFilenameProcessor backup = new BackupFilenameProcessor("", this._context);
			ArrayList backupList = new ArrayList();

			// Installed Backups
			AnydatasetBackupLogFilenameProcessor backupLogProcessor = new AnydatasetBackupLogFilenameProcessor("backup", this._context);
			AnyDataSet anyDataSet = new AnyDataSet(backupLogProcessor);
			IIterator it = anyDataSet.getIterator();
			while (it.hasNext())
			{
				SingleRow row = it.moveNext();
				backupList.Add("I*" + row.getField("project"));
			}

			//personal list
			string[] tempBackupList = FileUtil.RetrieveFilesFromFolder(backup.PrivatePath(), "*" + backup.Extension());

			foreach (string backupName in tempBackupList)
			{
				string temp_name = FileUtil.ExtractFileName(backupName).Replace(backup.Extension(), "");

				if ((!backupList.Contains("I*" + temp_name)) && (!backupList.Contains("N*" + temp_name)))
				{
					backupList.Add("N*" + backup.removeLanguage(temp_name));
				}
			}

			if (this.isUserAdmin())
			{
				tempBackupList = FileUtil.RetrieveFilesFromFolder(backup.SharedPath(), "*" + backup.Extension());

				foreach (string backupName in tempBackupList)
				{
					string temp_name = FileUtil.ExtractFileName(backupName).Replace(backup.Extension(), "");

					if ((!backupList.Contains("I*" + temp_name)) && (!backupList.Contains("N*" + temp_name)))
					{
						backupList.Add("N*" + backup.removeLanguage(temp_name));
					}
				}
			}

			return backupList;
		}



		//--------------------------------------------------------
		// CREATE BACKUP
		//--------------------------------------------------------

		/**
		 * Generate the Project Backup
		 *
		 */
		public void createProjectBackup()
		{
			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("TITLE_CREATEBACKUP"), BlockPosition.Center);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			string projectName = this.getProjectName(Convert.ToInt32(this._context.ContextValue("valueid")));

			AnyDataSet anyDataSet = this.openProject(projectName);

			ArrayList files = new ArrayList();

			IIterator it = anyDataSet.getIterator();

			// Test if exists a previous versions. Old versions must be deleted.
			BackupFilenameProcessor backupProcessor = new BackupFilenameProcessor(projectName, this._context);
			if (FileUtil.Exists(backupProcessor))
			{
				FileUtil.DeleteFile(backupProcessor);
			}

			// Get the files and directories to be backuped.
			string tmpFile = null;
			while (it.hasNext())
			{
				SingleRow row = it.moveNext();
				string[] array;
				switch (row.getField("type"))
				{
					case "file":
						array = row.getFieldArray("object");
						files.AddRange(array);
						break;
					case "directory":
						array = row.getFieldArray("object");
						files.AddRange(array);
						break;
					case "setup":
						array = row.getFieldArray("object");
						tmpFile = this.getTempFileName(backupProcessor);
						FileUtil.QuickFileWrite(tmpFile, string.Join("\n", array));
						files.Add(tmpFile);
						break;
				}
			}

			// Start the process
			string tarFile = backupProcessor.FullQualifiedNameAndPath();

			//create the tar file
			Tar tar = new Tar(tarFile, Tar.Compression.Gzip);
			tar.AddFile(files);
			tar.create(this._context.SystemRootPath());

			//verify if the file was created, or not created
			string text;
			if (tar.Errors().Count > 0)
				text = this._myWords.Value("BACKUPNOTCREATED");
			else
				text = this._myWords.Value("BACKUPCREATED");

			block.addXmlnukeObject(new XmlnukeText(text, true));
			block.addXmlnukeObject(new XmlnukeBreakLine());
			block.addXmlnukeObject(new XmlnukeBreakLine());

			//if successfull, show the files in tar file
			if (tar.Errors().Count == 0)
			{
				tar.listFiles(block);
			}
			else
			{
				tar.showErrors(block);
			}

			// Delete the temp file
			if (tmpFile != null)
			{
				FileUtil.DeleteFile(tmpFile);
			}
		}

		protected string getTempFileName(BackupFilenameProcessor backupProcessor)
		{
			string tmpFile = backupProcessor.PathSuggested() + "." + backupProcessor.ToString() + ".tmp";
			tmpFile = tmpFile.Replace(this._context.SystemRootPath(), this._context.SystemRootPath() + "teste2" + FileUtil.Slash());
			return tmpFile;
		}

		/**
		 * View the Backup File
		 *
		 */
		public void viewBackup()
		{
			string backupName = this._context.ContextValue("bkp");

			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("TITLE_BACKUPCONTENTS", backupName), BlockPosition.Center);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			BackupFilenameProcessor backupProcessor = new BackupFilenameProcessor(backupName, this._context);
			backupProcessor.FilenameLocation = ForceFilenameLocation.UseWhereExists;

			XmlFormCollection form = new XmlFormCollection(this._context, "admin:backupmodule", "");
			form.addXmlnukeObject(new XmlInputHidden("op", OP_MANAGEBACKUP));
			form.addXmlnukeObject(new XmlInputHidden("action", AC_NEW));
			form.addXmlnukeObject(new XmlInputHidden("bkp", backupName));
			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(this._myWords.Value("BTN_BACKUPINSTALL", backupName), "btn");
			form.addXmlnukeObject(button);

			block.addXmlnukeObject(form);
			Tar tar = new Tar(backupProcessor.FullQualifiedNameAndPath());
			tar.list();
			tar.listFiles(block);
			block.addXmlnukeObject(form);
		}


		/**
		 * View the Backup File
		 *
		 */
		public void viewBackupLog()
		{
			string backupName = this._context.ContextValue("bkp");

			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("TITLE_BACKUPLOGCONTENTS", backupName), BlockPosition.Center);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			BackupFilenameProcessor backupProcessor = new BackupFilenameProcessor(backupName, this._context);
			backupProcessor.FilenameLocation = ForceFilenameLocation.UseWhereExists;

			XmlFormCollection form = new XmlFormCollection(this._context, "admin:backupmodule", "");
			form.addXmlnukeObject(new XmlInputHidden("op", OP_MANAGEBACKUP));
			form.addXmlnukeObject(new XmlInputHidden("action", AC_DELETE_CONF));
			form.addXmlnukeObject(new XmlInputHidden("bkp", backupName));
			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(this._myWords.Value("BTN_BACKUPUNINSTALL", backupName), "btn");
			form.addXmlnukeObject(button);

			block.addXmlnukeObject(form);

			AnydatasetBackupLogFilenameProcessor anyDataSetFile = new AnydatasetBackupLogFilenameProcessor("backup", this._context);
			AnyDataSet anyDataSet = new AnyDataSet(anyDataSetFile);

			//Delete project
			IteratorFilter itf = new IteratorFilter();
			itf.addRelation("project", Relation.Equal, backupName);
			IIterator it = anyDataSet.getIterator();
			while (it.hasNext())
			{
				SingleRow row = it.moveNext();

				string[] directories = row.getFieldArray("directory");
				foreach (string value in directories)
				{
					block.addXmlnukeObject(new XmlnukeText("Directory: " + value, true, false, false, true));
				}

				string[] files = row.getFieldArray("file");
				foreach (string value in files)
				{
					block.addXmlnukeObject(new XmlnukeText("File: " + value, true, false, false, true));
				}

				string[] setup = row.getFieldArray("setup");
				foreach (string value in setup)
				{
					block.addXmlnukeObject(new XmlnukeText("Setup: " + value, true, false, false, true));
				}
			}

			block.addXmlnukeObject(form);
		}


		/**
		 * Install the Tar Backup
		 *
		 */
		public void installBackup()
		{
			string backupName = this._context.ContextValue("bkp");

			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("TITLE_INSTALLBACKUP", backupName), BlockPosition.Center);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			//install directories and files in tar files
			BackupFilenameProcessor backupProcessor = new BackupFilenameProcessor(backupName, this._context);
			Tar tar = new Tar(backupProcessor.FullQualifiedNameAndPath(), Tar.Compression.Gzip);
			tar.extract("teste2");   // <<<===-------------------------------

			string text;
			if (tar.Errors().Count > 0)
				text = this._myWords.Value("BACKUP_INSTALL_ERROR");
			else
				text = this._myWords.Value("BACKUP_INSTALL_SUCCESS");

			block.addXmlnukeObject(new XmlnukeText(text, true));
			block.addXmlnukeObject(new XmlnukeBreakLine());
			block.addXmlnukeObject(new XmlnukeBreakLine());

			tar.showErrors(block);

			if (tar.Errors().Count == 0)
			{
				//set the status of backup installation in config file
				AnydatasetBackupLogFilenameProcessor anyDataSetLogFile = new AnydatasetBackupLogFilenameProcessor("backup", this._context);
				AnyDataSet anyDataSetLog = new AnyDataSet(anyDataSetLogFile);
				anyDataSetLog.appendRow();
				anyDataSetLog.addField("project", backupName);
				anyDataSetLog.addField("date", System.DateTime.Now.ToString("YYYY-MM-dd hh:mm:ss"));
				//save the directories installed
				ArrayList directories = tar.getDirectories();
				foreach (object directory in directories)
				{
					anyDataSetLog.addField("directory", directory.ToString());
				}
				//save the files installed
				ArrayList files = tar.getFiles();
				foreach (object file in files)
				{
					anyDataSetLog.addField("file", file.ToString());
				}

				string tmpFile = this.getTempFileName(backupProcessor);
				if (FileUtil.Exists(tmpFile))
				{
					AnyDataSet anydataRfl = null;
					SingleRow singleRowRfl = null;
					string[] lines = FileUtil.QuickFileRead(tmpFile).Split('\n');
					foreach (string line in lines)
					{
						anyDataSetLog.addField("setup", line);
						Match m = Regex.Match(line, LINESECTIONPATTERN);
						Match m1 = Regex.Match(line, LINEVALUEPATTERN);
						if (m.Success)
						{
							anydataRfl = this.getAnyDataByReflection(m.Groups[1].Value, m.Groups[2].Value, m.Groups[3].Value);
							singleRowRfl = this.getRowByReflection(anydataRfl, m.Groups[4].Value);
						}
						else if (m1.Success)
						{
							string[] fieldValue = line.Split('=');
							if (singleRowRfl != null)
							{
								singleRowRfl.AddField(fieldValue[0].Trim(), fieldValue[1].Trim());
							}
							else if (anydataRfl != null)
							{
								anydataRfl.addField(fieldValue[0].Trim(), fieldValue[1].Trim());
							}
							else
							{
								throw new Exception(this._myWords.Value("WRONGLINEDEF", line));
							}
							anydataRfl.Save();
						}
						else
						{
							throw new Exception(this._myWords.Value("SETUPERROR", line));
						}
					}
					FileUtil.DeleteFile(tmpFile);
				}

				anyDataSetLog.Save();
			}
		}


		protected AnyDataSet getAnyDataByReflection(string strProcessor, string singleName, string location)
		{
			Assembly asm = Assembly.GetExecutingAssembly();
			FilenameProcessor fileProcessorRfl =
				(FilenameProcessor)asm.CreateInstance
					("com.xmlnuke.processor." + strProcessor,
					 true,
					 BindingFlags.CreateInstance | BindingFlags.NonPublic | BindingFlags.Public | BindingFlags.Instance,
					 null,
					 new object[] { singleName, this._context },
					 null,
					 null
				  );

			if (fileProcessorRfl != null)
			{
				switch (location.ToLower())
				{
					case "private":
						fileProcessorRfl.FilenameLocation = ForceFilenameLocation.PrivatePath;
						break;
					case "shared":
						fileProcessorRfl.FilenameLocation = ForceFilenameLocation.SharedPath;
						break;
					default:
						fileProcessorRfl.FilenameLocation = ForceFilenameLocation.UseWhereExists;
						break;
				}

				return new AnyDataSet((AnydatasetBaseFilenameProcessor)fileProcessorRfl);
			}
			else
			{
				throw new Exception("Invalid " + strProcessor + " FilenameProcessor");
			}
		}

		protected SingleRow getRowByReflection(AnyDataSet anydataRfl, string strFilter)
		{
			string[] filter = strFilter.Split(';');

			IteratorFilter itf = new IteratorFilter();
			foreach (string value in filter)
			{
				string[] filterValue = value.Split('=');
				itf.addRelation(filterValue[0].Trim(), Relation.Equal, filterValue[1].Trim());
			}
			IIterator it = anydataRfl.getIterator(itf);
			if (it.hasNext())
			{
				return it.moveNext();
			}
			else
			{
				anydataRfl.appendRow();
				foreach (string value in filter)
				{
					string[] filterValue = value.Split('=');
					anydataRfl.addField(filterValue[0].Trim(), filterValue[1].Trim());
					anydataRfl.Save();
				}
				return null;
			}
		}

		/**
		 * Uninstall Backup
		 *
		 */
		public void uninstallBackup()
		{
			string projectName = this._context.ContextValue("bkp");

			XmlBlockCollection block = new XmlBlockCollection(this._myWords.Value("TITLE_UNINSTALLBACKUP", projectName), BlockPosition.Center);
			this.defaultXmlnukeDocument.addXmlnukeObject(block);

			AnydatasetBackupLogFilenameProcessor anyDataSetFile = new AnydatasetBackupLogFilenameProcessor("backup", this._context);
			AnyDataSet anyDataSet = new AnyDataSet(anyDataSetFile);

			bool error = false;

			//Delete project
			IteratorFilter itf = new IteratorFilter();
			itf.addRelation("project", Relation.Equal, projectName);
			IIterator it = anyDataSet.getIterator();
			while (it.hasNext())
			{
				SingleRow row = it.moveNext();

				block.addXmlnukeObject(new XmlnukeBreakLine());
				block.addXmlnukeObject(new XmlnukeText(this._myWords.Value("UNINSTALL_LOG"), true, false, false, true));

				string[] directories = row.getFieldArray("directory");
				string[] files = row.getFieldArray("file");
				string[] setup = row.getFieldArray("setup");

				//delete all installed files
				foreach (string fileItem in files)
				{
					string file = "teste2" + FileUtil.Slash() + FileUtil.AdjustSlashes(fileItem);
					block.addXmlnukeObject(new XmlnukeText("File: " + file));
					try
					{
						if (FileUtil.Exists(file))
						{
							FileUtil.DeleteFile(file);
							block.addXmlnukeObject(new XmlnukeText(" OK", false, false, false, true));
						}
						else
						{
							block.addXmlnukeObject(new XmlnukeText(" MISSING", true, false, false, true));
						}
					}
					catch (Exception e)
					{
						block.addXmlnukeObject(new XmlnukeText(" ERROR " + e.Message, true, false, false, true));
						error = true;
					}
				}


				//delete all installed directories
				Array.Sort(directories);
				Array.Reverse(directories);

				foreach (string directoryItem in directories)
				{
					string directory = "teste2" + FileUtil.Slash() + FileUtil.AdjustSlashes(directoryItem);
					block.addXmlnukeObject(new XmlnukeText("Directory: " + directory));
					try
					{
						if (FileUtil.Exists(directory))
						{
							FileUtil.ForceRemoveDirectories(directory);
							block.addXmlnukeObject(new XmlnukeText(" OK", false, false, false, true));
						}
						else
						{
							block.addXmlnukeObject(new XmlnukeText(" MISSING", true, false, false, true));
						}
					}
					catch (Exception e)
					{
						block.addXmlnukeObject(new XmlnukeText(" ERROR " + e.Message, true, false, false, true));
						error = true;
					}
				}

				if (setup.Length > 0)
				{
					AnyDataSet anydataRfl = null;
					SingleRow singleRowRfl = null;
					foreach (string line in setup)
					{
						Match m = Regex.Match(line, LINESECTIONPATTERN);
						Match m1 = Regex.Match(line, LINEVALUEPATTERN);
						if (m.Success)
						{
							anydataRfl = this.getAnyDataByReflection(m.Groups[1].Value, m.Groups[2].Value, m.Groups[3].Value);
							singleRowRfl = this.getRowByReflection(anydataRfl, m.Groups[4].Value);
							block.addXmlnukeObject(new XmlnukeText("Opened: " + m.Groups[1].Value + "(" + m.Groups[2].Value + ") in " + m.Groups[3].Value));
							block.addXmlnukeObject(new XmlnukeText(" OK", false, false, false, true));
						}
						else if (m1.Success)
						{
							string[] fieldValue = line.Split('=');
							block.addXmlnukeObject(new XmlnukeText("Delete Row: " + line));
							if (singleRowRfl != null)
							{
								singleRowRfl.removeFieldNameValue(fieldValue[0].Trim(), fieldValue[1].Trim());
								block.addXmlnukeObject(new XmlnukeText(" OK", false, false, false, true));
								anydataRfl.Save();
							}
							else
							{
								block.addXmlnukeObject(new XmlnukeText(" MISSING", false, false, false, true));
							}
						}
						else
						{
							throw new Exception(this._myWords.Value("SETUPERROR", line));
						}
					}
				}
			}

			if (!error)
			{
				anyDataSetFile = new AnydatasetBackupLogFilenameProcessor("backup", this._context);
				anyDataSet = new AnyDataSet(anyDataSetFile);
				it = anyDataSet.getIterator();
				while (it.hasNext())
				{
					SingleRow row = it.moveNext();
					if (row.getField("project") == projectName)
					{
						anyDataSet.removeRow(row.getDomObject());
						anyDataSet.Save();
					}
				}
			}

			block.addXmlnukeObject(new XmlnukeBreakLine());
		}
	}
}