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
using System.Collections.Specialized;
using System.Xml;
using com.xmlnuke.engine;
using com.xmlnuke.anydataset;
using com.xmlnuke.processor;

namespace com.xmlnuke.classes
{

	/// <summary>
	/// ProcessPageStateDB is class to make easy View, Edit, Delete and Update single tables from relational databases like MySQL, PostGres, Oracle, SQLServer and others.
	/// To use this class is necessary define the fields are used.
	/// </summary>
	/// <example>
	/// <code>
	/// ProcessPageField fieldPage;
	/// ProcessPageFields pageFields = new ProcessPageFields();
	/// 
	/// fieldPage = new ProcessPageField(true);
	/// fieldPage.fieldName = "field1";
	/// fieldPage.key = true;             // Only one key field.
	/// fieldPage.dataType = INPUTTYPE.NUMBER;
	/// fieldPage.fieldCaption = "Key Field";
	/// fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
	/// fieldPage.visibleInList = true;   // This field must be visible and in the FIRST position
	/// fieldPage.editable = true;        // If the Key field is AutoIncrement set this property 
	///                                      // to false.
	/// fieldPage.required = true;
	/// pageFields.addProcessPageField(fieldPage);
	/// 
	/// fieldPage = new ProcessPageField(true);
	/// fieldPage.fieldName = "field2";
	/// fieldPage.key = false;
	/// fieldPage.dataType = INPUTTYPE.TEXT;
	/// fieldPage.fieldCaption = "Caption of Field 2";
	/// fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
	/// fieldPage.visibleInList = true;
	/// fieldPage.editable = true;
	/// fieldPage.required = true;
	/// pageFields.addProcessPageField(fieldPage);
	/// 
	/// fieldPage = new ProcessPageField(true);
	/// fieldPage.fieldName = "field3";
	/// fieldPage.key = false;
	/// fieldPage.dataType = INPUTTYPE.DATE;
	/// fieldPage.fieldCaption = "Caption of Field 3";
	/// fieldPage.fieldXmlInput = XmlInputObjectType.TEXTBOX;
	/// fieldPage.visibleInList = true;
	/// fieldPage.editable = true;
	/// fieldPage.required = true;
	/// pageFields.addProcessPageField(fieldPage);
	/// </code>
	/// After defined all the fields the user must create the class, like this:
	/// <code>
	/// // Create a Block. 
	/// XmlBlockCollection block = new XmlBlockCollection("ProcessPageStateDB Example", BlockPosition.Center);
	/// 
	/// // Create the class passing all relevant paramenters.
	/// ProcessPageStateDB processPage = new ProcessPageStateDB(
	///                   this._context, 
	///                   pageFields, 
	///                   "Editing Table 'mytable'", 
	///                   "module:sample", 
	///                   null, 
	///                   "mytable", 
	///                   "myconnection");
	/// block1.addXmlnukeObject(processPage);
	/// 
	/// // Create a XmlnukeDocument and generate XML in the CreatePage method.
	/// XmlnukeDocument xmlnukeDoc = new XmlnukeDocument("Titulo da Pgina", "Abstract Dessa Pgina");
	/// xmlnukeDoc.addXmlnukeObject(block1);
	/// return xmlnukeDoc.generatePage();
	/// </code>
	/// </example>
	public class ProcessPageStateAnydata : ProcessPageStateBase
	{
		protected AnydatasetBaseFilenameProcessor _anydata;
		protected IteratorFilter _itf;
		protected AnydataSetUpdateMethod _updateMethod = AnydataSetUpdateMethod.APPEND;

		/// <summary>
		/// Constructor
		/// </summary>
		/// <param name="context">XMLNuke context object</param>
		/// <param name="fields">Fields will be processed.</param>
		/// <param name="header">Simple string header</param>
		/// <param name="module">Module will be process this request. Usually is the same module instantiate the ProcessPageStateAnyData</param>
		/// <param name="buttons">Custom buttons in View/Select mode.</param>
		/// <param name="anydata">Anydataset File name processor</param>
		public ProcessPageStateAnydata(Context context, ProcessPageFields fields, string header, string module, CustomButtons[] buttons, AnydatasetBaseFilenameProcessor anydata)
			: this(context, fields, header, module, buttons, anydata, null)
		{ }

		/// <summary>
		/// Constructor
		/// </summary>
		/// <param name="context">XMLNuke context object</param>
		/// <param name="fields">Fields will be processed.</param>
		/// <param name="header">Simple string header</param>
		/// <param name="module">Module will be process this request. Usually is the same module instantiate the ProcessPageStateAnyData</param>
		/// <param name="buttons">Custom buttons in View/Select mode.</param>
		/// <param name="anydata">Anydataset File name processor</param>
		/// <param name="itf">Iterator Filter</param>
		public ProcessPageStateAnydata(Context context, ProcessPageFields fields, string header, string module, CustomButtons[] buttons, AnydatasetBaseFilenameProcessor anydata, IteratorFilter itf)
			: base(context, fields, header, module, buttons)
		{
			this._anydata = anydata;
			this._itf = itf;
		}

