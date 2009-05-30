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

namespace com.xmlnuke.classes
{

	public class XmlInputButtons : XmlnukeDocumentObject
	{
		protected enum ButtonType
		{
			SUBMIT,
			RESET,
			CLICKEVENT,
			BUTTON
		}

		protected struct InputButton
		{
			public ButtonType buttonType;
			public string name;
			public string caption;
			public string onClick;
		}

		protected ArrayList _values;

		public XmlInputButtons()
			: base()
		{
			this._values = new ArrayList();
		}

		public void addSubmit(string caption)
		{
			this.addSubmit(caption, "");
		}

		public void addSubmit(string caption, string name)
		{
			InputButton ib = new InputButton();
			ib.caption = caption;
			ib.name = name;
			ib.buttonType = ButtonType.SUBMIT;
			this._values.Add(ib);
		}

		/// <summary>
		/// Add a Submit button with an event associated with him. The method created must be the suffix "_Event".
		/// </summary>
		/// <param name="caption">Button Caption</param>
		/// <param name="methodName">Method Name</param>
		public void addClickEvent(string caption, string methodName)
		{
			InputButton ib = new InputButton();
			ib.caption = caption;
			ib.name = methodName;
			ib.buttonType = ButtonType.CLICKEVENT;
			this._values.Add(ib);
		}

		public void addReset(string caption)
		{
			this.addReset(caption, "");
		}

		public void addReset(string caption, string name)
		{
			InputButton ib = new InputButton();
			ib.caption = caption;
			ib.name = name;
			ib.buttonType = ButtonType.RESET;
			this._values.Add(ib);
		}

		public void addButton(string caption, string name, string onclick)
		{
			InputButton ib = new InputButton();
			ib.caption = caption;
			ib.name = name;
			ib.onClick = onclick;
			ib.buttonType = ButtonType.BUTTON;
			this._values.Add(ib);
		}
	
		public static XmlInputButtons CreateSubmitButton(string caption)
		{
			XmlInputButtons button = new XmlInputButtons();
			button.addSubmit(caption);
			return button;
		}

		public static XmlInputButtons CreateSubmitCancelButton(string captionSubmit, string captionCancel, string urlCancel)
		{
			XmlInputButtons button = XmlInputButtons.CreateSubmitButton(captionSubmit);
			button.addButton(captionCancel, "cancel", "javacript:window.location = '" + urlCancel + "'");
			return button;
		}

		/// <summary>
		/// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
		/// </summary>
		/// <param name="px">PageXml class</param>
		/// <param name="current">XmlNode where the XML will be created.</param>
		public override void generateObject(XmlNode current)
		{
			XmlNode objBoxButtons = util.XmlUtil.CreateChild(current, "buttons", "");
			string clickEvent = "";

			foreach (object o in this._values)
			{
				InputButton button = (InputButton)o;

				if (button.buttonType == ButtonType.CLICKEVENT)
				{
					clickEvent += (String.IsNullOrEmpty(clickEvent) ? "" : "|") + button.name;
				}

				XmlNode nodeWorking = null;
				switch (button.buttonType)
				{
					case ButtonType.CLICKEVENT:
					case ButtonType.SUBMIT:
						{
							nodeWorking = util.XmlUtil.CreateChild(objBoxButtons, "submit", "");
							break;
						}
					case ButtonType.RESET:
						{
							nodeWorking = util.XmlUtil.CreateChild(objBoxButtons, "reset", "");
							break;
						}
					case ButtonType.BUTTON:
						{
							nodeWorking = util.XmlUtil.CreateChild(objBoxButtons, "button", "");
							util.XmlUtil.AddAttribute(nodeWorking, "onclick", button.onClick);
							break;
						}
				}
				util.XmlUtil.AddAttribute(nodeWorking, "caption", button.caption);
				util.XmlUtil.AddAttribute(nodeWorking, "name", button.name);
			}

			XmlNode clickEventNode = current.SelectSingleNode("clickevent");
			if (clickEventNode == null)
			{
				clickEventNode = util.XmlUtil.CreateChild(current, "clickevent", clickEvent);
			}
			else
			{
				clickEventNode.InnerText = clickEventNode.InnerText + "|" + clickEvent;
			}
		}

	}

}