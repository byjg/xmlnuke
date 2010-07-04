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
using System.Collections.Generic;
using System.Xml;

namespace com.xmlnuke.classes
{
    /// <summary>
    /// Base for implement a class to abstract a XML implementation of a collection of XmlObjects. Use it to create your own XML document also.
    /// </summary>
    /// <example>
    /// To create a Xml object that represent the follow XML:
    /// <code escaped="true">
    /// <my>
    ///    <element></element>
    ///    <element></element>
    ///    <element></element>
    /// </my>
    /// </code>
    /// Use as follow:
    /// <code>
    /// public class XmlMyCollection : XmlnukeCollection , IXmlnukeDocumentObject
    /// {
    ///     public void generateObject(XmlNode current)
    ///     {
    ///          XmlNode node = util.XmlUtil.CreateChild(current, "my", "");
    ///          this.generatePage(node);
    ///     }
    /// }
    /// </code>
    /// </example>
    /// <seealso cref="com.xmlnuke.classes.XmlnukeDocumentObject">XmlnukeDocumentObject</seealso>
    /// <seealso cref="com.xmlnuke.classes.IXmlnukeDocumentObject">IXmlnukeDocumentObject</seealso>
    /// <seealso cref="com.xmlnuke.classes.XmlnukeDocument">XmlnukeDocument</seealso>
    public abstract class XmlnukeCollection
    {
        protected ArrayList _items;

        /// <summary>
        /// Initializes an instance of XmlnukeCollection.
        /// </summary>
        public XmlnukeCollection()
        {
            _items = new ArrayList();
        }

        /// <summary>
        /// Method for add new objects to collection.
        /// </summary>
        /// <param name="docobj">Any object it implements IXmlnukeDocumentObject interface</param>
        public virtual void addXmlnukeObject(IXmlnukeDocumentObject docobj)
        {
            if (docobj == this)
            {
                throw new Exception("You are adding the object to itself");
            }
            _items.Add(docobj);
        }

        /// <summary>
        /// Method for process all XMLNukedocumentObjects in collection.
        /// </summary>
        /// <param name="current">XmlNode where all XML will be inserted.</param>
        protected void generatePage(XmlNode current)
        {
            foreach (IXmlnukeDocumentObject item in _items)
            {
                item.generateObject(current);
            }
        }
    }

    /// <summary>
    /// Main XmlnukeDocument. All collections must be added to this object.
    /// </summary>
    /// <example>
    /// <code>
    /// // Creating paragraphs.
    /// XmlParagraphCollection para1 = new XmlParagraphCollection();
    /// para1.addXmlnukeObject(new XmlnukeText("Isso  um teste"));
    /// 
    /// // Creating blocks
    /// XmlBlockCollection block1 = new XmlBlockCollection("BlockTitle", BlockPosition.Center);
    /// block1.addXmlnukeObject(para1);
    /// 
    /// // Create XmlnukeDocument and add the blocks to this document.
    /// XmlnukeDocument xmlnukeDoc = new XmlnukeDocument("Page Title", "Abstract of this page");
    /// xmlnukeDoc.addXmlnukeObject(block1);
    /// return xmlnukeDoc.generatePage();
    /// </code>
    /// </example>
    public class XmlnukeDocument : XmlnukeCollection, IXmlnukeDocument
    {
        protected string _pageTitle = "XmlNuke Page";
        protected string _abstract = "";
        protected string _groupKeyword = "";
        protected DateTime _created = DateTime.Now;
        protected ArrayList _scripts;
        protected Dictionary<string, MenuGroup> _menuGroup;
        protected NameValueCollection _metaTag;
        protected bool _waitLoading = false;
        protected bool _disableButtonsOnSubmit = true;


        protected struct menuDef
        {
            public string id;
            public string title;
            public string summary;
            public string icon;
        }

        protected struct MenuGroup
        {
            public string menuTitle;
            public ArrayList menus;
        }


