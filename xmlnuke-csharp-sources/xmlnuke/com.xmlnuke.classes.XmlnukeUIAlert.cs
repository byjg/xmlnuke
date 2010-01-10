/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  Acknowledgments to: Roan Brasil Monteiro
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
using System.Collections.Generic;

using com.xmlnuke.engine;
using com.xmlnuke.processor;
using com.xmlnuke.util;
using System.Xml;

namespace com.xmlnuke.classes
{

    public enum UIAlert
    {
        Dialog,
        ModalDialog,
        BoxInfo,
        BoxAlert
    }

    public enum UIAlertOpenAction
    {
        URL,
        Image,
        Button,
        NoAutoOpen,
        None
    }


    /**
    *@package com.xmlnuke
    *@subpackage xmlnukeobject
    */
    public class XmlnukeUIAlert : XmlnukeCollection, IXmlnukeDocumentObject
    {
        /**
         * @var Context
         */
        protected Context _context;

        protected UIAlert _uialert = UIAlert.BoxInfo;
        protected string _title = "";
        protected string _name = "";
        protected UIAlertOpenAction _openAction = UIAlertOpenAction.None;
        protected string _openActionText = null;
        protected int _autoHide = 0;
        protected int _width = 0;
        protected int _height = 0;

        protected Dictionary<string, string> _buttons;

        public XmlnukeUIAlert(Context context, UIAlert uialert)
            : this(context, uialert, "")
        { }

        public XmlnukeUIAlert(Context context, UIAlert uialert, string title)
        {
            this._context = context;
            this._uialert = uialert;
            if (String.IsNullOrEmpty(title))
            {
                this._title = "Message";
            }
            else
            {
                this._title = title;
            }
            this._name = "uialert_" + this._context.getRandomNumber(1000, 9999).ToString();
            this._buttons = new Dictionary<string, string>();
        }

        public string getName()
        {
            return this._name;
        }
        public void setName(string value)
        {
            this._name = value;
        }

        public int getAutoHide()
        {
            return this._autoHide;
        }
        public void setAutoHide(int value)
        {
            this._autoHide = value;
        }

        public void setDimensions(int width)
        {
            this.setDimensions(width, 0);
        }

        public void setDimensions(int width, int height)
        {
            this._width = width;
            this._height = height;
        }

        public void setUIAlertType(UIAlert type)
        {
            this._uialert = type;
        }

        public void addCloseButton(string text)
        {
            this._buttons.Add(text, "(this).dialog('close');");
        }

        public void addRedirectButton(string text, string url)
        {
            ParamProcessor param = new ParamProcessor(this._context);
            string urlXml = param.GetFullLink(url);
            this._buttons.Add(text, "window.location='urlXml';");
        }

        public void addCustomButton(string text, string javascript)
        {
            this._buttons.Add(text, javascript);
        }

        public void setOpenAction(UIAlertOpenAction openaction, string text)
        {
            if (openaction != UIAlertOpenAction.None)
            {
                this._openAction = openaction;
                this._openActionText = text;
            }
            else
            {
                this._openAction = UIAlertOpenAction.None;
            }
        }


        #region IXmlnukeDocumentObject Members

        public void generateObject(XmlNode current)
        {
            XmlNode node = XmlUtil.CreateChild(current, "uialert", "");
            XmlUtil.AddAttribute(node, "type", this._uialert.ToString().ToLower());
            XmlUtil.AddAttribute(node, "name", this._name);
            XmlUtil.AddAttribute(node, "title", this._title);
            if (this._autoHide > 0)
            {
                XmlUtil.AddAttribute(node, "autohide", this._autoHide);
            }
            if (this._openAction != UIAlertOpenAction.None)
            {
                XmlUtil.AddAttribute(node, "openaction", this._openAction.ToString().ToLower());
                XmlUtil.AddAttribute(node, "openactiontext", this._openActionText);
            }
            if (this._width > 0)
            {
                XmlUtil.AddAttribute(node, "width", this._width);
            }
            if (this._height > 0)
            {
                XmlUtil.AddAttribute(node, "height", this._height);
            }
            foreach (KeyValuePair<string, string> kvp in this._buttons)
            {
                XmlNode btn = XmlUtil.CreateChild(node, "button", kvp.Value);
                XmlUtil.AddAttribute(btn, "text", kvp.Key);
            }
            XmlNode body = XmlUtil.CreateChild(node, "body");
            base.generatePage(body);
        }

        #endregion
    }

}