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
using System.Collections.Generic;
using com.xmlnuke.anydataset;

namespace com.xmlnuke.classes
{
	/// <summary>
	/// 
	/// </summary>
	/// <example escaped="yes">
	/// <code>
	/// XmlEditList editList = 
	///		new XmlEditList(this._context, "My XmlEditList", "xmlnuke.Module");
	/// editList.setDataSource(ds);
	///
	/// CustomButtons cb = new CustomButtons();
	/// cb.enabled = true;
	/// cb.action = "cat";
	/// cb.icon = "common/editlist/ic_categorias.gif";
	/// cb.alternateText = "List all categories";
	/// cb.url = this._context.bindModuleUrl("xmlnuke.Sample");
	/// editList.setCustomButton(0, cb);
	/// </code>
	/// </example>
	/// <seealso cref="com.xmlnuke.classes.XmlEditList">XmlEditList</seealso>
	public struct CustomButtons
	{
		public bool enabled;
		public string url;
		public string icon;
		public string action;
		public string alternateText;
		public MultipleSelectType multiple;
		public string message;
	}

	public interface IEditListFormatter
	{
		/// <summary>
		/// Class to format a EditList Field
		/// </summary>
		/// <param name="row">All data row values</param>
		/// <param name="fieldname">Current Field name</param>
		/// <param name="value">Current Field value</param>
		/// <returns></returns>
		string Format(com.xmlnuke.anydataset.SingleRow row, string fieldname, string value);
	}


	public enum MultipleSelectType
	{
		NONE = 0,
		ONLYONE = 1,
		MULTIPLE = 2
	}

	/// <summary>
	/// Define how XmlEditList will select the elements in list. 
	/// </summary>
	/// <example>
	/// <code>
	/// XmlEditList editList = 
	///		new XmlEditList(this._context, "My XmlEditList", "xmlnuke.Module");
	/// editList.setDataSource(ds);
	/// editList.setSelectRecordType(SelectType.CHECKBOX);
	/// </code>
	/// </example>
	public enum SelectType
	{
		RADIO,
		CHECKBOX
	}

	/// <summary>
	/// Define the type of fields
	/// </summary>
	/// <seealso cref="com.xmlnuke.classes.XmlEditList">XmlEditList</seealso>
	public enum EditListFieldType
	{
		/// <summary>Defines a generic text/number field</summary>
		TEXT,
		/// <summary>The field value refer to link to an image</summary>
		IMAGE,
		/// <summary>The field value refer to a code from another list. Use the arrayLookUp to define the list</summary>
		LOOKUP,
		/// <summary>The field value will be the result of IEditListFormatter object</summary>
		FORMATTER,
		/// <summary>Custom field. To use this field you must inherited XmlEditList and overrides the method "renderColumn"</summary>
		CUSTOM
	}

	/// <summary>
	/// Defines if a field will calculate a summary at the end of the list
	/// </summary>
	public enum EditListFieldSummary
	{
		NONE,
		SUM,
		AVG,
		COUNT
	}

	/// <summary>
	/// Define all fields will be showed in XmlEditList
	/// </summary>
	/// <example>
	/// <code>
	/// XmlEditList editList = 
	///		new XmlEditList(this._context, "My XmlEditList", "xmlnuke.Module");
	///
	/// // Add a Field. Note that the first *must* be a code
	/// EditListField edlf = new EditListField(true);
	/// edlf.fieldData = "custid";
	/// edlf.editListName = "Number";
	/// edlf.EditListFieldType = EditListFieldType.TEXT;
	/// editList.addEditListField(edlf);
	///
	/// // Add another Field
	/// edlf = new EditListField(true);
	/// edlf.fieldData = "name";
	/// edlf.editListName = "Customer Name";
	/// edlf.EditListFieldType = EditListFieldType.TEXT;
	/// editList.addEditListField(edlf);
	/// <code>
	/// </example>
	/// <remarks>
	/// Note: The first field must be a primary key, or a field it identifies
	/// uniquely the row. When a action is selected this field is passed to the module
	/// with the name "valueid" and a parameter name called "action"
	/// </remarks>
	/// <seealso cref="com.xmlnuke.classes.XmlEditList">XmlEditList</seealso>
	public struct EditListField
	{
		// Header NAME for this field (Show on top)
		public string fieldData;
		// Header NAME for this field (Show on top)
		public string editlistName;
		public EditListFieldType fieldType;
		public int maxSize;
		public bool newColumn;
		public IEditListFormatter formatter;
		public NameValueCollection arrayLookup;
		public EditListFieldSummary summary;

