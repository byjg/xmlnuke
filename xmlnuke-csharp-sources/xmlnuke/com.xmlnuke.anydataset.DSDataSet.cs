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

namespace com.xmlnuke.anydataset
{
	/// <summary>
	/// AnyDataSet is a simple way to store data using only XML file. 
	/// Your structure is hierarquical and each "row" contains "fields" but these structure can vary for each row.
	/// Anydataset files have extension ".anydata.xml" and have many classes to put and get data into anydataset xml file.
	/// Anydataset class just read and write files. To search elements you need use Iterator and IteratorFilter. Each row have a class SingleRow.
	/// <seealso cref="com.xmlnuke.anydataset.SingleRow"/>
	/// <seealso cref="com.xmlnuke.anydataset.Iterator"/>
	/// <seealso cref="com.xmlnuke.anydataset.IteratorFilter"/>
	/// <seealso cref="com.xmlnuke.anydataset.AnyDataSetFilenameProcessor"/>
	/// </summary>
	/// <example>
	/// Retrieve and store data in an AnydataSet XML.
	/// <code>
	/// AnydatasetFilenameProcessor guestbookFile = AnydatasetFilenameProcessor("guestbook", this._context);
	/// AnyDataSet guestbook = new AnyDataSet(guestbookFile);
	/// anydataset.Iterator it = guestbook.getIterator();
	/// while (it.hasNext())
	/// {
	///   SingleRow sr = it.moveNext();
	///   Console.WriteLine(sr.getField("fieldname1"));
	///   sr.setField("fieldname1", "newvalue");
	/// }
	/// guestbook.Save(guestbookFile);
	/// </code>
	/// </example>
	/// <example>
	/// AnydataSet structure
	/// <code>
	/// &lt;anydataset&gt;
	///   &lt;row&gt;
	///     &lt;field name="fieldname1"&gt;value of fieldname 1&lt;/field&gt;
	///     &lt;field name="fieldname2"&gt;value of fieldname 2&lt;/field&gt;
	///     &lt;field name="fieldname3"&gt;value of fieldname 3&lt;/field&gt;
	///   &lt;/row&gt;
	///   &lt;row&gt;
	///     &lt;field name="fieldname1"&gt;value of fieldname 1&lt;/field&gt;
	///     &lt;field name="fieldname4"&gt;value of fieldname 4&lt;/field&gt;
	///   &lt;/row&gt;
	/// &lt;/anydataset&gt;
	/// </code>
	/// </example>
	public class DSDataSet
	{
		protected DataSet _ds;

		public DSDataSet(DataSet ds)
		{
			this._ds = ds;
		}


		public string[] getTables()
		{
			int qtdTables = this._ds.Tables.Count;
			string[] result = new string[qtdTables];

			for (int i = 0; i < qtdTables; i++)
			{
				result[i] = this._ds.Tables[i].TableName;
			}

			return result;
		}

		/// <summary>
		/// Get an Iterator with all anydataset rows.
		/// </summary>
		/// <returns>Iterator</returns>
		public IIterator getIterator()
		{
			return this.getIterator(0, null);
		}

		public IIterator getIterator(IteratorFilter itf)
		{
			return this.getIterator(0, itf);
		}

		public IIterator getIterator(int tableNumber, IteratorFilter itf)
		{
			string tableName = this.getTables()[tableNumber];
			return this.getIterator(tableName, itf);
		}

		public IIterator getIterator(int tableNumber)
		{
			return this.getIterator(tableNumber, null);
		}

		public IIterator getIterator(string tableName)
		{
			return this.getIterator(tableName, null);
		}

		/// <summary>
		/// Get an Iterator filtered by an IteratorFilter
		/// </summary>
		/// <param name="itf">Iterator Filter</param>
		/// <returns>Iterator</returns>
		public IIterator getIterator(string tableName, IteratorFilter itf)
		{
			DataView dv = this._ds.Tables[tableName].DefaultView;
			if (itf != null)
			{
				dv.RowFilter = itf.getFilter();
			}
			return new DSIterator(dv);
		}
	}
}