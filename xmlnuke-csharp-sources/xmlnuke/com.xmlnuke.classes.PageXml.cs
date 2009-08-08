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

namespace com.xmlnuke.classes
{
	/// <summary>
	/// Date format used in &lt;editform&gt;&lt;/editform&gt; elements. 
	/// </summary>
	public enum DATEFORMAT
	{
		DMY = 0,
		MDY = 1,
		YMD = 2
	}

	/// <summary>
	/// Data input format used in &lt;editform&gt;&lt;/editform&gt; elements. 
	/// </summary>
	public enum INPUTTYPE
	{
		TEXT = 0,
		LOWER = 1,
		UPPER = 2,
		NUMBER = 3,
		DATE = 4,
		DATETIME = 5,
		UPPERASCII = 9,
		EMAIL = 10
	}

	/// <summary>
	/// PageXml is the old method to abstract XmlDocuments. The new model is defined by XmlnukeDocument.
	/// </summary>
	/// <remarks>Deprecated. Use XmlnukeObject and XmlnukeCollection instead.</remarks>
	/// <seealso cref="com.xmlnuke.classes.XmlnukeObject"/>
	/// <seealso cref="com.xmlnuke.classes.XmlnukeCollection"/>
	public class PageXml : IXmlnukeDocument // [Obsolete]
	{

		private XmlDocument xmlDoc;
		private XmlNode nodePage;
		private XmlNode nodeGroup;
		private bool _breakLine;

		/// <summary>
		/// PageXml Constructor. Empty page.
		/// </summary>
		public PageXml()
		{
			xmlDoc = util.XmlUtil.CreateXmlDocumentFromStr(
				"<page>\r\n" +
				"<meta>\r\n" +
				"<title/>\r\n" +
				"<abstract/>\r\n" +
				"<created>" + DateTime.Now + "</created>\r\n" +
				"<modified>" + DateTime.Now + "</modified>\r\n" +
				"<keyword>XMLSite ByJG</keyword>\r\n" +
				"<groupkeyword/>\r\n" +
				"</meta>\r\n" +
				"<group>\r\n" +
				"<id>__DEFAULT__</id>\r\n" +
				"<title/>\r\n" +
				"<keyword>all</keyword>\r\n" +
				"</group>\r\n" +
				"</page>"
			);
			nodePage = xmlDoc.SelectSingleNode("page");
			nodeGroup = xmlDoc.SelectSingleNode("page/group");
		}

		/// <summary>
		/// PageXml Constructor. Create from XML.
		/// </summary>
		/// <param name="xmlfilename">XMLFilenameProcessor</param>
		public PageXml(processor.XMLFilenameProcessor xmlfilename)
		{
			xmlDoc = xmlfilename.getContext().getXMLDataBase().getDocument(xmlfilename.FullQualifiedName());
		}

		/// <summary>
		/// PageXml Constructor. Create from file name and path. Do not use with XmlNukeDB repository.
		/// </summary>
		/// <param name="path">Path</param>
		/// <param name="filename">Filename</param>
		public PageXml(string path, string filename)
		{
			xmlDoc = util.XmlUtil.CreateXmlDocument();
			xmlDoc.Load(util.FileUtil.AdjustSlashes(path + util.FileUtil.Slash() + filename));
		}

		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		// Meta Properties
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-


		/// <summary>
		/// Get/Set the xml metadata title
		/// </summary>
		public string Title
		{
			set
			{
				xmlDoc.SelectSingleNode("page/meta/title").InnerXml = value;
			}
			get
			{
				return xmlDoc.SelectSingleNode("page/meta/title").InnerXml;
			}
		}

		/// <summary>
		/// Get/Set the xml metadata abstract
		/// </summary>
		public string Abstract
		{
			set
			{
				xmlDoc.SelectSingleNode("page/meta/abstract").InnerXml = value;
			}
			get
			{
				return xmlDoc.SelectSingleNode("page/meta/abstract").InnerXml;
			}
		}

		/// <summary>
		/// Get/Set the xml metadata groupkeyword (used to list menus)
		/// </summary>
		public string GroupKeyword
		{
			set
			{
				xmlDoc.SelectSingleNode("page/meta/groupkeyword").InnerXml = value;
			}
			get
			{
				return xmlDoc.SelectSingleNode("page/meta/groupkeyword").InnerXml;
			}
		}