		public EditListField(bool newcolumn)
		{
			fieldData = "";
			editlistName = "";
			fieldType = EditListFieldType.TEXT;
			maxSize = 0;
			newColumn = newcolumn;
			formatter = null;
			arrayLookup = new NameValueCollection();
			summary = EditListFieldSummary.NONE;
		}
	}

	/// <summary>
	/// Object to view data in a list format. This object defines representes the &lt;editlist/&gt; xml and all of parameters necessaries for list the data. 
	/// Includes pagination.
	/// </summary>
	/// <example>
	/// <code escaped="true">
	/// AnyDataSet any = new AnyDataSet();
	/// IIterator ds = any.getIterator();
	/// XmlEditList editList = 
	///		new XmlEditList(this._context, "My XmlEditList", "xmlnuke.Module");
	/// editList.setDataSource(ds);
	/// this.defaultXmlDocument.addXmlnukeObject(editlist);
	/// </code>
	/// </example>
	/// <remarks>
	/// Notes: The pageback, pagefwd parameters enable the button to move to previous page and move to forward page. The value must be a number.
	/// The curpage and offset parameters have the current page and the number of rows of this grid. But, these funcionalities *must* be implemented using XMLNuke classes.
	/// You can start EditList pagination passing curpage and offset paramenter in POST or GET.
	/// </remarks>
	/// <seealso cref="com.xmlnuke.classes.EditListField">EditListField</seealso>
	/// <seealso cref="com.xmlnuke.classes.SelectType">SelectType</seealso>
	/// <seealso cref="com.xmlnuke.classes.EditListFieldType">EditListFieldType</seealso>
	/// <seealso cref="com.xmlnuke.classes.CustomButtons">XmlEditList</seealso>
	public class XmlEditList : XmlnukeDocumentObject
	{
		protected string _title;
		protected string _module;
		protected Context _context;
		protected bool _new;
		protected bool _view;
		protected bool _edit;
		protected bool _delete;
		protected bool _readonly;
		protected SelectType _selecttype;
		protected com.xmlnuke.anydataset.IIterator _it;
		protected ArrayList _fields;
		protected string _name;
		protected NameValueCollection _extraParam;
		protected int _curPage; // Used only in programming mode... 
		protected int _qtdRows; // Used only in programming mode... 
		protected bool _enablePages;
		protected string _customsubmit;
		protected IXmlnukeDocumentObject _objXmlHeader = null;

		protected ArrayList _customButton;

		/// <summary>
		/// Initializes a instance of XmlEditList object
		/// </summary>
		/// <param name="context">Xmlnuke context.</param>		
		/// <param name="title">Editlist Title</param>		
		/// <param name="module">Module will execute a action defined by XmlEditList</param>		
		public XmlEditList(Context context, string title, string module)
			: this(context, title, module, true, true, true, true)
		{ }

		/// <summary>
		/// Initializes a instance of XmlEditList object and set the actions is enabled.
		/// </summary>
		/// <param name="context">Xmlnuke context.</param>		
		/// <param name="title">Editlist Title</param>		
		/// <param name="module">Module will execute a action defined by XmlEditList</param>		
		/// <param name="newButton">Enable or disable the new button</param>		
		/// <param name="view">Enable or disable the view button</param>		
		/// <param name="edit">Enable or disable the edit button</param>		
		/// <param name="delete">Enable or disable the delete button</param>		
		public XmlEditList(Context context, string title, string module, bool newButton, bool view, bool edit, bool delete)
		{
			this._module = module;
			this._title = title;
			this._new = newButton;
			this._view = view;
			this._edit = edit;
			this._delete = delete;
			this._readonly = false;
			this._selecttype = SelectType.RADIO;
			this._context = context;

			_customButton = new ArrayList();

			this._name = "EL" + this._context.getRandomNumber(100000).ToString();
			this._extraParam = new NameValueCollection();

			try
			{
				this._curPage = Convert.ToInt32(this._context.ContextValue("curpage"));
			}
			catch
			{
				this._curPage = 0;
			}
			try
			{
				this._qtdRows = Convert.ToInt32(this._context.ContextValue("offset"));
			}
			catch
			{
				this._qtdRows = 0;
			}
			this._enablePages = (this._qtdRows > 0) && (this._curPage > 0);

			this._fields = new ArrayList();

			this._customsubmit = "";
		}

