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
using System.Data;
using com.xmlnuke.engine;
using com.xmlnuke.classes;
using System.Collections;

namespace com.xmlnuke.anydataset
{

	public class DSIterator : IIterator
	{
		protected DataView _dv;
		protected int _currentRow;

		/// <summary>
		/// Class it implements IIterator interface. 
		/// You need to use the getIterator method in a AnyDataSet class to create an Iterator.
		/// </summary>
		/// <param name="reader">DataView .NET</param>
		/// <param name="context">XMLNuke context</param>
		/// <seealso cref="com.xmlnuke.anydataset.DBDataSet">DBDataSet</seealso>
		public DSIterator(DataView dv)
		{
			this._currentRow = 0;
			this._dv = dv;
		}

		/// <summary>
		/// Get an array with all field names existing in DataSet.
		/// </summary>
		/// <returns>String with field names</returns>
		public string[] getFieldNames()
		{
			int qtd = this._dv.Table.Columns.Count;
			string[] result = new string[qtd];

			for (int i = 0; i < qtd; i++)
			{
				result[i] = this._dv.Table.Columns[i].ColumnName;
			}

			return result;
		}

		/// <summary>
		/// Return record count;
		/// </summary>
		/// <returns>Record count</returns>
		public int Count()
		{
			return this._dv.Count;
		}

		/// <summary>
		/// Check if exists more records and move the pointer to the next record.
		/// </summary>
		/// <returns>True if exists more records.</returns>
		public bool hasNext()
		{
			return this._currentRow < this.Count();
		}

		/// <summary>
		/// Return the current record.
		/// </summary>
		/// <returns>SingleRow it contains the current record.</returns>
		public SingleRow moveNext()
		{
			if (this.hasNext())
			{
				AnyDataSet any = new AnyDataSet();
				any.appendRow();

				for (int i = 0; i < this._dv.Table.Columns.Count; i++)
				{
					string fieldName = this._dv.Table.Columns[i].ColumnName;
					string fieldValue = this._dv.Table.Rows[this._currentRow][i].ToString();
					any.addField(fieldName, fieldValue);
				}

				this._currentRow++;
				Iterator it = any.getIterator();
				return it.moveNext();
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