		/// <summary>
		/// Set the XML modified date.
		/// </summary>
		private void setModified()
		{
			xmlDoc.SelectSingleNode("page/meta/modified").InnerXml = DateTime.Now.ToString();
		}

		/// <summary>
		/// Get the xml metadata datetime created
		/// </summary>
		public string Created
		{
			get
			{
				return xmlDoc.SelectSingleNode("page/meta/created").InnerXml;
			}
		}

		/// <summary>
		/// Get/Set the BreakLine information. After add text to a paragraph BreakLine or not
		/// </summary>
		public bool BreakLine
		{
			get
			{
				return _breakLine;
			}
			set
			{
				_breakLine = value;
			}
		}

		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		// Paragraph Methods
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-


		/// <summary>
		/// Add a free XML string to structure. 
		/// <p><b>Be careful. Only Works on tags blockcenter, blockleft, blockright.</b></p>
		/// </summary>
		/// <param name="XML">XML string</param>
		public void addXMLBlock(string XML)
		{
			string xmlDocstr = xmlDoc.OuterXml;
			int i = xmlDocstr.IndexOf("</page>");
			xmlDocstr = xmlDocstr.Substring(0, i) + XML + "</page>";
			xmlDoc.LoadXml(xmlDocstr);
		}

		/// <summary>
		/// Add a single blockcenter.
		/// <code>
		/// <blockcenter>
		///		<title></title>
		///		<body></body>
		/// </blockcenter>
		/// </code>
		/// </summary>
		/// <param name="title"></param>
		/// <returns>Return the BODY element from Block</returns>
		public XmlNode addBlockCenter(string title)
		{
			XmlNode objBlockCenter = util.XmlUtil.CreateChild(nodePage, "blockcenter", "");
			util.XmlUtil.CreateChild(objBlockCenter, "title", title);
			return util.XmlUtil.CreateChild(objBlockCenter, "body", "");
		}

		public XmlNode addBlockLeft(string title)
		{
			XmlNode objBlockCenter = util.XmlUtil.CreateChild(nodePage, "blockleft", "");
			util.XmlUtil.CreateChild(objBlockCenter, "title", title);
			return util.XmlUtil.CreateChild(objBlockCenter, "body", "");
		}

		public XmlNode addBlockRight(string title)
		{
			XmlNode objBlockCenter = util.XmlUtil.CreateChild(nodePage, "blockright", "");
			util.XmlUtil.CreateChild(objBlockCenter, "title", title);
			return util.XmlUtil.CreateChild(objBlockCenter, "body", "");
		}

		/// <summary>
		/// A single paragraph into Body element.
		/// </summary>
		/// <example>
		/// <code>
		///   &lt;body&gt;
		///     &lt;p&gt;&lt;/p&gt;
		///   &lt;/body&gt;
		/// </code>
		/// </example>
		/// <param name="objBlockCenter"></param>
		/// <returns>XmlNode</returns>
		public XmlNode addParagraph(XmlNode objBlockCenter)
		{
			return util.XmlUtil.CreateChild(objBlockCenter, "p", "");
		}

		public void addCode(XmlNode objParagraph, string code)
		{
			util.XmlUtil.CreateChild(objParagraph, "code", code);
		}

		/// <summary>
		/// Add text to a paragraph structure
		/// </summary>
		/// <param name="objParagraph">Paragraph structure</param>
		/// <param name="strText">Text to be added</param>
		public void addText(XmlNode objParagraph, string strText)
		{
			util.XmlUtil.AddTextNode(objParagraph, strText);
			if (_breakLine)
			{
				util.XmlUtil.CreateChild(objParagraph, "br", "");
			}
		}

		/// <summary>
		/// Add Italic text to a paragraph structure
		/// </summary>
		/// <param name="objParagraph">Paragraph structure</param>
		/// <param name="strText">Text to be added</param>
		public void addItalic(XmlNode objParagraph, string strText)
		{
			util.XmlUtil.CreateChild(objParagraph, "i", strText);
			if (_breakLine)
			{
				util.XmlUtil.CreateChild(objParagraph, "br", "");
			}
		}

		/// <summary>
		/// Add bold text to a paragraph structure
		/// </summary>
		/// <param name="objParagraph">Paragraph structure</param>
		/// <param name="strText">Text to be added</param>
		public void addBold(XmlNode objParagraph, string strText)
		{
			util.XmlUtil.CreateChild(objParagraph, "b", strText);
			if (_breakLine)
			{
				util.XmlUtil.CreateChild(objParagraph, "br", "");
			}
		}