		/// <summary>
		/// Define the number of lines will showed when pagination is enabled. 
		/// To enable a pagination you need call the setEnablePage method. 
		/// </summary>
		/// <param name="qtdRows">Number of rows</param>		
		/// <param name="curPage">Define 0 to XmlEditList manage the pagination, or another number to force a specific page.</param>		
		public void setPageSize(int qtdRows, int curPage)
		{
			if (curPage != 0)
			{
				this._curPage = curPage;
			}
			this._qtdRows = qtdRows;
		}

		/// <summary>
		/// Enable XmlEditList show data in pages. Useful when you have much data.
		/// </summary>
		/// <example>
		/// <code>
		/// XmlEditList editList = 
		///		new XmlEditList(this._context, "My XmlEditList", "xmlnuke.Module");
		/// editList.setDataSource(ds);
		/// editlist.setEnablePage(true);
		/// editlist.setPageSize(10,0);
		/// </code>
		/// </example>
		/// <param name="enable">Enable or disable the pagination</param>		
		public void setEnablePage(bool enable)
		{
			this._enablePages = enable;
			if (this._enablePages)
			{
				if (this._qtdRows == 0)
				{
					this._qtdRows = 10;
				}
				if (this._curPage == 0)
				{
					this._curPage = 1;
				}
			}
		}

		/// <summary>
		/// Get a CustomButton object
		/// </summary>
		/// <param name="i">The number of CustomButtom to retrieve</param>		
		public CustomButtons getCustomButton()
		{
			CustomButtons cb = new CustomButtons();
			cb.enabled = true;
			cb.multiple = MultipleSelectType.ONLYONE;
			return cb;
		}

		/// <summary>
		/// Set a CustomButton object
		/// </summary>
		/// <param name="i">The number of CustomButtom to set</param>		
		public void setCustomButton(int i, CustomButtons cb)
		{
			if (i < this._customButton.Count)
			{
				this._customButton[i] = cb;
			}
			else
			{
				this.setCustomButton(cb);
			}
		}

		public void setCustomButton(CustomButtons cb)
		{
			this._customButton.Add(cb);
		}

		/// <summary>
		/// Set an IIterator object to XmlEditList. XmlEditList uses this object to retrive data.
		/// </summary>
		/// <param name="it">IIteratorObject</param>		
		public void setDataSource(com.xmlnuke.anydataset.IIterator it)
		{
			this._it = it;
		}

		/// <summary>
		/// Add a pair of values to XmlEditList. When a button is pressed this pair will be send togheter.
		/// </summary>
		/// <param name="key">Name of value</param>
		/// <param name="value">Value associated to this name.</param>
		public void addParameter(string key, string value)
		{
			this._extraParam.Add(key, value);
		}

		/// <summary>
		/// Add a field do XmlEditList. 
		/// </summary>
		/// <param name="field">Field to be added</param>
		/// <seealso cref="com.xmlnuke.classes.EditListField">EditListField object</seealso>
		public void addEditListField(EditListField field)
		{
			this._fields.Add(field);
		}

		/// <summary>
		/// Defines how XmlEditList will select the data. 
		/// </summary>
		/// <param name="SelectType">RADIO or CHECK</param>
		/// <seealso cref="com.xmlnuke.classes.SelectType">EditListField enum</seealso>
		public void setSelectRecordType(SelectType st)
		{
			this._selecttype = st;
		}

		/// <summary>
		/// Set XmlEditList readonly or not. If is readonly all buttons are disabled.
		/// </summary>
		/// <param name="value">Enable or Disable the readonly feature.</param>
		public void setReadOnly(bool value)
		{
			this._readonly = value;
		}

		/// <summary>
		/// Add xml information to put into header. 
		/// </summary>
		/// <param name="IXmlnukeDocumentObject">Reference to a object</param>
		public void setXmlHeader(IXmlnukeDocumentObject objXmlHeader)
		{
			this._objXmlHeader = objXmlHeader;
		}

		public void setTitle(string title)
		{
			this._title = title;
		}

		public override void generateObject(XmlNode current)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(current, "editlist", "");
			util.XmlUtil.AddAttribute(nodeWorking, "module", this._module);
			util.XmlUtil.AddAttribute(nodeWorking, "title", this._title);
			util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);

			if (this._new)
				util.XmlUtil.AddAttribute(nodeWorking, "new", "true");
			if (this._edit)
				util.XmlUtil.AddAttribute(nodeWorking, "edit", "true");
			if (this._view)
				util.XmlUtil.AddAttribute(nodeWorking, "view", "true");
			if (this._delete)
				util.XmlUtil.AddAttribute(nodeWorking, "delete", "true");
			if (this._readonly)
				util.XmlUtil.AddAttribute(nodeWorking, "readonly", "true");
			if (this._selecttype == SelectType.CHECKBOX)
				util.XmlUtil.AddAttribute(nodeWorking, "selecttype", "check");

