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
using com.xmlnuke.engine;
using System.Xml;
using com.xmlnuke.util;

namespace com.xmlnuke.classes
{

    public class XmlnukeMediaGallery : XmlnukeCollection, IXmlnukeDocumentObject
    {
        protected Context _context;

        protected string _name = "";
        protected bool _api = false;
        protected bool _visible = true;
        protected bool _showCaptionOnThumb = false;


        public XmlnukeMediaGallery(Context context)
            : this(context, "")
        { }

        /**
        *@desc Generate page, processing yours childs.
        *@param DOMNode current
        *@return void
        */
        public XmlnukeMediaGallery(Context context, string name)
        {
            this._context = context;
            this._name = name;
            if (this._name == "")
            {
                this._name = "gallery_" + this._context.getRandomNumber(1000, 9999);
            }
        }

        public string getName()
        {
            return this._name;
        }
        public void setName(string value)
        {
            this._name = value;
        }

        public bool getApi()
        {
            return this._api;
        }
        public void setApi(bool value)
        {
            this._api = value;
        }

        public bool getVisible()
        {
            return this._visible;
        }
        public void setVisible(bool value)
        {
            this._visible = value;
        }

        public bool getShowCaptionOnThumb()
        {
            return this._showCaptionOnThumb;
        }
        public void setShowCaptionOnThumb(bool value)
        {
            this._showCaptionOnThumb = value;
        }

        public override void addXmlnukeObject(IXmlnukeDocumentObject o)
        {
            if (o is XmlnukeMediaItem)
            {
                base.addXmlnukeObject(o);
            }
            else
            {
                throw new Exception("Object need to an instance of XmlnukeMediaItem class");
            }
        }

        public void addImage(string src)
        {
            this.addImage(src, "", "", "", 0, 0);
        }
        public void addImage(string src, string thumb)
        {
            this.addImage(src, thumb, "", "", 0, 0);
        }
        public void addImage(string src, string thumb, string title)
        {
            this.addImage(src, thumb, title, "", 0, 0);
        }
        public void addImage(string src, string thumb, string title, string caption)
        {
            this.addImage(src, thumb, title, caption, 0, 0);
        }
        public void addImage(string src, string thumb, string title, string caption, int width)
        {
            this.addImage(src, thumb, title, caption, width, 0);
        }

        /**
        * Create an Media Item of image Type
        * @param src
        * @param thumb
        * @param title
        * @param caption
        * @param width
        * @param height
        * @return XmlnukeMediaItem
        */
        public void addImage(string src, string thumb, string title, string caption, int width, int height)
        {
            this.addXmlnukeObject(XmlnukeMediaItem.ImageFactory(src, thumb, title, caption, width, height));
        }


        public void addEmbed(string src, int windowWidth, int windowHeight)
        {
            this.addEmbed(src, windowWidth, windowHeight, "", "", "", 0, 0);
        }
        public void addEmbed(string src, int windowWidth, int windowHeight, string thumb)
        {
            this.addEmbed(src, windowWidth, windowHeight, thumb, "", "", 0, 0);
        }
        public void addEmbed(string src, int windowWidth, int windowHeight, string thumb, string title)
        {
            this.addEmbed(src, windowWidth, windowHeight, thumb, title, "", 0, 0);
        }
        public void addEmbed(string src, int windowWidth, int windowHeight, string thumb, string title, string caption)
        {
            this.addEmbed(src, windowWidth, windowHeight, thumb, title, caption, 0, 0);
        }
        public void addEmbed(string src, int windowWidth, int windowHeight, string thumb, string title, string caption, int width)
        {
            this.addEmbed(src, windowWidth, windowHeight, thumb, title, caption, width, 0);
        }
        /**
         * Create an Media Item of Flash, Youtube or Quicktime
         * @param src
         * @param windowWidth
         * @param windowHeight
         * @param thumb
         * @param title
         * @param caption
         * @param width
         * @param height
         * @return XmlnukeMediaItem
         */
        public void addEmbed(string src, int windowWidth, int windowHeight, string thumb, string title, string caption, int width, int height)
        {
            this.addXmlnukeObject(XmlnukeMediaItem.EmbedFactory(src, windowWidth, windowHeight, thumb, title, caption, width, height));
        }


