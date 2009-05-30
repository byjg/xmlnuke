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
using System.Collections;
using System.Collections.Specialized;
using com.xmlnuke.anydataset;
using com.xmlnuke.engine;

namespace com.xmlnuke.database
{
	/// <summary>
	/// DotNetDataSet has several methods to create a DotNet DataSet object.
	/// <seealso cref="com.xmlnuke.anydataset.SingleRow"/>
	/// <seealso cref="com.xmlnuke.anydataset.Iterator"/>
	/// <seealso cref="com.xmlnuke.anydataset.IteratorFilter"/>
	/// <seealso cref="com.xmlnuke.anydataset.AnyDataSetFilenameProcessor"/>
	/// </summary>
	public class DotNetDataSet
	{
		public DotNetDataSet()
		{ }

		public static DataSet getDataSet(IIterator it)
		{
			string[] fieldNames = null;
			SingleRow sr = null;
			if (it.hasNext())
			{
				sr = it.moveNext();
				fieldNames = sr.getFieldNames();
			}
			else
			{
				return null;
			}

			DataSet ds = DotNetDataSet.getDataSet(fieldNames);

			do
			{
				DataRow row = ds.Tables[0].NewRow();
				foreach (string field in fieldNames)
				{
					row[field] = sr.getField(field);
				}
				ds.Tables[0].Rows.Add(row);

				if (it.hasNext())
				{
					sr = it.moveNext();
				}
				else
				{
					sr = null;
				}
			} while (sr != null);

			ds.AcceptChanges();

			return ds;
		}

		public static DataSet getDataSet(string[] fieldNames)
		{
			DataSet ds;
			System.Data.DataTable dt;

			ds = new DataSet();
			dt = new DataTable();

			foreach (string field in fieldNames)
			{
				DataColumn column = new DataColumn(field);
				dt.Columns.Add(column);
			}
			ds.Tables.Add(dt);

			return ds;
		}

		public static DataSet getDataSet(DBDataSet dbdata, string tableName)
		{
			IDataAdapter da = dbdata.getDataAdpater(tableName);
			DataSet ds = new DataSet();
			da.Fill(ds);

			return ds;
		}
	}
}