			foreach (string key in this._extraParam.Keys)
			{
				XmlNode param = util.XmlUtil.CreateChild(nodeWorking, "param", "");
				util.XmlUtil.AddAttribute(param, "name", key);
				util.XmlUtil.AddAttribute(param, "value", this._extraParam[key]);
			}

			for (int i = 0; i < this._customButton.Count; i++)
			{
				CustomButtons cb = (CustomButtons)this._customButton[i];
				if (cb.enabled)
				{
					XmlNode nodeButton = util.XmlUtil.CreateChild(nodeWorking, "button");
					util.XmlUtil.AddAttribute(nodeButton, "custom", (i + 1).ToString());
					util.XmlUtil.AddAttribute(nodeButton, "acao", cb.action);
					util.XmlUtil.AddAttribute(nodeButton, "alt", cb.alternateText);
					util.XmlUtil.AddAttribute(nodeButton, "url", cb.url);
					util.XmlUtil.AddAttribute(nodeButton, "img", cb.icon);
					try
					{
						util.XmlUtil.AddAttribute(nodeButton, "multiple", Convert.ToInt32(cb.multiple).ToString());
					}
					catch
					{
						util.XmlUtil.AddAttribute(nodeButton, "multiple", "0");
					}
					util.XmlUtil.AddAttribute(nodeButton, "message", cb.message);
				}
			}

			int qtd = 0;
			int qtdPagina = 0;
			bool started = !this._enablePages;
			bool first = true;
			bool firstRow = true;

			Dictionary<string, double> summaryFields = new Dictionary<string, double>();

			// Generate XML with Data
			while (this._it.hasNext())
			{
				com.xmlnuke.anydataset.SingleRow registro = this._it.moveNext();

				// Insert fields if none is passed.
				if (this._fields.Count == 0)
				{
					foreach (string fieldname in registro.getFieldNames())
					{
						EditListField fieldtmp = new EditListField(true);
						fieldtmp.editlistName = fieldname;
						fieldtmp.fieldData = fieldname;
						fieldtmp.fieldType = EditListFieldType.TEXT;
						this.addEditListField(fieldtmp);
						if (this._fields.Count == 1) // The First field isnt visible because is the "key"
						{
							this.addEditListField(fieldtmp);
						}
					}
				}

				// Fill Values
				if (this._enablePages)
				{
					int page = (qtd / this._qtdRows) + 1;
					started = (page == this._curPage);
				}

				if (started)
				{
					XmlNode row = util.XmlUtil.CreateChild(nodeWorking, "row", "");
					XmlNode currentNode = null;
					foreach (EditListField field in this._fields)
					{
						if (field.newColumn || (currentNode == null))
						{
							currentNode = util.XmlUtil.CreateChild(row, "field", "");
							if (firstRow)
							{
								if (!first)
								{
									util.XmlUtil.AddAttribute(currentNode, "name", field.editlistName);
								}
								else
								{
									first = false;
								}
							}
						}
						else
						{
							util.XmlUtil.CreateChild(currentNode, "br", "");
						}
						this.renderColumn(currentNode, registro, field);

						// Check if the field requires summary
						if (field.summary != EditListFieldSummary.NONE)
						{
							Double d;
							Double.TryParse(registro.getField(field.fieldData), out d);
							summaryFields[field.fieldData] += d;
						}
					}
					firstRow = false;
					qtdPagina++;
				}

				qtd += 1;
			}

			// Generate SUMMARY Information
			if (summaryFields.Count > 0)
			{
				string value = "";

				AnyDataSet anydata = new AnyDataSet();
				anydata.appendRow();
				foreach (object fieldObj in this._fields)
				{
					EditListField field = (EditListField)fieldObj;

					switch (field.summary)
					{
						case EditListFieldSummary.SUM:
							value = summaryFields[field.fieldData].ToString("0.00");
							break;

						case EditListFieldSummary.AVG:
							value = (summaryFields[field.fieldData] / qtdPagina).ToString("0.00");
							break;

						case EditListFieldSummary.COUNT:
							value = qtdPagina.ToString();
							break;

						default:
							value = "";
							break;
					}

					anydata.addField(field.fieldData, value);
				}
				IIterator ittemp = anydata.getIterator();
				SingleRow registro = ittemp.moveNext();

				XmlNode row = util.XmlUtil.CreateChild(nodeWorking, "row", "");
				util.XmlUtil.AddAttribute(row, "total", "true");
				foreach (object fieldObj in this._fields)
				{
					EditListField field = (EditListField)fieldObj;

					XmlNode currentNode = null;
					if ((field.newColumn) || (currentNode == null))
					{
						currentNode = util.XmlUtil.CreateChild(row, "field", "");
					}
					else
					{
						util.XmlUtil.CreateChild(currentNode, "br", "");
					}
					this.renderColumn(currentNode, registro, field);
				}
			}
		
