/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification and Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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
using System.Reflection;
using System.Collections;
using System.Collections.Specialized;
using System.Xml;
using com.xmlnuke.engine;
using com.xmlnuke.classes;
using com.xmlnuke.anydataset;
using com.xmlnuke.util;
using System.Text.RegularExpressions;

namespace com.xmlnuke.database
{

	/// <summary>
	/// BaseModel is a concept for data exchange. You need create class with String properties. 
	/// </summary>
	public abstract class BaseModel
	{
		protected string[] _propertyPattern = new string[]{@"(\w*)", "$1"};

        public BaseModel() { }

	    public BaseModel(SingleRow o)
	    {
		    this.bindSingleRow(o);
	    }

        public BaseModel(IIterator o)
        {
            this.bindIterator(o);
        }


	    public void setPropertyPattern(string pattern, string replace)
	    {
		    this._propertyPattern = new string[]{pattern, replace};
	    }
        public string[] getPropertyPattern()
        {
            return this._propertyPattern;
        }

		public void bindSingleRow(SingleRow sr)
		{
            this.bindObject(sr);
		}	

		public void bindIterator(IIterator it)
		{
			if (it.hasNext()) 
			{
				SingleRow sr = it.moveNext();
				this.bindSingleRow(sr);
			}
		}

        public void bindFromContext(Context context)
        {
            this.bindObject(context);
        }

        protected void bindObject(object o)
        {
            Type t = this.GetType();

			PropertyInfo[] pi = t.GetProperties();
			foreach (PropertyInfo prop in pi)
			{
				if (prop.CanWrite && (prop.PropertyType.FullName == "System.String") && (prop.Name != "_propertyPattern"))
				{
                    string propValue = "";
                    bool change = false;
                    if (o is SingleRow)
                        propValue = ((SingleRow)o).getField(prop.Name);
                    else if (o is Context)
                        propValue = ((Context)o).ContextValue(prop.Name);

                    if (propValue != "")
					    prop.SetValue(this, propValue, null);
				}
			}
        }
    }
}