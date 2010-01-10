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
using System.Collections.Generic;
using System.Collections;

namespace com.xmlnuke.anydataset
{
	/// <summary>
	/// Iterator class is a structure used to navigate foward in a AnyDataSet structure.
	/// You need to use the getIterator method in a AnyDataSet class to create an Iterator.
	/// </summary>
	public class Iterator : IIterator
	{
		/// <summary>Rows elements</summary>
		private List<SingleRow> _list;
		/// <summary>Current row number</summary>
		private int curRow;

		/// <summary>
		/// Iterator constructor
		/// </summary>
		/// <param name="list">XmlNodeList</param>
		public Iterator(List<SingleRow> list)
		{
			curRow = 0;
            if (list != null)
            {
                _list = list;
            }
            else
            {
                _list = new List<SingleRow>();
            }
		}

		/// <summary>
		/// How many elements have
		/// </summary>
		public int Count()
		{
			return _list.Count;
		}

		/// <summary>
		/// Ask the Iterator is exists more rows. Use before moveNext method.
		/// </summary>
		/// <returns>True if exist more rows, otherwise false</returns>
		public bool hasNext()
		{
			return (curRow < this.Count());
		}

		/// <summary>
		/// Return the next row.
		/// </summary>
		/// <returns>SingleRow</returns>
		public SingleRow moveNext()
		{
			if (!this.hasNext())
			{
				return null;
			}
			else
			{
				return _list[curRow++];
			}
		}

        #region IEnumerable Members

        public IEnumerator GetEnumerator()
        {
            return new IteratorEnumerable(this);
        }

        #endregion
    }
}