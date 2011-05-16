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
using System.Xml;
using System.Collections;

using com.xmlnuke.module;
using com.xmlnuke.classes;
using com.xmlnuke.engine;
using com.xmlnuke.international;
using com.xmlnuke.util;
using com.xmlnuke.processor;
using com.xmlnuke.anydataset;

namespace com.xmlnuke.classes
{

	public enum FileBrownserUserType
	{
		ADMIN,
		USER
	}


	public enum FileBrowserEditListType
	{
		SUBFOLDER,
		FILE
	}

	public class XmlFileBrowser : XmlnukeDocumentObject
	{
		protected FileBrownserUserType _userType;

		protected string _currentFolder;

		protected string _currentSubFolder;

		protected string[] _subFoldersPermitted;

		protected bool _folderNew;

		protected bool _folderView;

		protected bool _folderEdit;

		protected bool _folderDelete;

		protected bool _fileNew;

		protected bool _fileView;

		protected bool _fileEdit;

		protected bool _fileDelete;

		protected bool _fileUpload;

		protected string[] _fileNewList;

		protected string[] _fileViewList;

		protected string[] _fileEditList;

		protected string[] _fileDeleteList;

		protected string[] _fileUploadList;

		protected int _fileMaxUpload;

		protected string _module;

		protected Context _context;

		protected XmlnukeSpanCollection _block;

		protected string _action;

		protected LanguageCollection _lang;

		/**
		 * True or false, if exist directory to show in editList
		 *
		 * @var Bool
		 */
		protected bool _existDirectory;

		/**
		 * Method Constructor
		 *
		 * @param String root
		 * @param Action action
		 * @param Context context
		 * @return XmlFileBrowser
		 */
		public XmlFileBrowser(string root, string action, Context context)
		{
			this._context = context;
			this._currentFolder = root;
			this._action = action;
			this._module = context.Module;

			//permission
			this._folderNew = false;
			this._folderView = false;
			this._folderEdit = false;
			this._folderDelete = false;
			this._fileNew = false;
			this._fileView = false;
			this._fileEdit = false;
			this._fileDelete = false;
			this._fileUpload = false;
			this._fileNewList = null; //<-
			this._fileViewList = null; //<-
			this._fileEditList = null; //<-
			this._fileDeleteList = null; //<-	
			this._fileUploadList = null; //<-
			this._fileMaxUpload = 0;
			this._subFoldersPermitted = null; // <-

			this._existDirectory = true;

			this._userType = FileBrownserUserType.USER;

			this._lang = LanguageFactory.GetLanguageCollection(this._context, LanguageFileTypes.OBJECT, ((object)this).GetType().FullName);
		}

		public void setUserType(FileBrownserUserType userType)
		{
			this._userType = userType;
		}


		/**
		 * Contains specific instructions to generate all XML informations. 
		 * This method is processed only one time. Usually is the last method processed.
		 *
		 * @param DOMNode current
		 */
		public override void generateObject(XmlNode current)
		{
			this._block = new XmlnukeSpanCollection();

			switch (this._action)
			{
				case "new":
					this.formNew();
					break;
				case "edit":
					this.formEdit();
					break;
				case "confirmedit":
					this.confirmEdit();
					break;
				case "view":
					this.view();
					break;
				case "delete":
					this.delete();
					break;
				case "uploadform":
					this.formUpload();
					break;
				case "uploadfile":
					this.uploadFile();
					break;
				case "create":
					this.createNew();
					break;
			}

			this._block.addXmlnukeObject(new XmlnukeBreakLine());
			this._block.addXmlnukeObject(new XmlnukeBreakLine());

			this.showFolder();

			this._block.generateObject(current);

			//FileUtil.Debug(this._currentFolder);
		}