        protected struct scriptDef
        {
            public string sourceCode;
            public string reference;
        }

        public XmlnukeDocument() : this("Xmlnuke Page", "") { }

        public XmlnukeDocument(string pageTitle, string desc)
            : base()
        {
            this._pageTitle = pageTitle;
            this._abstract = desc;
            this._scripts = new ArrayList();
            this._metaTag = new NameValueCollection();

            this._menuGroup = new Dictionary<string, MenuGroup>();
            this.addMenuGroup("Módulo", "__DEFAULT__");
        }

        /// <summary>
        /// Get/Set the xml metadata title
        /// </summary>
        public string PageTitle
        {
            set
            {
                _pageTitle = value;
            }
            get
            {
                return _pageTitle;
            }
        }

        /// <summary>
        /// Get/Set the xml metadata abstract
        /// </summary>
        public string Abstract
        {
            set
            {
                _abstract = value;
            }
            get
            {
                return _abstract;
            }
        }

        /// <summary>
        /// Get/Set the xml metadata groupkeyword (used to list menus)
        /// </summary>
        public string GroupKeyword
        {
            set
            {
                _groupKeyword = value;
            }
            get
            {
                return _groupKeyword;
            }
        }

        /// <summary>
        /// Get the xml metadata datetime created
        /// </summary>
        public DateTime Created
        {
            get
            {
                return _created;
            }
        }

        public void setMenuTitle(string title)
        {
            this.setMenuTitle(title, "__DEFAULT__");
        }
        public void setMenuTitle(string title, string group)
        {
            Object menuObj = this._menuGroup[group];
            if (menuObj != null)
            {
                MenuGroup menu = (MenuGroup)menuObj;
                menu.menuTitle = title;
                this._menuGroup[group] = menu;
            }
            else
            {
                throw new Exception("Menu Group '" + group + "' does not exists. Did you created using addMenuGroup() method?");
            }
        }

        public void addMenuGroup(string title, string group)
        {
            MenuGroup mg = new MenuGroup();
            mg.menuTitle = title;
            mg.menus = new ArrayList();
            //
            this._menuGroup[group] = mg;
        }

        public void addMenuItem(string id, string title, string summary)
        {
            this.addMenuItem(id, title, summary, "__DEFAULT__");
        }
        public void addMenuItem(string id, string title, string summary, string group)
        {
            this.addMenuItem(id, title, summary, group, "");
        }
        public void addMenuItem(string id, string title, string summary, string group, string icon)
        {
            menuDef m = new menuDef();
            m.id = id;
            m.title = title;
            m.summary = summary;
            m.icon = icon;

            Object menuObj = this._menuGroup[group];
            if (menuObj != null)
            {
                MenuGroup menu = (MenuGroup)menuObj;
                menu.menus.Add(m);
                this._menuGroup[group] = menu;
            }
            else
            {
                throw new Exception("Menu Group '" + group + "' does not exists. Did you created using addMenuGroup() method?");
            }
        }

        public void addJavaScriptSource(string source)
        {
            this.addJavaScriptSource(source, false);
        }

        public void addJavaScriptSource(string source, bool compress)
        {
            scriptDef s = new scriptDef();
            if (compress)
            {
                util.JSCompressor jsc = new util.JSCompressor(true, false);
                jsc.TestVariableNameCompression = false;
                s.sourceCode = jsc.Compress(source);
            }
            else
            {
                s.sourceCode = source;
            }
            this._scripts.Add(s);
        }

        public void addJavaScriptReference(string reference)
        {
            scriptDef s = new scriptDef();
            s.reference = reference;
            this._scripts.Add(s);
        }

        /// <summary>
        /// Add a Meta tag in page/meta
        /// </summary>
        /// <param name="name"></param>
        /// <param name="value"></param>
        public void addMetaTag(string name, string value)
        {
            this._metaTag[name] = value;
        }

