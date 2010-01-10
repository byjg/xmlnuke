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
using System.Collections;
using System.Collections.Generic;

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
	public class AnyDataSet
	{
		/// <summary>Internal structure to store anydataset elements</summary>
        private List<SingleRow> _collection;
        /// <summary>Current node anydataset works</summary>
		private int _currentRow;

		private string _path;

		/// <summary>
		/// Private method used to create Empty Anydataset
		/// </summary>
        protected void Create(string filepath)
		{
            this._collection = new List<SingleRow>();
            this._currentRow = -1;
            this._path = filepath;

            if (filepath != null && util.FileUtil.Exists(filepath))
            {
			    XmlDocument anyDataSet = util.XmlUtil.CreateXmlDocument(filepath);

    			XmlNodeList rows = anyDataSet.GetElementsByTagName( "row" );
			    foreach (XmlNode row in rows)
			    {
				    SingleRow sr = new SingleRow();
				    XmlNodeList fields =  row.SelectNodes("field");
				    foreach (XmlNode field in fields)
				    {
					    sr.AddField(field.Attributes["name"].Value, field.InnerXml);
				    }
				    sr.acceptChanges();
				    this._collection.Add(sr);
			    }
			    this._currentRow = this._collection.Count-1;
            }
		}


		/// <summary>
		/// AnyDataSet constructor. Create an empty anydata struture.
		/// </summary>
		/// <example>
		/// <code>
		/// AnyDataSet any = new AnyDataSet();
		/// </code>
		/// </example>
		public AnyDataSet()
		{
			this.Create(null);
		}

		/// <summary>
		/// AnyDataSet constructor. Create an anydataset class from the file defined by AnydatasetBaseFilenameProcessor. 
		/// </summary>
		/// <param name="file">AnydatasetBaseFilenameProcessor</param>
		/// <example>
		/// <code>
		/// file = new AnydatasetFilenameProcessor("guestbook", context);
		/// AnyDataSet any = new AnyDataSet(file);
		/// </code>
		/// </example>
		public AnyDataSet(processor.AnydatasetBaseFilenameProcessor file)
		{
			this.Create(file.FullQualifiedNameAndPath());
		}

		/// <summary>
		/// AnyDataSet constructor. Create an anydateset class from the file defined in the physhical path.
		/// </summary>
		/// <param name="filepath">Path and Filename to be read</param>
		public AnyDataSet(string filepath)
		{
			this.Create(filepath);
		}

		/// <summary>
		/// Returns the AnyDataSet XML representative structure.
		/// </summary>
		/// <returns>XML String</returns>
		public string XML()
		{
			return this.getDomObject().OuterXml;
		}

		/// <summary>
		/// Returns the AnyDataSet XmlDocument representive object
		/// </summary>
		/// <returns>XmlDocument object</returns>
		public XmlDocument getDomObject()
		{
		    XmlDocument anyDataSet = util.XmlUtil.CreateXmlDocumentFromStr( "<anydataset/>" );
            XmlNodeList temp = anyDataSet.GetElementsByTagName("anydataset");
		    XmlNode nodeRoot = temp[0];
		    foreach (SingleRow sr in this._collection)
		    {
			    XmlNode row = sr.getDomObject();
                //XmlNode nodeRow = row.SelectSingleNode("row");
			    XmlNode newRow = util.XmlUtil.CreateChild(nodeRoot, "row");
			    util.XmlUtil.AddNodeFromNode(newRow, row);
		    }

		    return anyDataSet;
		}

		/// <summary>
		/// Save the AnyDataSet file to disk. All operations running in memory. You need save to disk to persist data.
		/// </summary>
		/// <param name="file">AnydatasetBaseFilenamePrcessor</param>
		public void Save(processor.AnydatasetBaseFilenameProcessor file)
		{
            this.Save(file.FullQualifiedNameAndPath());
		}

		/// <summary>
		/// Save the AnyDataSet file to disk. All operations running in memory. You need save to disk to persist data.
		/// </summary>
		/// <param name="filepath">File name and Path</param>
		public void Save(string filepath)
		{
			_path = filepath;
            if (_path == null)
            {
                throw new Exception("No such file path to save anydataset");
            }
            this.getDomObject().Save(_path);
		}

		public void Save()
		{
			this.Save(_path);
		}

		/// <summary>
		/// Append one row to AnyDataSet. 
		/// </summary>
		public void appendRow()
		{
            this.appendRow(null);
		}


		/// <summary>
		/// Import a Single to row to an AnyDataSet. 
		/// </summary>
		/// <param name="sr">SingleRow object</param>
		public void appendRow(SingleRow sr)
		{
            if (sr == null)
            {
                sr = new SingleRow();
            }
            this._collection.Add(sr);
            sr.acceptChanges();
			this._currentRow = this._collection.Count - 1;
		}

		public void import(IIterator it)
		{
			while (it.hasNext())
			{
				SingleRow sr = it.moveNext();
				this.appendRow(sr);
			}
		}

		/// <summary>
		/// Insert one row before specified position.
		/// </summary>
		/// <param name="row">Row number (sequential)</param>
		public void insertRowBefore(int row)
		{
            this.insertRowBefore(row, null);
		}

		public void insertRowBefore(int row, SingleRow sr)
		{
            if (row > this._collection.Count - 1)
            {
                this.appendRow(sr);
            }
            else
            {
                this._collection.Insert(row, (sr == null ? new SingleRow() : sr));
                _currentRow = row;
            }
        }

		/// <summary>
		/// Remove specified row position.
		/// </summary>
		/// <param name="row">Row number (sequential)</param>
		public void removeRow(SingleRow row)
		{
            this._collection.Remove(row);
		}

        public void removeRow(int row)
        {
            this._collection.RemoveAt(row);
            this._currentRow = this._collection.Count - 1;
        }

        public void removeRow()
        {
            this.removeRow(this._currentRow);
        }

        /// <summary>
		/// Add a single string field to an existing row
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">Field value</param>
		public void addField(string name, string value)
		{
            if (this._currentRow < 0)
            {
                this.appendRow();
            }
			this._collection[this._currentRow].AddField(name, value);
		}

		/// <summary>
		/// Add a single datetime field to an existing row
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">Field value</param>
		public void addField(string name, DateTime value)
		{
            if (this._currentRow < 0)
            {
                this.appendRow();
            }
            this._collection[this._currentRow].AddField(name, value);
        }

		/// <summary>
		/// Get an Iterator with all anydataset rows.
		/// </summary>
		/// <returns>Iterator</returns>
		public Iterator getIterator()
		{
			return this.getIterator(null);
		}

		/// <summary>
		/// Get an Iterator filtered by an IteratorFilter
		/// </summary>
		/// <param name="itf">Iterator Filter</param>
		/// <returns>Iterator</returns>
		public Iterator getIterator(IteratorFilter itf)
		{
			if (itf == null)
			{
				return new Iterator(this._collection);
			}
			else
			{
				return new Iterator(itf.match(this._collection));
			}
		}

		/// <summary>
		/// Get an array filtered by an IteratorFilter. The item array is defined by the contents existing in fieldName.
		/// </summary>
		/// <param name="itf"></param>
		/// <param name="fieldName"></param>
		/// <returns></returns>
		public System.Collections.ArrayList getArray(IteratorFilter itf, string fieldName)
		{
			Iterator it = getIterator(itf);
			System.Collections.ArrayList result = new System.Collections.ArrayList();
			while (it.hasNext())
			{
				SingleRow sr = it.moveNext();
				result.Add(sr.getField(fieldName));
			}
			return result;
		}

		public void Sort(IComparer<SingleRow> sc)
		{
            this._collection.Sort(sc);
		}

	}





    public abstract class SortCompare : IComparer<SingleRow>
    {
        protected string _fieldname;
        public SortCompare(string fieldname)
        {
            this._fieldname = fieldname;
        }

        public virtual int Compare(SingleRow o1, SingleRow o2)
        {
            return 0;
        }
    }

	public class SortCompareNumber : SortCompare
	{
        public SortCompareNumber(string fieldname) : base(fieldname)
        {
        }

        public override int Compare(SingleRow o1, SingleRow o2)
        {
			Double i1 = 0, i2 = 0;
			Double.TryParse(o1.getField(this._fieldname), out i1);
            Double.TryParse(o2.getField(this._fieldname), out i2);
			return ((i1 == i2) ? 0 : ((i1 < i2) ? -1 : 1));
		}
	}

    public class SortCompareString : SortCompare
	{
        public SortCompareString(string fieldname) : base(fieldname)
        {
        }
        public override int Compare(SingleRow o1, SingleRow o2)
        {
			string s1 = o1.getField(this._fieldname);
			string s2 = o2.getField(this._fieldname);
			return (s1.CompareTo(s2));
		}
	}

    public class SortCompareDate : SortCompare
	{
		protected com.xmlnuke.classes.DATEFORMAT dateFormat = com.xmlnuke.classes.DATEFORMAT.YMD;

        public SortCompareDate(string fieldname) : base(fieldname)
        {
        }

		public com.xmlnuke.classes.DATEFORMAT DateFormat
		{
			get { return this.dateFormat; }
			set { this.dateFormat = value; }
		}

        public override int Compare(SingleRow o1, SingleRow o2)
        {
			DateTime d1, d2;
			try
			{
				d1 = util.DateUtil.ConvertDate(o1.getField(this._fieldname), this.dateFormat);
			}
			catch
			{
				d1 = new DateTime(2500, 1, 1);
			}

			try
			{
				d2 = util.DateUtil.ConvertDate(o2.getField(this._fieldname), this.dateFormat);
			}
			catch
			{
				d2 = new DateTime(2500, 1, 1);
			}

			return d1.CompareTo(d2);
		}
	}
}