		/**
		 * Show the Current Folder
		 *
		 */
		private void showFolder()
		{
			//ROOT FOLDER
			XmlAnchorCollection anchor = new XmlAnchorCollection("module:" + this._module);
			anchor.addXmlnukeObject(new XmlnukeText("Root"));
			this._block.addXmlnukeObject(anchor);

			if (this._currentFolder != "")
				this._block.addXmlnukeObject(new XmlnukeText(" / "));

			//TREE FOLDERS
			ArrayList treeFolders = this.getTreeFolder();

			//CURRENT SUB FOLDER
			anchor = new XmlAnchorCollection("module:" + this._module + "&folder=" + this._currentSubFolder);
			anchor.addXmlnukeObject(new XmlnukeText(this._currentSubFolder));
			this._block.addXmlnukeObject(anchor);

			if (this._currentFolder != "")
				this._block.addXmlnukeObject(new XmlnukeText(" / "));

			string fullFolder = "";
			foreach (object folder in treeFolders)
			{
				if (fullFolder == "")
					fullFolder = this._currentSubFolder + FileUtil.Slash() + folder.ToString();
				else
					fullFolder += FileUtil.Slash() + folder;

				anchor = new XmlAnchorCollection("module:" + this._module + "&folder=" + fullFolder);
				anchor.addXmlnukeObject(new XmlnukeText(folder.ToString()));
				this._block.addXmlnukeObject(anchor);
				this._block.addXmlnukeObject(new XmlnukeText(" " + FileUtil.Slash() + " "));
			}

			this._block.addXmlnukeObject(new XmlnukeBreakLine());
			this._block.addXmlnukeObject(new XmlnukeBreakLine());


			//EDIT LIST SUB FOLDERS
			ArrayList subFolders = this.getSubFoldersFromCurrent();

			if (this._existDirectory)
				this.showEditlist(subFolders, this._lang.Value("SUBFOLDERS"), this._lang.Value("SUBFOLDERS"), FileBrowserEditListType.SUBFOLDER);

			this._block.addXmlnukeObject(new XmlnukeBreakLine());
			this._block.addXmlnukeObject(new XmlnukeBreakLine());


			//EDIT LIST FILES
			if ((this._currentFolder != "") || (this._userType == FileBrownserUserType.ADMIN))
			{
				ArrayList files = new ArrayList();
				ArrayList tempFiles = this.getFilesFromFolder();

				if (tempFiles != null)
				{
					foreach (object file in tempFiles)
						if (this.extensionIsPermitted(this._fileViewList, file.ToString()))
							files.Add(file);
				}

				if (this._existDirectory)
					this.showEditlist(files, this._lang.Value("FILES"), this._lang.Value("FILES"), FileBrowserEditListType.FILE);
			}
		}

		/**
		 * Show the EditList of Sub Folders or files of currenct folder
		 * 
		 * @param Array values
		 * @param String title
		 * @param String field
		 * @param FileBrowserEditListType type
		 */
		private void showEditlist(ArrayList values, string title, string field, FileBrowserEditListType type)
		{
			if (this._currentFolder != "")
			{
				for (int i = 0; i < values.Count; i++)
					values[i] = this.getSingleName(values[i].ToString());
			}

			ArrayDataSet arrayDs = new ArrayDataSet(values);
			IIterator arrayIt = arrayDs.getIterator();

			bool newD = false;
			bool view = false;
			bool edit = false;
			bool delete = false;

			if (type == FileBrowserEditListType.FILE)
			{
				newD = this.getFileNew();
				view = this.getFileView();
				edit = this.getFileEdit();
				delete = this.getFileDelete();
			}
			else
			{
				newD = this.getFolderNew();
				view = this.getFolderView();
				edit = this.getFolderEdit();
				delete = this.getFolderDelete();
			}
			bool readOnly = (!newD && !view && !edit && !delete);

			XmlEditList editlist = new XmlEditList(this._context, this._currentFolder, "module:" + this._module, newD, view, edit, delete);
			editlist.addParameter("type", type.ToString());
			editlist.addParameter("folder", this._currentFolder);
			editlist.setDataSource(arrayIt);

			EditListField editlistfield = new EditListField(true);
			editlistfield.fieldData = "id";
			editlistfield.editlistName = "#";
			editlist.addEditListField(editlistfield);

			editlistfield = new EditListField(true);
			editlistfield.fieldData = "value";
			editlistfield.editlistName = field;
			editlist.addEditListField(editlistfield);

			if ((type == FileBrowserEditListType.FILE) && this.getFileUpload())
			{
				CustomButtons customButton = new CustomButtons();
				customButton.action = "uploadform";
				customButton.enabled = true;
				customButton.alternateText = "UPLOAD";
				XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.MODULE, "com.xmlnuke.admin.FileManagement");
				url.addParam("type", type.ToString());
				url.addParam("folder", this._currentFolder);
				customButton.url = url.getUrlFull(this._context);
				customButton.icon = "common/editlist/ic_custom.gif";
				customButton.multiple = MultipleSelectType.NONE;
				editlist.setCustomButton(0, customButton);
				readOnly = false;
			}

			if (readOnly)
			{
				editlist.setReadOnly(true);
			}

			this._block.addXmlnukeObject(editlist);
		}

