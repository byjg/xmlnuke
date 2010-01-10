/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  CSharp Implementation: Joao Gilberto Magalhaes
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
using System.Collections;
using System.Collections.Specialized;
using System.Text.RegularExpressions;
using com.xmlnuke.engine;
using com.xmlnuke.processor;
using com.xmlnuke.util;

namespace com.xmlnuke.anydataset
{
	public class ArrayIterator : IIterator
	{
		/**
		*@var Context
		*/
		protected NameValueCollection _rows;

		protected int _currentRow;

		/**
		*@access public
		*@return IIterator
		*/
		public ArrayIterator(NameValueCollection rows)
		{
			this._currentRow = 0;
			this._rows = rows;
		}

		/**
		*@access public
		*@return int
		*/
		public int Count()
		{
			return this._rows.Keys.Count;
		}

		/**
		*@access public
		*@return bool
		*/
		public bool hasNext()
		{
			return (this._currentRow < this.Count());
		}

		/**
		*@access public
		*@return SingleRow
		*/
		public SingleRow moveNext()
		{
			if (this.hasNext())
			{
				string[] cols = this._rows[this._currentRow].Split('\xFE');

				AnyDataSet any = new AnyDataSet();
				any.appendRow();
				any.addField("id", this._currentRow.ToString());
				any.addField("key", this._rows.Keys[this._currentRow]);
				for (int i = 0; i < cols.Length; i++)
				{
					string[] field = cols[i].Split('\xFF');
					if (field.Length == 2)
					{
						any.addField(field[0].ToLower(), field[1]);
					}
					else
					{
						any.addField("value", field[0]);
					}
				}
				IIterator it = any.getIterator(null);
				SingleRow sr = it.moveNext();
				this._currentRow++;
				return sr;
			}
			else
			{
				return null;
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