        public void addIFrame(string src, int windowWidth, int windowHeight)
        {
            this.addIFrame(src, windowWidth, windowHeight, "", "", "", 0, 0);
        }
        public void addIFrame(string src, int windowWidth, int windowHeight, string thumb)
        {
            this.addIFrame(src, windowWidth, windowHeight, thumb, "", "", 0, 0);
        }
        public void addIFrame(string src, int windowWidth, int windowHeight, string thumb, string title)
        {
            this.addIFrame(src, windowWidth, windowHeight, thumb, title, "", 0, 0);
        }
        public void addIFrame(string src, int windowWidth, int windowHeight, string thumb, string title, string caption)
        {
            this.addIFrame(src, windowWidth, windowHeight, thumb, title, caption, 0, 0);
        }
        public void addIFrame(string src, int windowWidth, int windowHeight, string thumb, string title, string caption, int width)
        {
            this.addIFrame(src, windowWidth, windowHeight, thumb, title, caption, width, 0);
        }
        /**
         * Create an Media Item of IFrame type
         * @param src
         * @param windowWidth
         * @param windowHeight
         * @param thumb
         * @param title
         * @param caption
         * @param width
         * @param height
         * @return XmlnukeMediaItem
         */
        public void addIFrame(string src, int windowWidth, int windowHeight, string thumb, string title, string caption, int width, int height)
        {
            this.addXmlnukeObject(XmlnukeMediaItem.IFrameFactory(src, windowWidth, windowHeight, thumb, title, caption, width, height));
        }

        public void generateObject(XmlNode current)
        {
            XmlNode mediaGallery = XmlUtil.CreateChild(current, "mediagallery");
            XmlUtil.AddAttribute(mediaGallery, "name", this._name);
            XmlUtil.AddAttribute(mediaGallery, "api", (this._api ? "true" : "false"));
            XmlUtil.AddAttribute(mediaGallery, "visible", (this._visible ? "true" : "false"));
            XmlUtil.AddAttribute(mediaGallery, "showthumbcaption", (this._showCaptionOnThumb ? "true" : "false"));
            this.generatePage(mediaGallery);
        }

    }









    public class XmlnukeMediaItem : XmlnukeCollection, IXmlnukeDocumentObject
    {
        protected string _src;
        protected string _thumb;
        protected string _caption;
        protected string _title;
        protected int _width;
        protected int _height;

        /**
        *@desc Generate page, processing yours childs.
        *@param DOMNode current
        *@return void
        */
        protected XmlnukeMediaItem(string src, string thumb, string title, string caption, int width, int height)
        {
            this._src = src;
            this._thumb = thumb;
            this._caption = caption;
            this._title = title;
            this._width = width;
            this._height = height;
        }


        /**
         * Create an Media Item of image Type
         * @param src
         * @param thumb
         * @param title
         * @param caption
         * @param width
         * @param height
         * @return XmlnukeMediaItem
         */
        public static XmlnukeMediaItem ImageFactory(string src, string thumb, string title, string caption, int width, int height)
        {
            return new XmlnukeMediaItem(src, thumb, title, caption, width, height);
        }

        /**
         * Create an Media Item of Flash, Youtube or Quicktime
         * @param src
         * @param windowWidth
         * @param windowHeight
         * @param thumb
         * @param title
         * @param caption
         * @param width
         * @param height
         * @return XmlnukeMediaItem
         */
        public static XmlnukeMediaItem EmbedFactory(string src, int windowWidth, int windowHeight, string thumb, string title, string caption, int width, int height)
        {
            if (src.IndexOf("?") >= 0)
            {
                src += "&amp;";
            }
            else
            {
                src += "?";
            }

            if (windowWidth > 0)
            {
                src += "width=" + windowWidth.ToString() + "&amp;";
            }
            if (windowHeight > 0)
            {
                src += "height=" + windowHeight.ToString();
            }

            return new XmlnukeMediaItem(src, thumb, title, caption, width, height);
        }

        /**
         * Create an Media Item of IFrame type
         * @param src
         * @param windowWidth
         * @param windowHeight
         * @param thumb
         * @param title
         * @param caption
         * @param width
         * @param height
         * @return XmlnukeMediaItem
         */
        public static XmlnukeMediaItem IFrameFactory(string src, int windowWidth, int windowHeight, string thumb, string title, string caption, int width, int height)
        {
            if (src.IndexOf("?") >= 0)
            {
                src += "&amp;";
            }
            else
            {
                src += "?";
            }
            src += "iframe=true&amp;width=" + windowWidth.ToString() + "&amp;height=" + windowHeight.ToString();

            return new XmlnukeMediaItem(src, thumb, title, caption, width, height);
        }

        public void generateObject(XmlNode current)
        {
            XmlNode mediaGallery = XmlUtil.CreateChild(current, "mediaitem");
            XmlUtil.AddAttribute(mediaGallery, "src", this._src);
            XmlUtil.AddAttribute(mediaGallery, "thumb", this._thumb);
            XmlUtil.AddAttribute(mediaGallery, "title", this._title);
            XmlUtil.AddAttribute(mediaGallery, "caption", this._caption);
            if (this._width > 0)
                XmlUtil.AddAttribute(mediaGallery, "width", this._width);
            if (this._height > 0)
                XmlUtil.AddAttribute(mediaGallery, "height", this._height);
        }
    }
}