			// Create other properties
			util.XmlUtil.AddAttribute(nodeWorking, "cols", this._fields.Count.ToString());

			if (this._enablePages)
			{
				if (this._curPage > 1)
				{
					util.XmlUtil.AddAttribute(nodeWorking, "pageback", (this._curPage - 1).ToString());
				}
				if (!started) // In this case, the list reachs the last element, so you dont need move forward!
				{
					util.XmlUtil.AddAttribute(nodeWorking, "pagefwd", (this._curPage + 1).ToString());
				}
				util.XmlUtil.AddAttribute(nodeWorking, "curpage", this._curPage.ToString());
				util.XmlUtil.AddAttribute(nodeWorking, "offset", this._qtdRows.ToString());
				util.XmlUtil.AddAttribute(nodeWorking, "pages", Convert.ToInt32(((qtd - 1) / this._qtdRows) + 1).ToString());
			}
			if (this._customsubmit != "")
			{
				util.XmlUtil.AddAttribute(nodeWorking, "customsubmit", this._customsubmit);
			}

			if (this._objXmlHeader != null)
			{
				XmlNode nodeHeader = util.XmlUtil.CreateChild(nodeWorking, "xmlheader", "");
				this._objXmlHeader.generateObject(nodeHeader);
			}
		}

		/// <summary>
		/// Instruct the XmlEditList how the data will be render. Note that this data must be a XML and the Xsl understant what is necessary to transform this XML.
		/// </summary>
		/// <param name="column">XmlNode whre class</param>
		/// <param name="row">All fields and data for the current line.</param>
		/// <param name="EditListField">All information about the current column.</param>
		/// <example>
		/// <code>
		/// public class MyXmlEditList : XmlEditList
		/// {
		///     public override void renderColumn(XmlNode column, com.xmlnuke.anydataset.SingleRow row, EditListField field)
		///     {
		///          if (field.fieldName == "myfield")
		///	         {
		///              XmlnukeText xmt = new XmlnukeText(row.getField(field.fieldData), true);
		///              xmt.generateObject(column);
		///	         {
		///	         else
		///	         {
		///              base.renderColumn(column, row, field)
		///	         }
		///     }
		/// }
		/// </code>
		/// </example>
		/// <remarks>
		/// This method is intended to be override.
		/// </remarks>
		public virtual void renderColumn(XmlNode column, com.xmlnuke.anydataset.SingleRow row, EditListField field)
		{
			switch (field.fieldType)
			{
				case EditListFieldType.TEXT:
					{
						util.XmlUtil.AddTextNode(column, row.getField(field.fieldData));
						break;
					}
				case EditListFieldType.IMAGE:
					{
						XmlnukeImage xmi = new XmlnukeImage(row.getField(field.fieldData));
						xmi.generateObject(column);
						break;
					}
				case EditListFieldType.FORMATTER:
					{
						IEditListFormatter obj = field.formatter;
						if ((obj == null) || !(obj is IEditListFormatter))
						{
							throw new Exception("The EditListFieldType::FORMATTER requires a valid IEditListFormatter class");
						}
						else
						{
							util.XmlUtil.AddTextNode(column, obj.Format(row, field.fieldData, row.getField(field.fieldData)));
						}
						break;
					}
				case EditListFieldType.LOOKUP:
					{
						string value = row.getField(field.fieldData);
						if (value == "")
						{
							value = "---";
						}
						else
						{
							value = field.arrayLookup[value];
						}
						util.XmlUtil.AddTextNode(column, value);
						break;
					}
			}
		}

		/// <summary>
		/// Add a name of JavaScript function. XmlEditList will call this function before post the action.
		/// </summary>
		/// <param name="fnclient">Name od JavaScript function.</param>
		public void setCustomSubmit(string fnclient)
		{
			this._customsubmit = fnclient;
		}
	}

}