        public void setWaitLoading(bool value)
        {
            this._waitLoading = value;
        }
        public bool getWaitLoading()
        {
            return this._waitLoading;
        }


        public void setDisableButtonOnSubmit(bool value)
        {
            this._disableButtonsOnSubmit = value;
        }
        public bool getDisableButtonOnSubmit()
        {
            return this._disableButtonsOnSubmit;
        }


        public void addJavaScriptMethod(string jsObject, string jsMethod, string jsSource)
        {
            this.addJavaScriptMethod(jsObject, jsMethod, jsSource, "");
        }

        /// <summary>
        /// Add a JavaScript method to a JavaScript object.
        ///
        /// Some examples:
        /// 
        /// addJavaScriptMethod("a", "click", "alert('clicked on a hiperlink');");
        /// addJavaScriptMethod("#myID", "blur", "alert('blur a ID object in JavaScript');");
        /// </summary>
        /// <param name="jsObject"></param>
        /// <param name="jsMethod"></param>
        /// <param name="jsSource"></param>
        /// <param name="jsParameters"></param>
        /// <returns></returns>
        public void addJavaScriptMethod(string jsObject, string jsMethod, string jsSource, string jsParameters)
        {
            string jsEventSource =
                "(function() { \n" +
                "	('" + jsObject + "')." + jsMethod + "(function(" + jsParameters + ") { \n" +
                "		" + jsSource + "\n" +
                "	}); \n" +
                "});\n\n";
            this.addJavaScriptSource(jsEventSource, false);
        }

        /// <summary>
        /// Add a JavaScript attribute to a JavaScript object.
        ///
        /// Some examples:
        ///
        /// addJavaScriptMethod("#myID", "someAttr", array("param"=>"'teste'"));
        /// </summary>
        /// <param name="jsObject"></param>
        /// <param name="jsAttrName"></param>
        /// <param name="attrParam"></param>
        public void addJavaScriptAttribute(string jsObject, string jsAttrName, Dictionary<String, String> attrParam)
        {
            string jsEventSource =
                "(function() { \n" +
                "   ('" + jsObject + "')." + jsAttrName + "({ \n";

            bool first = true;
            foreach (KeyValuePair<string, string> kvp in attrParam)
            {
                jsEventSource += (!first ? ",\n" : "") + "      " + kvp.Key + ": " + kvp.Value;
                first = false;
            }

            jsEventSource +=
                "\n   }); \n" +
                "});\n\n";

            this.addJavaScriptSource(jsEventSource, false);
        }