		/// <summary>
		/// Returns an IIterator with all records in table
		/// </summary>
		/// <returns>IIterator</returns>
		public override IIterator getAllRecords()
		{
			AnyDataSet data = new AnyDataSet(this._anydata);
			return data.getIterator(this._itf);
		}

		protected IteratorFilter getIteratorFilterKey()
		{
			IteratorFilter itf;
			if (this._itf == null)
			{
				itf = new IteratorFilter();
			}
			else
			{
				itf = this._itf;
			}
			string[] arValueId = this._valueId.Split('|');
			int i = 0;
			foreach (int keyIndex in this._keyIndex)
			{
				itf.addRelation(this._fields[keyIndex].fieldName, Relation.Equal, arValueId[i++]);
			}
			return itf;
		}
		/// <summary>
		/// Return a SingleRow with the selection of the user.
		/// </summary>
		/// <returns>SingleRow</returns>
		public override SingleRow getCurrentRecord()
		{
			if (this._currentAction != ACTION_NEW)
			{
				IteratorFilter itf = this.getIteratorFilterKey();

				AnyDataSet data = new AnyDataSet(this._anydata);
				Iterator it = data.getIterator(itf);

				if (it.hasNext())
				{
					return it.moveNext();
				}
			}
			return null;
		}

		/// <summary>
		/// Execute the proper action to insert, update and delete data from database.
		/// </summary>
		/// <returns>IXmlnukeDocumentObject it contains all necessary XML to inform the user the operation result</returns>
		public override IXmlnukeDocumentObject updateRecord()
		{
			IXmlnukeDocumentObject mdo = this.validateUpdate();
			if (mdo != null)
			{
				return mdo;
			}

			// Receive ALL params POSTED.
			NameValueCollection fieldValues = new NameValueCollection();
			ArrayList uploadOrder = new ArrayList();
			for (int i = 0; i < this._fields.Length; i++)
			{
				if (this._fields[i].fieldXmlInput == XmlInputObjectType.FILE)
				{
					uploadOrder.Add(this._fields[i].fieldName);
				}
				else
				{
					fieldValues[this._fields[i].fieldName] = this._context.ContextValue(this._fields[i].fieldName);
				}
			}

			// Receive Uploaded files if the have XmlInputTypeObject.FILE
			if (uploadOrder.Count > 0)
			{
				UploadFilenameProcessor uploadFilename = new UploadFilenameProcessor("common" + util.FileUtil.Slash() + "files", this._context);
				uploadFilename.FilenameLocation = ForceFilenameLocation.SharedPath;
				ArrayList files = this._context.processUpload(uploadFilename, false);

				for (int i = 0; i < files.Count; i++)
				{
					fieldValues[uploadOrder[i].ToString()] = "common/files/" + util.FileUtil.ExtractFileName(files[i].ToString());
				}
			}

			fieldValues = this.preUpdateField(this._currentAction, fieldValues);

			AnyDataSet data = new AnyDataSet(this._anydata);
			if (this._currentAction == ACTION_NEW_CONFIRM)
			{
				if (this._updateMethod == AnydataSetUpdateMethod.APPEND)
				{
					data.appendRow();
				}
				else
				{
					data.insertRowBefore(0);
				}
				foreach (string field in fieldValues.Keys)
				{
					data.addField(field, fieldValues[field]);
				}
			}
			else
			{
				IteratorFilter itf = this.getIteratorFilterKey();
				Iterator it = data.getIterator(itf);

				if (it.hasNext())
				{
					SingleRow sr = it.moveNext();

					if (this._currentAction == ACTION_EDIT_CONFIRM)
					{
						foreach (string field in fieldValues.Keys)
						{
							sr.setField(field, fieldValues[field]);
						}
					}
					else if (this._currentAction == ACTION_DELETE_CONFIRM)
					{
						data.removeRow(sr.getDomObject());
					}
				}
			}

			data.Save(this._anydata);

			//XmlFormCollection retorno = new XmlFormCollection(this._context, this._module, message);
			//retorno.addXmlnukeObject(new XmlInputHidden("filter", this._filter));
			//retorno.addXmlnukeObject(new XmlInputHidden("sort", this._sort));
			//retorno.addXmlnukeObject(new XmlInputHidden("curpage", this._curPage.ToString()));
			//retorno.addXmlnukeObject(new XmlInputHidden("offset", this._qtdRows.ToString()));
			//XmlInputButtons btnRetorno = new XmlInputButtons();
			//btnRetorno.addSubmit("Retornar", "");
			//retorno.addXmlnukeObject(btnRetorno);

			return null;
		}

		protected virtual NameValueCollection preUpdateField(string currentAction, NameValueCollection fieldValues)
		{
			return fieldValues;
		}

		public AnydataSetUpdateMethod getUpdateMethod()
		{
			return this._updateMethod;
		}

		public void setUpdateMethod(AnydataSetUpdateMethod updateMethod)
		{
			this._updateMethod = updateMethod;
		}
	}

	public enum AnydataSetUpdateMethod
	{
		INSERTFIRST,
		APPEND
	}
}