		/**
		 * Get the Tree of Current Folder
		 *
		 * @return Array
		 */
		private ArrayList getTreeFolder()
		{
			//get all tree folder of current before folder until them
			string[] tempFolders = this._currentFolder.Split(FileUtil.Slash()[0]);

			//verify the subfolders that was found in current folder
			string fullFolder = "";
			ArrayList folders = new ArrayList();
			foreach (string folder in tempFolders)
			{
				if (fullFolder == "")
					fullFolder = folder;
				else
					fullFolder += FileUtil.Slash() + folder;

				//verify if the fullfolder is a Permitted Sub Folder
				bool found = false;
				foreach (string folderPermitted in this.getSubFoldersPermitted())
				{
					if (fullFolder == folderPermitted)
					{
						this._currentSubFolder = folderPermitted;
						found = true;
						break;
					}
				}

				//if found the Permitted sub folder
				if (found)
				{
					folders = new ArrayList();
				}

				if (!found)
				{
					folders.Add(folder);
				}
			}

			return folders;
		}

		/**
		 * Get All The Sub Folders of Folders
		 *
		 * @return Array
		 */
		private ArrayList getSubFoldersFromCurrent()
		{
			ArrayList directories = new ArrayList();
			//try get the subfolders
			try
			{
				//get the subfolders
				string[] tempDirectories;
				if (this._currentFolder != "")
				{
					tempDirectories = FileUtil.RetrieveSubFolders(this.realPathName(this._currentFolder));
				}
				else
				{
					tempDirectories = this.getSubFoldersPermitted();
				}

				foreach (string folder in tempDirectories)
				{
					string relFolder = this.relativePathName(folder);
					//if the folder not beginner with ., get them
					if (relFolder[0] != '.')
						directories.Add(relFolder);
				}
			}
			catch (Exception e) //if was ocurred an error
			{
				this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

				this._existDirectory = false;

				this._block.addXmlnukeObject(new XmlnukeBreakLine());
				this._block.addXmlnukeObject(new XmlnukeBreakLine());
			}

			return directories;
		}

		/**
		 * Verify if folder is Permitted subfolders from root folder
		 *
		 * @param String folder
		 * @return Bool
		 */
		private bool isSubFolderPermitted(string folder)
		{
			if (this._userType == FileBrownserUserType.ADMIN)
				return true;

			bool found = false;
			foreach (string list in this._subFoldersPermitted)
			{
				string folderPermitted = /* this._root + */ FileUtil.Slash() + list;
				if (folder == folderPermitted)
				{
					found = true;
					break;
				}
			}

			return found;
		}

		/**
		 * Get All The Files of Folder
		 *
		 * @return Array
		 */
		private ArrayList getFilesFromFolder()
		{
			ArrayList files = new ArrayList();
			//FileUtil.Debug("GetSubFolders: "+ this._currentFolder);
			try
			{
				string folder = this._currentFolder;
				if (folder == "")
				{
					if (this._userType == FileBrownserUserType.ADMIN)
					{
						folder = "~"; //this._context.SystemRootPath();
					}
					else
					{
						return new ArrayList();
					}
				}
				string[] tempFiles = FileUtil.RetrieveFilesFromFolder(this.realPathName(folder), "*");

				//remove the files that was blocked for user
				foreach (string file in tempFiles)
				{
					if (this.extensionIsPermitted(this._fileViewList, file))
					{
						if (this._currentFolder == "")
						{
							files.Add(System.IO.Path.GetFileName(file));
						}
						else
						{
							files.Add(file);
						}
					}
				}
			}
			catch (Exception e)
			{
				this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

				this._block.addXmlnukeObject(new XmlnukeBreakLine());
				this._block.addXmlnukeObject(new XmlnukeBreakLine());
			}

			return files;
		}