		public XmlNode addTable(XmlNode objParagraph)
		{
			return util.XmlUtil.CreateChild(objParagraph, "table", "");
		}

		public XmlNode addTableRow(XmlNode objTable)
		{
			return util.XmlUtil.CreateChild(objTable, "tr", "");
		}

		public XmlNode addTableColumn(XmlNode objTableRow)
		{
			return util.XmlUtil.CreateChild(objTableRow, "td", "");
		}

		/// <summary>
		/// Add image to a paragragh structure
		/// <code>
		///		<body>
		///			<p>
		///				<img src="" />
		///			</p>
		///		</body>
		/// </code>
		/// </summary>
		/// <param name="objParagraph">Paragragh structure</param>
		/// <param name="strSrc">SRC tag</param>
		/// <param name="strAlt">ALT tag</param>
		/// <param name="intWidth">Width</param>
		/// <param name="intHeight">Height</param>
		public void addImage(XmlNode objParagraph, string strSrc, string strAlt, int intWidth, int intHeight)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objParagraph, "img", "");
			util.XmlUtil.AddAttribute(nodeWorking, "src", strSrc);
			util.XmlUtil.AddAttribute(nodeWorking, "alt", strAlt);
			if (intWidth != 0)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "width", intWidth.ToString());
			}
			if (intHeight != 0)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "height", intHeight.ToString());
			}
			if (_breakLine)
			{
				util.XmlUtil.CreateChild(objParagraph, "br", "");
			}
		}

		/// <summary>
		/// Add HREF to paragraph structure
		/// </summary>
		/// <param name="objParagraph">Paragraph structure</param>
		/// <param name="link">Hyperlink</param>
		/// <param name="text">Text</param>
		public XmlNode addHref(XmlNode objParagraph, string link, string text)
		{
			return this.addHref(objParagraph, link, text, "");
		}

		public XmlNode addHref(XmlNode objParagraph, string link, string text, string target)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objParagraph, "a", text);
			util.XmlUtil.AddAttribute(nodeWorking, "href", link);
			if (target != "")
			{
				util.XmlUtil.AddAttribute(nodeWorking, "target", target);
			}
			if (_breakLine)
			{
				util.XmlUtil.CreateChild(objParagraph, "br", "");
			}
			return nodeWorking;
		}

		public XmlNode addUnorderedList(XmlNode objParagraph)
		{
			return util.XmlUtil.CreateChild(objParagraph, "ul", "");
		}

		public XmlNode addOptionList(XmlNode objList)
		{
			return util.XmlUtil.CreateChild(objList, "li", "");
		}

		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		// Form Methods
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

		/// <summary>
		/// Add FORM structure into blobkcenter structure
		/// <code>
		///		<body>
		///			<editform>
		///			</editform>
		///		</body>
		/// </code>
		/// </summary>
		/// <param name="objBlockCenter">Blockcenter structure</param>
		/// <param name="action">Form Action</param>
		/// <param name="title">Titile</param>
		/// <returns></returns>
		public XmlNode addForm(XmlNode objBlockCenter, string action, string title)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objBlockCenter, "editform", "");
			util.XmlUtil.AddAttribute(nodeWorking, "action", action);
			util.XmlUtil.AddAttribute(nodeWorking, "title", title);
			return nodeWorking;
		}

		/// <summary>
		/// Add FORM structure into blobkcenter structure
		/// NOTE: This function requires latest snippet_htmlbody.inc and validate.js script.
		/// <code>
		///		<body>
		///			<editform>
		///			</editform>
		///		</body>
		/// </code>
		/// </summary>
		/// <param name="objBlockCenter">Blockcenter structure</param>
		/// <param name="action">Form Action</param>
		/// <param name="title">Titile</param>
		/// <param name="decimalseparator">Decimal separator</param>
		/// <param name="dateformat">Date format: DMY, MDY, YMD</param>
		/// <returns></returns>
		public XmlNode addForm(XmlNode objBlockCenter, string action, string title, string formname, bool jsvalidate)
		{
			XmlNode nodeWorking = this.addForm(objBlockCenter, action, title);
			util.XmlUtil.AddAttribute(nodeWorking, "name", formname);
			util.XmlUtil.AddAttribute(nodeWorking, "jsvalidate", jsvalidate.ToString().ToLower());
			return nodeWorking;
		}

		public XmlNode addForm(XmlNode objBlockCenter, string action, string title, string formname, bool jsvalidate, char decimalseparator, DATEFORMAT dateformat)
		{
			XmlNode nodeWorking = this.addForm(objBlockCenter, action, title, formname, jsvalidate);
			util.XmlUtil.AddAttribute(nodeWorking, "decimalseparator", Convert.ToString(decimalseparator));
			util.XmlUtil.AddAttribute(nodeWorking, "dateformat", dateformat.ToString());
			return nodeWorking;
		}

		/// <summary>
		/// Add a label/caption to Form Structure
		/// </summary>
		/// <param name="objForm">Form Structure</param>
		/// <param name="text">Text label</param>
		public void addCaption(XmlNode objForm, string text)
		{
			util.XmlUtil.CreateChild(objForm, "caption", text);
		}

		/// <summary>
		/// Add hidden object to Form structure
		/// </summary>
		/// <param name="objForm">Form structure</param>
		/// <param name="name">Name</param>
		/// <param name="value">Value</param>
		public void addHidden(XmlNode objForm, string name, string value)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objForm, "hidden", "");
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
			util.XmlUtil.AddAttribute(nodeWorking, "value", value);
		}

		/// <summary>
		/// Add a Text Box Object to Form structure
		/// </summary>
		/// <param name="objForm">Form structure</param>
		/// <param name="caption">Caption</param>
		/// <param name="name">Name</param>
		/// <param name="value">Value</param>
		/// <param name="size">Max size</param>
		public void addTextBox(XmlNode objForm, string caption, string name, string value, int size)
		{
			this.addTextBox(objForm, caption, name, value, size, false, INPUTTYPE.TEXT, "", "", "", "");
		}

		/// <summary>
		/// Add a Text Box Object to Form structure
		/// </summary>
		/// <param name="objForm">Form structure</param>
		/// <param name="caption">Caption</param>
		/// <param name="name">Name</param>
		/// <param name="value">Value</param>
		/// <param name="size">Max size</param>
		/// <param name="required">If required, XMLNuke will be validate this field using JavaScript.</param>
		/// <param name="inputtype">Defines the accept data</param>
		/// <param name="minvalue">Min Value</param>
		/// <param name="maxvalue">Max Value</param>
		/// <param name="description">Message will be appear in error case</param>
		/// <param name="customjs">Optional JS to validate. The JS must have the sollow signature: jsfunction(form, obj)</param></param>
		/// <seealso cref="AddForm"/>
		public void addTextBox(XmlNode objForm, string caption, string name, string value, int size, bool required, INPUTTYPE inputtype, string minvalue, string maxvalue, string description, string customjs)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objForm, "textbox", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
			util.XmlUtil.AddAttribute(nodeWorking, "value", value);
			util.XmlUtil.AddAttribute(nodeWorking, "size", size.ToString());
			this.addJSValidation(nodeWorking, required, inputtype, minvalue, maxvalue, description, customjs);
		}

		private void addJSValidation(XmlNode objInput, bool required, INPUTTYPE inputtype, string minvalue, string maxvalue, string description, string customjs)
		{
			util.XmlUtil.AddAttribute(objInput, "required", required.ToString().ToLower());
			util.XmlUtil.AddAttribute(objInput, "type", Convert.ToInt32(inputtype).ToString());
			if (minvalue != "")
			{
				util.XmlUtil.AddAttribute(objInput, "minvalue", minvalue);
			}
			if (maxvalue != "")
			{
				util.XmlUtil.AddAttribute(objInput, "maxvalue", maxvalue);
			}
			if (description != "")
			{
				util.XmlUtil.AddAttribute(objInput, "description", description);
			}
			if (customjs != "")
			{
				util.XmlUtil.AddAttribute(objInput, "customjs", customjs);
			}
		}

		public void addLabelField(XmlNode objForm, string caption, string value)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objForm, "label", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "value", value);
		}

		/// <summary>
		/// Add a Password Text Box Object to Form structure
		/// </summary>
		/// <param name="objForm">Form structure</param>
		/// <param name="caption">Caption</param>
		/// <param name="name">Name</param>
		/// <param name="size">Max size</param>
		public void addPassword(XmlNode objForm, string caption, string name, int size)
		{
			this.addPassword(objForm, caption, name, size, false, INPUTTYPE.TEXT, "", "", "", "");
		}

		public void addPassword(XmlNode objForm, string caption, string name, int size, bool required, INPUTTYPE inputtype, string minvalue, string maxvalue, string description, string customjs)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objForm, "password", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
			util.XmlUtil.AddAttribute(nodeWorking, "size", size.ToString());
			this.addJSValidation(nodeWorking, required, inputtype, minvalue, maxvalue, description, customjs);
		}

		/// <summary>
		/// Add a Multiline Text Box Object to Form structure
		/// </summary>
		/// <param name="objForm">Form structure</param>
		/// <param name="caption">Caption</param>
		/// <param name="name">Name</param>
		/// <param name="value">Value</param>
		/// <param name="cols">Cols</param>
		/// <param name="rows">Rows</param>
		/// <param name="wrap">SOFT|OFF</param>
		public void addMemo(XmlNode objForm, string caption, string name, string value, int cols, int rows, string wrap)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objForm, "memo", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
			util.XmlUtil.AddAttribute(nodeWorking, "cols", cols.ToString());
			util.XmlUtil.AddAttribute(nodeWorking, "rows", rows.ToString());
			util.XmlUtil.AddAttribute(nodeWorking, "wrap", wrap);
			util.XmlUtil.AddTextNode(nodeWorking, value);
		}

		/// <summary>
		/// Add a Radio Box Object to Form structure
		/// </summary>
		/// <param name="objForm">Form structure</param>
		/// <param name="caption">Caption</param>
		/// <param name="name">Name</param>
		/// <param name="value">Value</param>
		public void addRadioBox(XmlNode objForm, string caption, string name, string value)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objForm, "radiobox", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
			util.XmlUtil.AddAttribute(nodeWorking, "value", value);
		}

		/// <summary>
		/// Add a Check Box Object to Form structure
		/// </summary>
		/// <param name="objForm">Form structure</param>
		/// <param name="caption">Caption</param>
		/// <param name="name">Name</param>
		/// <param name="value">Value</param>
		public void addCheckBox(XmlNode objForm, string caption, string name, string value)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objForm, "checkbox", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
			util.XmlUtil.AddAttribute(nodeWorking, "value", value);
		}

		/// <summary>
		/// Add a Select object to Form Structure
		/// </summary>
		/// <param name="objForm">Form Structure</param>
		/// <param name="caption">Caption</param>
		/// <param name="name">Select name</param>
		/// <returns>Select object</returns>
		public XmlNode addSelect(XmlNode objForm, string caption, string name)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objForm, "select", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
			return nodeWorking;
		}

		public XmlNode addSelect(XmlNode objForm, string caption, string name, string[] values)
		{
			return addSelect(objForm, caption, name, values, "");
		}

		public XmlNode addSelect(XmlNode objForm, string caption, string name, string[] values, string defaultValue)
		{
			XmlNode nodeWorking = this.addSelect(objForm, caption, name);
			if (values != null)
			{
				foreach (string value in values)
				{
					this.addOption(nodeWorking, value, value, (value == defaultValue));
				}
			}
			return nodeWorking;
		}

		public XmlNode addSelect(XmlNode objForm, string caption, string name, bool required, string customjs)
		{
			XmlNode nodeWorking = this.addSelect(objForm, caption, name);
			util.XmlUtil.AddAttribute(nodeWorking, "required", required.ToString().ToLower());
			if (customjs != "")
			{
				util.XmlUtil.AddAttribute(nodeWorking, "customjs", customjs);
			}
			return nodeWorking;
		}

		/// <summary>
		/// Add a option line to a Select Object
		/// </summary>
		/// <param name="objSelect">Select Object</param>
		/// <param name="caption">Caption</param>
		/// <param name="value">Value</param>
		public void addOption(XmlNode objSelect, string caption, string value)
		{
			this.addOption(objSelect, caption, value, false);
		}

		public void addOption(XmlNode objSelect, string caption, string value, bool selected)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objSelect, "option", "");
			util.XmlUtil.AddAttribute(nodeWorking, "value", value);
			if (selected)
			{
				util.XmlUtil.AddAttribute(nodeWorking, "selected", "yes");
			}
			util.XmlUtil.AddTextNode(nodeWorking, caption);
		}

		/// <summary>
		/// Add a Box option to a form Object
		/// </summary>
		/// <param name="objForm">Form Object</param>
		/// <returns>Box Option</returns>
		public XmlNode addBoxButtons(XmlNode objForm)
		{
			return util.XmlUtil.CreateChild(objForm, "buttons", "");
		}

		/// <summary>
		/// Add a Button to a Box Button Object
		/// </summary>
		/// <param name="objBoxButtons">Box Button Object</param>
		/// <param name="name">Name</param>
		/// <param name="caption">Caption</param>
		/// <param name="onclick">Onclick javascript</param>
		public void addButton(XmlNode objBoxButtons, string name, string caption, string onclick)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objBoxButtons, "button", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
			util.XmlUtil.AddAttribute(nodeWorking, "onclick", onclick);
		}

		/// <summary>
		/// Add a submit button to a Box Button Object
		/// </summary>
		/// <param name="objBoxButtons">Box Button Object</param>
		/// <param name="name">Name </param>
		/// <param name="caption">Caption</param>
		public void addSubmit(XmlNode objBoxButtons, string name, string caption)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objBoxButtons, "submit", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
		}

		/// <summary>
		/// Add a reset button to a Box Button Object
		/// </summary>
		/// <param name="objBoxButtons">Box Button Object</param>
		/// <param name="name">Name </param>
		/// <param name="caption">Caption</param>
		public void addReset(XmlNode objBoxButtons, string name, string caption)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(objBoxButtons, "reset", "");
			util.XmlUtil.AddAttribute(nodeWorking, "caption", caption);
			util.XmlUtil.AddAttribute(nodeWorking, "name", name);
		}

		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		// Menu Itens
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

		public void setMenuInfo(string title)
		{
			XmlNode nodeWorking = nodeGroup.SelectSingleNode("title");
			if (nodeWorking != null)
			{
				nodeWorking.InnerText = title;
			}
		}

		public void addMenuItem(string xmlID, string title, string summary)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(nodeGroup, "page", "");
			util.XmlUtil.CreateChild(nodeWorking, "id", xmlID);
			util.XmlUtil.CreateChild(nodeWorking, "title", title);
			util.XmlUtil.CreateChild(nodeWorking, "summary", summary);
		}

		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		// Others
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		public void addJavaScript(string javascript)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(nodePage, "script", "");
			util.XmlUtil.AddAttribute(nodeWorking, "language", "javascript");
			util.XmlUtil.AddTextNode(nodeWorking, javascript);
		}

		public void addFlash(string movie, int width, int height)
		{
			XmlNode nodeWorking = util.XmlUtil.CreateChild(nodePage, "script", "");
			util.XmlUtil.AddAttribute(nodeWorking, "movie", movie);
			util.XmlUtil.AddAttribute(nodeWorking, "width", width.ToString());
			util.XmlUtil.AddAttribute(nodeWorking, "height", height.ToString());
		}


		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
		// Get XML
		//=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

		/// <summary>
		/// Gets the XML string from PageXml object
		/// </summary>
		/// <returns>XML String</returns>
		public string XML()
		{
			setModified();
			return xmlDoc.OuterXml;
		}

		/// <summary>
		/// Gets the XMLDocument object from PageXml object
		/// </summary>
		/// <returns>XML String</returns>
		public XmlDocument getDomObject()
		{
			return this.makeDomObject();
		}

		/// <summary>
		/// 
		/// </summary>
		/// <returns></returns>
		public XmlDocument makeDomObject()
		{
			setModified();
			return xmlDoc;
		}

		/// <summary>
		/// Gets the XMLNode root node (<page/>) PageXml object
		/// </summary>
		/// <returns>XML String</returns>
		public XmlNode getRootNode()
		{
			return nodePage;
		}


		/// <summary>
		/// Save XML String to file
		/// </summary>
		/// <param name="xmlFile">XMLFilenameProcessor</param>
		public void SaveTo(processor.XMLFilenameProcessor xmlFile)
		{
			xmlFile.getContext().getXMLDataBase().saveDocument(xmlFile.FullQualifiedName(), this.getDomObject());
		}

		public void AddErrorMessage(XmlNode objParagraph, string sourceXml, XmlException ex)
		{
			int startLine = 0;
			for (int lineNo = 0; lineNo <= ex.LineNumber; lineNo++)
				startLine = sourceXml.IndexOf("\n", startLine + 1);
			int endLine = sourceXml.IndexOf("\n", startLine + 1);
			string line = sourceXml.Substring(startLine + 1, endLine - startLine).Replace("\t", " ");
			string compl = "-";
			compl = compl.PadRight(ex.LinePosition - 1, '-') + "^";
			this.addBold(objParagraph, "Error: " + ex.Message);
			this.addCode(objParagraph, line + "\n" + compl);
			this.addHref(objParagraph, "javascript:history.go(-1)", "Go Back");
		}



	}
}
