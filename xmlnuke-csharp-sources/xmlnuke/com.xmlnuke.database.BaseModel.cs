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

namespace com.xmlnuke.database
{

	/// <summary>
	/// BaseModel is a concept for data exchange. You need create class with String properties. 
	/// </summary>
	public abstract class BaseModel
	{
		
		public void bindSingleRow(SingleRow sr)
		{
			Type t = this.GetType();
			
			string[] fields = sr.getFieldNames();

			foreach (String field in fields)
			{
				PropertyInfo prop = t.GetProperty(field);
				
				if (prop.CanWrite && (prop.PropertyType.FullName == "System.String"))
				{
					prop.SetValue(this, sr.getField(field), null);
				}
			}
		}	

		public void bindIterator(IIterator it)
		{
			if (it.hasNext()) 
			{
				SingleRow sr = it.moveNext();
				this.bindSingleRow(sr);
			}
		}
		
	}
}