        public XmlDocument makeDomObject()
        {
            DateTime created = DateTime.Now;

            XmlDocument xmlDoc = util.XmlUtil.CreateXmlDocument();

            // Create the First first NODE ELEMENT!
            XmlElement nodePage = xmlDoc.CreateElement("page");
            xmlDoc.AppendChild(nodePage);

            // Create the META node
            XmlNode nodeMeta = util.XmlUtil.CreateChild(nodePage, "meta", "");
            util.XmlUtil.CreateChild(nodeMeta, "title", this._pageTitle);
            util.XmlUtil.CreateChild(nodeMeta, "abstract", this._abstract);
            util.XmlUtil.CreateChild(nodeMeta, "keyword", "xmlnuke");
            util.XmlUtil.CreateChild(nodeMeta, "groupkeyword", this._groupKeyword);
            foreach (string key in this._metaTag.Keys)
            {
                util.XmlUtil.CreateChild(nodeMeta, key, this._metaTag[key]);
            }
            // Create MENU (if exists some elements in menu).
            foreach (string key in this._menuGroup.Keys)
            {
                MenuGroup menuGroup = this._menuGroup[key];
                if (menuGroup.menus.Count > 0)
                {
                    XmlNode nodeGroup = util.XmlUtil.CreateChild(nodePage, "group", "");
                    util.XmlUtil.CreateChild(nodeGroup, "id", key.ToString());
                    util.XmlUtil.CreateChild(nodeGroup, "title", menuGroup.menuTitle);
                    util.XmlUtil.CreateChild(nodeGroup, "keyword", "all");

                    foreach (object o in menuGroup.menus)
                    {
                        menuDef m = (menuDef)o;
                        XmlNode nodeWorking = util.XmlUtil.CreateChild(nodeGroup, "page", "");
                        util.XmlUtil.CreateChild(nodeWorking, "id", m.id);
                        util.XmlUtil.CreateChild(nodeWorking, "title", m.title);
                        util.XmlUtil.CreateChild(nodeWorking, "summary", m.summary);
                        if (m.icon != "")
                        {
                            util.XmlUtil.CreateChild(nodeWorking, "icon", m.icon);
                        }
                    }
                }
            }

            // Create MENU (if exists some elements in menu).
            if (this._scripts.Count > 0)
            {
                foreach (object o in this._scripts)
                {
                    XmlNode nodeGroup = util.XmlUtil.CreateChild(nodePage, "script", "");
                    util.XmlUtil.CreateChild(nodeGroup, "language", "javascript");

                    scriptDef s = (scriptDef)o;
                    if (!String.IsNullOrEmpty(s.sourceCode))
                    {
                        util.XmlUtil.AddTextNode(nodeGroup, s.sourceCode, true);
                    }
                    else if (!String.IsNullOrEmpty(s.reference))
                    {
                        util.XmlUtil.AddAttribute(nodeGroup, "location", s.reference);
                    }
                }
            }

            // Generate Scripts
            //if(!is_null($this->_scripts))
            //{
            //	foreach($this->_scripts as $script) 
            //	{		
            //		$nodeWorking = XmlUtil::CreateChild($nodePage, "script", "");
            //		XmlUtil::AddAttribute($nodeWorking, "language", "javascript");
            //		if(!is_null($script->source))
            //			XmlUtil::AddTextNode($nodeWorking, $script->source);
            //		if(!is_null($script->file))
            //			XmlUtil::AddAttribute($nodeWorking, "src", $script->file);
            //		
            //		XmlUtil::AddAttribute($nodeWorking, "location", $script->location);
            //	}
            //}

            // Process ALL XmlnukeDocumentObject existing in Collection.
            //----------------------------------------------------------
            this.generatePage(nodePage);
            //----------------------------------------------------------

            // Finalize the Create Page Execution
            util.XmlUtil.CreateChild(nodeMeta, "created", created.ToString("dd/MM/yyyy hh:mm:ss"));
            util.XmlUtil.CreateChild(nodeMeta, "modified", DateTime.Now.ToLongDateString());
            TimeSpan elapsed = DateTime.Now - created;
            double elapsedSeconds = elapsed.Seconds + elapsed.Minutes * 60 + elapsed.Hours * 24 * 60 + elapsed.Milliseconds / 1000;
            util.XmlUtil.CreateChild(nodeMeta, "timeelapsed", elapsed.ToString());
            util.XmlUtil.CreateChild(nodeMeta, "timeelapsedsec", elapsedSeconds.ToString("0.000"));

            return xmlDoc;
        }

        /// <summary>
        /// DEPRECATED. For compatibility only
        /// </summary>
        /// <returns></returns>
        public IXmlnukeDocument generatePage()
        {
            return this;
        }

    }


    /// <summary>
    /// Interface for all XmlnukeDocument.
    /// </summary>
    public interface IXmlnukeDocument
    {
        XmlDocument makeDomObject();
    }

    /// <summary>
    /// Interface for all XmlnukeDocumentObject.
    /// </summary>
    public interface IXmlnukeDocumentObject
    {
        void generateObject(XmlNode current);
    }

    /// <summary>
    /// Abstract class. Base class for XmlnukeDocumentObject implementations.
    /// </summary>
    public abstract class XmlnukeDocumentObject : IXmlnukeDocumentObject
    {
        public abstract void generateObject(XmlNode current);
    }

}
