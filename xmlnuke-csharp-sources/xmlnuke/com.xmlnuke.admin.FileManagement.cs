/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 * 
 *  This file is part of XMLNuke project. Visit http://www+xmlnuke+com
 *  for more information.
 *  
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE+  See the
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

using com.xmlnuke.module;
using com.xmlnuke.classes;
using com.xmlnuke.international;
using com.xmlnuke.processor;
using com.xmlnuke.anydataset;

namespace com.xmlnuke.admin
{
	public class FileManagement : NewBaseAdminModule
	{
		public FileManagement()
		{
		}

		public override bool useCache()
		{
			return false;
		}

		public override AccessLevel getAccessLevel()
		{
			return AccessLevel.CurrentSiteAndRole;
		}

		public override string[] getRole()
		{
			return new string[] { "MANAGER" };
		}


		protected XmlBlockCollection _block;


		public override IXmlnukeDocument CreatePage()
		{
			base.CreatePage();

			this._myWords = this.WordCollection();

			//menu itens
			this.defaultXmlnukeDocument.addMenuItem("module:admin.FileManagement", this._myWords.Value("FILEMANAGEMENT"), "");

			this._block = new XmlBlockCollection(this._myWords.Value("FILEMANAGEMENT"), BlockPosition.Center);
			this.setTitlePage(this._myWords.Value("TITLE"));
			this.setHelp(this._myWords.Value("DESCRIPTION"));
			this.defaultXmlnukeDocument.addXmlnukeObject(this._block);

			//get the current folder
			string root = this._context.ContextValue("folder");

			XmlFileBrowser browser = new XmlFileBrowser(root, this._action, this._context);

			//SET FILEBROWSER ACESS LEVEL
			AnydatasetSetupFilenameProcessor processor = new AnydatasetSetupFilenameProcessor("filemanagement", this._context);
			processor.FilenameLocation = ForceFilenameLocation.UseWhereExists;
			AnyDataSet anyDataSet = new AnyDataSet(processor);

            bool ignoreAdmin = false;

			IIterator it = anyDataSet.getIterator();
			while (it.hasNext())
			{
				SingleRow row = it.moveNext();
				switch (row.getField("type"))
				{
					case "INITIAL_DIR":
						string[] initial_dir = row.getFieldArray("value");
						browser.setSubFoldersPermitted(initial_dir);
						break;
					case "VALID_FILE_NEW":
						string[] file_new_list = row.getFieldArray("value");
						browser.setFileNewList(file_new_list);
						break;
					case "VALID_FILE_VIEW":
						string[] file_view_list = row.getFieldArray("value");
						browser.setFileViewList(file_view_list);
						break;
					case "VALID_FILE_EDIT":
						string[] file_edit_list = row.getFieldArray("value");
						browser.setFileEditList(file_edit_list);
						break;
					case "VALID_FILE_DELETE":
						string[] file_delete_list = row.getFieldArray("value");
						browser.setFileDeleteList(file_delete_list);
						break;
					case "VALID_FILE_UPLOAD":
						string[] file_upload_list = row.getFieldArray("value");
						browser.setFileUploadList(file_upload_list);
						break;
					case "VALID_FILE_MAX_UPLOAD":
						string file_max_upload = row.getField("value");
						browser.setFileMaxUpload(file_max_upload);
						break;
					case "PERMISSION":
						browser.setFileNew(row.getField("file_new") == "true");
						browser.setFileView(row.getField("file_view") == "true");
						browser.setFileEdit(row.getField("file_edit") == "true");
						browser.setFileDelete(row.getField("file_delete") == "true");
						browser.setFileUpload(row.getField("file_upload") == "true");
						browser.setFolderNew(row.getField("folder_new") == "true");
						browser.setFolderView(row.getField("folder_view") == "true");
						browser.setFolderEdit(row.getField("folder_edit") == "true");
						browser.setFolderDelete(row.getField("folder_delete") == "true");
                        ignoreAdmin = (row.getField("ignore_admin")=="true");
						break;
				}
			}

			if (this.isUserAdmin() && !ignoreAdmin)
				browser.setUserType(FileBrownserUserType.ADMIN);

			this._block.addXmlnukeObject(browser);

			return this.defaultXmlnukeDocument.generatePage();
		}
	}
}