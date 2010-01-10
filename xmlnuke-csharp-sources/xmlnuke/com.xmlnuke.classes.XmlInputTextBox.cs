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
using System.Xml;

namespace com.xmlnuke.classes
{
	public enum InputTextBoxType
	{
		TEXT,
		PASSWORD
	}

    public class XmlInputTextBox : XmlInputValidate
    {
        protected string _name;
        protected string _caption;
        protected string _value;
        protected int _size;
        protected InputTextBoxType _inputextboxtype;
        protected bool _readonly;
        protected int _maxlength;
        protected string _readOnlyDeli = "[]";
        protected string _autosuggestUrl = "";
        protected string _autosuggestParamReq = "";
        protected string _autosuggestAttrInfo = "";
        protected string _autosuggestAttrId = "";
        protected string _autosuggestCallback = "";
        // Only used if sets autocomplete!
        protected engine.Context _context;
        protected string _maskText = "";

        public XmlInputTextBox(string caption, string name, string value)
            : base()
        {
            this._name = name;
            this._value = value;
            this._caption = caption;
            this._size = 20;
            this._readonly = false;
            this._maxlength = 0;
        }

        public XmlInputTextBox(string caption, string name, string value, int size)
            : base()
        {
            this._name = name;
            this._value = value;
            this._caption = caption;
            this._size = size;
            this._readonly = false;
        }


        public void setInputTextBoxType(InputTextBoxType inputextboxtype)
        {
            this._inputextboxtype = inputextboxtype;
        }

        public void setReadOnly(bool value)
        {
            this._readonly = value;
        }

        public void setReadOnlyDelimiters(string value)
        {
            if (value.Length != 2 && !String.IsNullOrEmpty(value))
            {
                throw new Exception("Read Only Delimiters must have two characters or is empty");
            }
            else
            {
                this._readOnlyDeli = value;
            }
        }

        public void setMaxLength(int maxlength)
        {
            this._maxlength = maxlength;
        }

        public void setValue(string value)
        {
            this._value = value;
        }
        public string getValue()
        {
            return this._value;
        }

        public void setName(string name)
        {
            this._name = name;
        }
        public string getName()
        {
            return this._name;
        }

        public void setCaption(string caption)
        {
            this._caption = caption;
        }
        public string getCaption()
        {
            return this._caption;
        }

        public void setMask(string text)
        {
            this._maskText = text;
        }
        public string getMask()
        {
            return this._maskText;
        }

        public void setAutosuggest(engine.Context context, string url, string paramReq)
        {
            this.setAutosuggest(context, url, paramReq, "", "", "");
        }
        public void setAutosuggest(engine.Context context, string url, string paramReq, string attrInfo)
        {
            this.setAutosuggest(context, url, paramReq, attrInfo, "", "");
        }
        public void setAutosuggest(engine.Context context, string url, string paramReq, string attrInfo, string attrId)
        {
            this.setAutosuggest(context, url, paramReq, attrInfo, attrId, "");
        }
        public void setAutosuggest(engine.Context context, string url, string paramReq, string attrInfo, string attrId, string jsCallback)
        {
            this._context = context;
            this._autosuggestUrl = url;
            this._autosuggestParamReq = paramReq;
            this._autosuggestAttrInfo = attrInfo;
            this._autosuggestAttrId = attrId;
            this._autosuggestCallback = jsCallback;
        }

        /// <summary>
        /// Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
        /// </summary>
        /// <param name="px">PageXml class</param>
        /// <param name="current">XmlNode where the XML will be created.</param>
        public override void generateObject(XmlNode current)
        {
            if (this._readonly)
            {
                string deliLeft = (String.IsNullOrEmpty(this._readOnlyDeli) ? this._readOnlyDeli[0].ToString() : "");
                string deliRight = (String.IsNullOrEmpty(this._readOnlyDeli) ? this._readOnlyDeli[1].ToString() : "");

                XmlInputLabelField ic;
                if (this._inputextboxtype == InputTextBoxType.TEXT)
                {
                    ic = new XmlInputLabelField(this._caption, deliLeft + this._value + deliRight);
                }
                else
                {
                    ic = new XmlInputLabelField(this._caption, deliLeft + "**********" + deliRight);
                }
                ic.generateObject(current);

                XmlInputHidden ih = new XmlInputHidden(this._name, this._value);
                ih.generateObject(current);
            }
            else
            {
                XmlNode nodeWorking;
                if (this._inputextboxtype == InputTextBoxType.TEXT)
                {
                    nodeWorking = util.XmlUtil.CreateChild(current, "textbox", "");
                }
                else
                {
                    nodeWorking = util.XmlUtil.CreateChild(current, "password", "");
                }
                if (this._maxlength != 0)
                {
                    util.XmlUtil.AddAttribute(nodeWorking, "maxlength", this._maxlength);
                }
                util.XmlUtil.AddAttribute(nodeWorking, "caption", this._caption);
                util.XmlUtil.AddAttribute(nodeWorking, "name", this._name);
                util.XmlUtil.AddAttribute(nodeWorking, "value", this._value);
                util.XmlUtil.AddAttribute(nodeWorking, "size", this._size.ToString());

                if (this._autosuggestUrl != "")
                {
                    XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.MODULE, this._autosuggestUrl);
                    string urlStr = url.getUrlFull(this._context);
                    if (urlStr.IndexOf("?") < 0)
                    {
                        urlStr += "?";
                    }
                    else
                    {
                        urlStr += "&";
                    }
                    util.XmlUtil.AddAttribute(nodeWorking, "autosuggesturl", urlStr);
                    util.XmlUtil.AddAttribute(nodeWorking, "autosuggestparamreq", this._autosuggestParamReq);
                    if (this._autosuggestAttrId != "") util.XmlUtil.AddAttribute(nodeWorking, "autosuggestattrid", this._autosuggestAttrId);
                    if (this._autosuggestAttrInfo != "") util.XmlUtil.AddAttribute(nodeWorking, "autosuggestattrinfo", this._autosuggestAttrInfo);
                    if (this._autosuggestCallback != "") util.XmlUtil.AddAttribute(nodeWorking, "autosuggestcallback", this._autosuggestCallback);
                }

                if (this.getMask() == "")
                {
                    if (this.getDataType() == INPUTTYPE.DATE)
                    {
                        this.setMask("99/99/9999");
                    }
                    else if (this.getDataType() == INPUTTYPE.DATETIME)
                    {
                        this.setMask("99/99/9999 99:99:99");
                    }
                }

                if (this.getMask() != "")
                {
                    util.XmlUtil.AddAttribute(nodeWorking, "mask", this._maskText);
                }


                base.generateObject(nodeWorking);
            }
        }

    }

}