		/**
		 * Sow a new form to create a new form
		 *
		 */
		private void formNew()
		{
			if (this.getFileBrowserEditListType() == FileBrowserEditListType.SUBFOLDER)
			{
				if ((this.getFolderNew()) && (this._currentFolder != ""))
				{
					XmlFormCollection form = new XmlFormCollection(this._context, "module:" + this._module, this._lang.Value("NEWDIRECTORY"));
					form.addXmlnukeObject(new XmlInputHidden("action", "create"));
					form.addXmlnukeObject(new XmlInputHidden("type", this._context.ContextValue("type")));
					form.addXmlnukeObject(new XmlInputHidden("folder", this._currentFolder));

					XmlInputTextBox textBox = new XmlInputTextBox(this._lang.Value("TXT_NAME"), "name", "");
					textBox.setRequired(true);
					form.addXmlnukeObject(textBox);

					XmlInputButtons button = new XmlInputButtons();
					button.addSubmit(this._lang.Value("CONFIRM"), "");
					form.addXmlnukeObject(button);

					this._block.addXmlnukeObject(form);
				}
				else
				{
					this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("NEWFOLDERNOTPERMITTED"), true));
				}
			}
			else//FILE
			{
				if (this.getFileNew())
				{
					XmlFormCollection form = new XmlFormCollection(this._context, "module:" + this._module, this._lang.Value("NEWFILE"));

					form.addXmlnukeObject(new XmlInputHidden("action", "create"));
					form.addXmlnukeObject(new XmlInputHidden("type", this._context.ContextValue("type")));
					form.addXmlnukeObject(new XmlInputHidden("folder", this._currentFolder));

					XmlInputTextBox textBox = new XmlInputTextBox(this._lang.Value("TXT_NAME"), "filename", "", 40);
					textBox.setRequired(true);
					form.addXmlnukeObject(textBox);

					XmlInputMemo textMemo = new XmlInputMemo(this._lang.Value("FILE"), "filecontent", "");
					textMemo.setSize(70, 20);
					form.addXmlnukeObject(textMemo);

					XmlInputButtons button = new XmlInputButtons();
					button.addSubmit(this._lang.Value("CONFIRM"), "");
					form.addXmlnukeObject(button);

					this._block.addXmlnukeObject(form);
				}
				else
				{
					this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("NEWFILENOTPERMITTED"), true));
				}
			}
		}

		/**
		 * Show the Form Upload
		 *
		 */
		private void formUpload()
		{
			if (this.getFileUpload())
			{
				XmlFormCollection form = new XmlFormCollection(this._context, "module:" + this._module, this._lang.Value("UPLOAD"));

				form.addXmlnukeObject(new XmlInputHidden("action", "uploadfile"));
				form.addXmlnukeObject(new XmlInputHidden("folder", this._currentFolder));

				XmlInputFile file = new XmlInputFile(this._lang.Value("FILE"), "form_file");
				form.addXmlnukeObject(file);

				XmlInputButtons button = new XmlInputButtons();
				button.addSubmit(this._lang.Value("SEND"), "");
				form.addXmlnukeObject(button);

				this._block.addXmlnukeObject(form);
			}
			else
			{
				this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("UPLOADNOTPERMITTED"), true));
			}
		}

		/**
		 * Upload a File
		 *
		 */
		private void uploadFile()
		{
			string dir = this._currentFolder + FileUtil.Slash();

			UploadFilenameProcessor fileProcessor = new UploadFilenameProcessor("", this._context);
			fileProcessor.PathForced = this._context.SystemRootPath() + dir;

			ArrayList result = this._context.processUpload(fileProcessor, false);

			//if (this.extensionIsPermitted(this._fileUploadList, filename))  
			if (result.Count > 0)
			{
				this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("FILE") + " " + result[0].ToString() + " " + this._lang.Value("SENDED")));
			}
			else
			{
				this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("EXTENSIONNOTPERMITTED"), true));
			}
		}

		/**
		 * Create a new subfolder or a new file
		 *
		 */
		private void createNew()
		{
			//string id = this._context.ContextValue("valueid");
			string name = this._context.ContextValue("name");

			if (this.getFileBrowserEditListType() == FileBrowserEditListType.SUBFOLDER) //SUBFOLDERS
			{
				string folder = this._currentFolder + FileUtil.Slash() + name;
				try
				{
					FileUtil.ForceDirectories(folder);
				}
				catch (Exception e)
				{
					this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

					this._block.addXmlnukeObject(new XmlnukeBreakLine());
					this._block.addXmlnukeObject(new XmlnukeBreakLine());
				}
			}
			else //CREATE FILE
			{
				string filename = this._context.ContextValue("filename");
				string filecontent = this._context.ContextValue("filecontent");
				string filePath = this._currentFolder + FileUtil.Slash();

				try
				{
					FileUtil.QuickFileWrite(filePath + filename, filecontent);
				}
				catch (Exception e)
				{
					this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

					this._block.addXmlnukeObject(new XmlnukeBreakLine());
					this._block.addXmlnukeObject(new XmlnukeBreakLine());
				}
			}
		}

		/**
		 * Show the Form Edit
		 *
		 */
		private void formEdit()
		{
			int id;
			try
			{
				id = Convert.ToInt32(this._context.ContextValue("valueid"));
			}
			catch
			{
				id = 0;
			}

			if (this.getFileBrowserEditListType() == FileBrowserEditListType.SUBFOLDER)
			{
				if ((this.getFolderEdit()) && (this._currentFolder != ""))
				{
					XmlFormCollection form = new XmlFormCollection(this._context, "module:" + this._module, this._lang.Value("FOLDEREDIT"));

					form.addXmlnukeObject(new XmlInputHidden("action", "confirmedit"));
					form.addXmlnukeObject(new XmlInputHidden("type", FileBrowserEditListType.SUBFOLDER.ToString()));
					form.addXmlnukeObject(new XmlInputHidden("folder", this._currentFolder));

					ArrayList subfolders = this.getSubFoldersFromCurrent();
					string subfolder = this.getSingleName(subfolders[id].ToString());

					XmlInputTextBox textBox = new XmlInputTextBox(this._lang.Value("NEWNAME"), "new_name", subfolder);
					textBox.setRequired(true);
					form.addXmlnukeObject(textBox);

					form.addXmlnukeObject(new XmlInputHidden("old_name", subfolder));

					XmlInputButtons button = new XmlInputButtons();
					button.addSubmit(this._lang.Value("TXT_UPDATE"), "");
					form.addXmlnukeObject(button);

					this._block.addXmlnukeObject(form);
				}
				else
				{
					this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("EDITFOLDERNOTPERMITTED"), true));
				}
			}
			else
			{
				if (this.getFileEdit())
				{
					ArrayList files = this.getFilesFromFolder();

					string filesrc = files[id].ToString();

					if (this.extensionIsPermitted(this._fileEditList, filesrc))
					{
						XmlFormCollection form = new XmlFormCollection(this._context, "module:" + this._module, this._lang.Value("EDITFILE"));

						form.addXmlnukeObject(new XmlInputHidden("action", "confirmedit"));
						form.addXmlnukeObject(new XmlInputHidden("type", this._context.ContextValue("type")));
						form.addXmlnukeObject(new XmlInputHidden("folder", this._currentFolder));

						string filename = this.getSingleName(filesrc);
						XmlInputTextBox textBox = new XmlInputTextBox(this._lang.Value("TXT_NAME"), "new_name", filename, 40);
						textBox.setRequired(true);
						form.addXmlnukeObject(textBox);

						form.addXmlnukeObject(new XmlInputHidden("old_name", filename));

						string filecontent = "";
						try
						{
							filecontent = FileUtil.QuickFileRead(filesrc);
						}
						catch (Exception e)
						{
							this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

							this._block.addXmlnukeObject(new XmlnukeBreakLine());
							this._block.addXmlnukeObject(new XmlnukeBreakLine());
						}

						XmlInputMemo textMemo = new XmlInputMemo("", "filecontent", filecontent);
						textMemo.setSize(110, 20);
						form.addXmlnukeObject(textMemo);

						XmlInputButtons button = new XmlInputButtons();
						button.addSubmit(this._lang.Value("UPDATE"), "");
						form.addXmlnukeObject(button);

						this._block.addXmlnukeObject(form);
					}
					else
					{
						this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("EXTENSIONEDITNOTPERMITTED"), true));
					}
				}
				else
				{
					this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("EDITFILENOTPERMITTED"), true));
				}
			}
		}

		/**
		 * Show the confirm edit
		 *
		 */
		private void confirmEdit()
		{
			string old_name = this._currentFolder + FileUtil.Slash() + this._context.ContextValue("old_name");
			old_name = this.realPathName(old_name);

			string new_name = System.IO.Path.GetDirectoryName(old_name) + FileUtil.Slash() + this._context.ContextValue("new_name");

			string filecontent = this._context.ContextValue("filecontent");

			try
			{
				if (this.getFileBrowserEditListType() == FileBrowserEditListType.SUBFOLDER) //SUBFOLDER
				{
					FileUtil.RenameDirectory(old_name, new_name);
				}
				else //FILE
				{
					if (new_name != old_name)
					{
						FileUtil.RenameFile(old_name, new_name);
					}
					FileUtil.QuickFileWrite(new_name, filecontent);
				}
			}
			catch (Exception e)
			{
				this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

				this._block.addXmlnukeObject(new XmlnukeBreakLine());
				this._block.addXmlnukeObject(new XmlnukeBreakLine());
			}
		}

		/**
		 * View the SubFolder
		 *
		 */
		private void view()
		{
			int id;
			try
			{
				id = Convert.ToInt32(this._context.ContextValue("valueid"));
			}
			catch
			{
				id = 0;
			}

			if (this.getFileBrowserEditListType() == FileBrowserEditListType.SUBFOLDER)
			{
				if (this.getFolderView())
				{
					ArrayList dir = this.getSubFoldersFromCurrent();
					this._currentFolder = dir[id].ToString();
				}
				else
				{
					this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("VIEWFOLDERNOTPERMITTED"), true));
				}
			}
			else
			{
				if (this.getFileView())
				{
					ArrayList files = this.getFilesFromFolder();
					string filesrc = files[id].ToString();

					string filename = this.getSingleName(filesrc);

					XmlInputTextBox textBox = new XmlInputTextBox(this._lang.Value("TXT_NAME"), "filename", filename, 60);
					textBox.setReadOnly(true);
					this._block.addXmlnukeObject(textBox);
					this._block.addXmlnukeObject(new XmlnukeBreakLine());

					bool img = false;
					string ext = this.getExtension(filename);
					switch (ext.ToLower())
					{
						case ".jpg":
						case ".bmp":
						case ".png":
						case ".gif":
						case ".jpeg":
							//FileUtil.Debug("sim");
							img = true;
							break;
					}

					if (img)
					{
						this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("IMAGE") + ":", false, false, false, true));
						XmlnukeImage image = new XmlnukeImage((this._currentFolder + FileUtil.Slash() + filename).Replace("\\", "/"));
						this._block.addXmlnukeObject(image);
					}
					else
					{
						string filecontent = "";
						try
						{
							filecontent = FileUtil.QuickFileRead(filesrc);
						}
						catch (Exception e)
						{
							this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

							this._block.addXmlnukeObject(new XmlnukeBreakLine());
							this._block.addXmlnukeObject(new XmlnukeBreakLine());
						}

						XmlInputMemo textMemo = new XmlInputMemo(this._lang.Value("FILE"), "filecontent", filecontent);
						textMemo.setSize(110, 20);
						this._block.addXmlnukeObject(textMemo);
					}
				}
				else
				{
					this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("VIEWFILENOTPERMITTED"), true));
				}
			}
		}

		/**
		 * Get single name of folder or file
		 *
		 * @param String src_name
		 * @return String
		 */
		private string getSingleName(string filename)
		{
			return System.IO.Path.GetFileName(filename);
		}

		/**
		 * Get extension of filename
		 *
		 * @param String filename
		 * @return String
		 */
		private string getExtension(string filename)
		{
			return System.IO.Path.GetExtension(filename);
		}

		/**
		 * Delete a Folder or File
		 *
		 */
		private void delete()
		{
			int id;
			try
			{
				id = Convert.ToInt32(this._context.ContextValue("valueid"));
			}
			catch
			{
				id = 0;
			}

			if (this.getFileBrowserEditListType() == FileBrowserEditListType.SUBFOLDER) //SUBFOLDERS
			{
				if ((this.getFolderDelete()) && (this._currentFolder != ""))
				{
					ArrayList folders = this.getSubFoldersFromCurrent();
					try
					{
						FileUtil.ForceRemoveDirectories(folders[id].ToString());
					}
					catch (Exception e)
					{
						this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

						this._block.addXmlnukeObject(new XmlnukeBreakLine());
						this._block.addXmlnukeObject(new XmlnukeBreakLine());
					}
				}
				else
				{
					this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("DELETEFOLDERNOTPERMITTED"), true));
				}
			}
			else //FILE
			{
				if (this.getFileDelete())
				{
					ArrayList files = this.getFilesFromFolder();
					try
					{
						FileUtil.DeleteFile(files[id].ToString());
					}
					catch (Exception e)
					{
						this._block.addXmlnukeObject(new XmlnukeText(e.Message, true));

						this._block.addXmlnukeObject(new XmlnukeBreakLine());
						this._block.addXmlnukeObject(new XmlnukeBreakLine());
					}
				}
				else
				{
					this._block.addXmlnukeObject(new XmlnukeText(this._lang.Value("DELETEFILENOTPERMITTED"), true));
				}
			}
		}

		/**
		 * Verfify if extension is a Permitted extension
		 *
		 * @param Array PermittedExtensionList
		 * @param String filename
		 * @return Bool
		 */
		private bool extensionIsPermitted(string[] PermittedExtensionList, string filename)
		{
			if (this._userType == FileBrownserUserType.ADMIN)
				return true;

			string ext = this.getExtension(filename);

			foreach (string extension in PermittedExtensionList)
			{
				if (extension == ext)
					return true;
			}

			return false;
		}



		//METHODS SETS AND GETS

		//CREATE NEW FOLDER
		/**
		 * Set if is Permitted create a new folder
		 *
		 * @param Bool folderNew
		 */
		public void setFolderNew(bool folderNew)
		{
			this._folderNew = folderNew;
		}

		/**
		 * Get if is Permitted create a new folder
		 *
		 * @return Bool
		 */
		public bool getFolderNew()
		{
			return this._folderNew || (this._userType == FileBrownserUserType.ADMIN);
		}


		//VIEW FOLDERS
		/**
		 * Set if is Permitted View a folder
		 *
		 * @param Bool folderView
		 */
		public void setFolderView(bool folderView)
		{
			this._folderView = folderView;
		}

		/**
		 * Get if is Permitted View a folder
		 *
		 * @return Bool
		 */
		public bool getFolderView()
		{
			return this._folderView || (this._userType == FileBrownserUserType.ADMIN);
		}

		//Edit FOLDERS
		/**
		 * Set if is Permitted Edit a folder
		 *
		 * @param Bool folderEdit
		 */
		public void setFolderEdit(bool folderEdit)
		{
			this._folderEdit = folderEdit;
		}

		/**
		 * Get if is Permitted Edit a folder
		 *
		 * @return Bool
		 */
		public bool getFolderEdit()
		{
			return this._folderEdit || (this._userType == FileBrownserUserType.ADMIN);
		}

		//Delete folderS
		/**
		 * Set if is Permitted Delete a folder
		 *
		 * @param Bool folderDelete
		 */
		public void setFolderDelete(bool folderDelete)
		{
			this._folderDelete = folderDelete;
		}

		/**
		 * Get if is Permitted Delete a folder
		 *
		 * @return Bool
		 */
		public bool getFolderDelete()
		{
			return this._folderDelete || (this._userType == FileBrownserUserType.ADMIN);
		}


		//CREATE NEW File
		/**
		 * Set if is Permitted create a new File
		 *
		 * @param Bool fileNew
		 */
		public void setFileNew(bool fileNew)
		{
			this._fileNew = fileNew;
		}

		/**
		 * Get if is Permitted create a new file
		 *
		 * @return Bool
		 */
		public bool getFileNew()
		{
			return this._fileNew || (this._userType == FileBrownserUserType.ADMIN);
		}


		//VIEW FileS
		/**
		 * Set if is Permitted View a File
		 *
		 * @param Bool fileView
		 */
		public void setFileView(bool fileView)
		{
			this._fileView = fileView;
		}

		/**
		 * Get if is Permitted View a File
		 *
		 * @return Bool
		 */
		public bool getFileView()
		{
			return this._fileView || (this._userType == FileBrownserUserType.ADMIN);
		}

		//Edit FileS
		/**
		 * Set if is Permitted Edit a File
		 *
		 * @param Bool fileEdit
		 */
		public void setFileEdit(bool fileEdit)
		{
			this._fileEdit = fileEdit;
		}

		/**
		 * Get if is Permitted Edit a File
		 *
		 * @return Bool
		 */
		public bool getFileEdit()
		{
			return this._fileEdit || (this._userType == FileBrownserUserType.ADMIN);
		}

		//Delete FileS
		/**
		 * Set if is Permitted Delete a File
		 *
		 * @param Bool FileDelete
		 */
		public void setFileDelete(bool fileDelete)
		{
			this._fileDelete = fileDelete;
		}

		/**
		 * Get if is Permitted Delete a File
		 *
		 * @return Bool
		 */
		public bool getFileDelete()
		{
			return this._fileDelete || (this._userType == FileBrownserUserType.ADMIN);
		}

		//Upload FileS
		/**
		 * Set if is Permitted upload a File
		 *
		 * @param Bool fileUpload
		 */
		public void setFileUpload(bool fileUpload)
		{
			this._fileUpload = fileUpload;
		}

		/**
		 * Get if is Permitted Upload a File
		 *
		 * @return Bool
		 */
		public bool getFileUpload()
		{
			return this._fileUpload || (this._userType == FileBrownserUserType.ADMIN);
		}



		//Arrays

		//SubFolders Permitted
		/**
		 * Set teh subFolders Permitted in root folder
		 *
		 * @param Array subFoldersPermitted
		 */
		public void setSubFoldersPermitted(string[] subFoldersPermitted)
		{
			this._subFoldersPermitted = subFoldersPermitted;
			for (int i = 0; i < subFoldersPermitted.Length; i++)
			{
				this._subFoldersPermitted[i] = FileUtil.AdjustSlashes(subFoldersPermitted[i]).Replace(FileUtil.Slash(), "^");
			}
		}

		/**
		 * Get the subfolders Permitted in root folder
		 *
		 * @return Array
		 */
		public string[] getSubFoldersPermitted()
		{
			if (this._userType == FileBrownserUserType.ADMIN)
			{
				string[] folders = FileUtil.RetrieveSubFolders(this._context.SystemRootPath());
				for (int i = 0; i < folders.Length; i++)
				{
					folders[i] = System.IO.Path.GetFileName(folders[i]);
				}
				return folders;
			}
			else
			{
				return this._subFoldersPermitted;
			}
		}

		//File New List
		/**
		 * Set if is Permitted Delete a File
		 *
		 * @param Array FileDelete
		 */
		public void setFileNewList(string[] fileNewList)
		{
			this._fileNewList = fileNewList;
		}

		/**
		 * Get if is Permitted Delete a File
		 *
		 * @return Array
		 */
		public string[] getFileNewList()
		{
			return this._fileNewList;
		}


		//File New List	
		/**
		 * Set if is Permitted Delete a File
		 *
		 * @param Array FileDelete
		 */
		public void setFileViewList(string[] fileViewList)
		{
			this._fileViewList = fileViewList;
		}

		/**
		 * Get if is Permitted Delete a File
		 *
		 * @return Array
		 */
		public string[] getFileViewList()
		{
			return this._fileViewList;
		}

		//File Edit List	
		/**
		 * Set if is Permitted Delete a File
		 *
		 * @param Array FileDelete
		 */
		public void setFileEditList(string[] fileEditList)
		{
			this._fileEditList = fileEditList;
		}

		/**
		 * Get if is Permitted Delete a File
		 *
		 * @return Array
		 */
		public string[] getFileEditList()
		{
			return this._fileEditList;
		}

		//File Delete List	
		/**
		 * Set if is Permitted Delete a File
		 *
		 * @param Array FileDelete
		 */
		public void setFileDeleteList(string[] fileDeleteList)
		{
			this._fileDeleteList = fileDeleteList;
		}

		/**
		 * Get if is Permitted Delete a File
		 *
		 * @return Array
		 */
		public string[] getFileDeleteList()
		{
			return this._fileDeleteList;
		}

		//File Upload List	
		/**
		 * Set if is Permitted Upload a File
		 *
		 * @param Array FileUpload
		 */
		public void setFileUploadList(string[] fileUploadList)
		{
			this._fileUploadList = fileUploadList;
		}

		/**
		 * Get if is Permitted Upload a File
		 *
		 * @return Array
		 */
		public string[] getFileUploadList()
		{
			return this._fileUploadList;
		}

		//File Max Upload	
		/**
		 * Set the max size for upload file
		 *
		 * @param Int fileMaxUpload
		 */
		public void setFileMaxUpload(int fileMaxUpload)
		{
			this._fileMaxUpload = fileMaxUpload;
		}

		public void setFileMaxUpload(string fileMaxUpload)
		{
			try
			{
				this.setFileMaxUpload(Convert.ToInt32(fileMaxUpload));
			}
			catch
			{
				this.setFileMaxUpload(2048);
			}
		}

		/**
		 * Get the max size for upload file
		 *
		 * @return Int
		 */
		public int getFileMaxUpload()
		{
			return this._fileMaxUpload;
		}

		/**
		 * Get the real path name from a specified PATH
		 *
		 * @param string path
		 */
		private string realPathName(string path)
		{
			if (path.IndexOf(this._context.SystemRootPath()) == -1)
			{
				return System.Web.HttpContext.Current.Server.MapPath(path.Replace("\\", "/")).Replace("^", "/");
			}
			else
			{
				return path;
			}
		}

		private string relativePathName(string path)
		{
			string root = this._context.SystemRootPath();
			if (path.IndexOf(root) != -1)
			{
				return path.Substring(root.Length);
			}
			else
			{
				return path;
			}
		}

		protected FileBrowserEditListType getFileBrowserEditListType()
		{
			string type = this._context.ContextValue("type");
			if (type.ToUpper() == "SUBFOLDER")
				return FileBrowserEditListType.SUBFOLDER;
			else
				return FileBrowserEditListType.FILE;
		}
	}
}