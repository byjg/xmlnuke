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
		private XmlDocument _anyDataSet;
		/// <summary>Internal structure represent the current SingleRow</summary>
		private SingleRow _singleRow;
		/// <summary>XML node represents ANYDATASET node</summary>
		private XmlNode _nodeRoot;
		/// <summary>Current node anydataset works</summary>
		private XmlNode _currentRow;

		private string _path;

		protected void defineNodeRoot()
		{
			_nodeRoot = _anyDataSet.SelectSingleNode("anydataset");
			if (_nodeRoot == null)
				throw new Exception("XML isnt a valid AnydataSet document");
		}

		/// <summary>
		/// Private method used to create Empty Anydataset
		/// </summary>
		protected void CreateNew()
		{
			_anyDataSet = util.XmlUtil.CreateXmlDocument();
			_anyDataSet.LoadXml("<anydataset/>");
			this.defineNodeRoot();
		}

		/// <summary>
		/// Private method used to read and populate anydataset class from specified file
		/// </summary>
		/// <param name="filepath">Path and Filename to be read</param>
		protected void CreateFrom(string filepath)
		{
			if (!util.FileUtil.Exists(filepath))
			{
				this.CreateNew();
			}
			else
			{
				_anyDataSet = util.XmlUtil.CreateXmlDocument(filepath);
				this.defineNodeRoot();
			}
			_path = filepath;
		}

		public void CreateFromXml(string xml)
		{
			_anyDataSet = util.XmlUtil.CreateXmlDocument();
			_anyDataSet.LoadXml(xml);
			this.defineNodeRoot();
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
			this._path = null;
			this.CreateNew();
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
			this.CreateFrom(file.FullQualifiedNameAndPath());
		}

		/// <summary>
		/// AnyDataSet constructor. Create an anydateset class from the file defined in the physhical path.
		/// </summary>
		/// <param name="filepath">Path and Filename to be read</param>
		public AnyDataSet(string filepath)
		{
			this.CreateFrom(filepath);
		}

		/// <summary>
		/// Returns the AnyDataSet XML representative structure.
		/// </summary>
		/// <returns>XML String</returns>
		public string XML()
		{
			return _anyDataSet.OuterXml;
		}

		/// <summary>
		/// Returns the AnyDataSet XmlDocument representive object
		/// </summary>
		/// <returns>XmlDocument object</returns>
		public XmlDocument getDomObject()
		{
			return this._anyDataSet;
		}

		/// <summary>
		/// Save the AnyDataSet file to disk. All operations running in memory. You need save to disk to persist data.
		/// </summary>
		/// <param name="file">AnydatasetBaseFilenamePrcessor</param>
		public void Save(processor.AnydatasetBaseFilenameProcessor file)
		{
			_path = file.FullQualifiedNameAndPath();
			_anyDataSet.Save(_path);
		}

		/// <summary>
		/// Save the AnyDataSet file to disk. All operations running in memory. You need save to disk to persist data.
		/// </summary>
		/// <param name="filepath">File name and Path</param>
		public void Save(string filepath)
		{
			_path = filepath;
			_anyDataSet.Save(_path);
		}

		public void Save()
		{
			_anyDataSet.Save(_path);
		}

		/// <summary>
		/// Append one row to AnyDataSet. 
		/// </summary>
		public void appendRow()
		{
			_currentRow = util.XmlUtil.CreateChild(_nodeRoot, "row", "");
			_singleRow = new SingleRow(_currentRow);
		}


		/// <summary>
		/// Import a Single to row to an AnyDataSet. 
		/// </summary>
		/// <param name="sr">SingleRow object</param>
		public void appendRow(SingleRow sr)
		{
			this._currentRow = util.XmlUtil.CreateChild(this._nodeRoot, "row");
			util.XmlUtil.AddNodeFromNode(this._currentRow, sr.getDomObject());
			this._singleRow = new SingleRow(this._currentRow);
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
			if (row > _nodeRoot.ChildNodes.Count - 1)
			{
				this.appendRow();
			}
			else
			{
				_currentRow = util.XmlUtil.CreateChildBefore(_nodeRoot, "row", "", row);
				_singleRow = new SingleRow(_currentRow);
			}
		}

		public void insertRowBefore(XmlNode nodeRow)
		{
			_currentRow = util.XmlUtil.CreateChildBefore("row", "", nodeRow);
			_singleRow = new SingleRow(_currentRow);
		}

		/// <summary>
		/// Remove specified row position.
		/// </summary>
		/// <param name="row">Row number (sequential)</param>
		public void removeRow(XmlNode row)
		{
			_nodeRoot.RemoveChild(row);
		}

		/// <summary>
		/// Add a single string field to an existing row
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">Field value</param>
		public void addField(string name, string value)
		{
			_singleRow.AddField(name, value);
		}

		/// <summary>
		/// Add a single datetime field to an existing row
		/// </summary>
		/// <param name="name">Field name</param>
		/// <param name="value">Field value</param>
		public void addField(string name, DateTime value)
		{
			_singleRow.AddField(name, value);
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
				return new Iterator(_nodeRoot.ChildNodes);
			}
			else
			{
				XmlNodeList xnl = _anyDataSet.SelectNodes(itf.getXPath());
				return new Iterator(xnl);
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

		public void Sort(string fieldName, ISortCompare sc)
		{
			XmlNodeList list = this._nodeRoot.ChildNodes;

			// Extract element to be sorted
			SortStructure[] Array = new SortStructure[list.Count];
			int i = 0;
			try
			{
				foreach (XmlElement node in list)
				{
					Array[i].index = i;
					Array[i++].value = node.SelectSingleNode("field[@name='" + fieldName + "']").InnerText;
				}
			}
			catch (Exception ex)
			{
				throw new Exception("Sort Error: '" + fieldName + "' doesnt exist! " + ex.Message);
			}

			// Sort Array
			this.Sort(Array, 0, Array.Length - 1, sc);

			// Create new Anydataset
			XmlDocument anydata = new XmlDocument();
			anydata.LoadXml("<anydataset/>");
			XmlNode root = anydata.DocumentElement;
			foreach (SortStructure item in Array)
			{
				XmlNode row = util.XmlUtil.CreateChild(root, "row");
				util.XmlUtil.AddNodeFromNode(row, list[item.index]);
			}

			// Setup new Data
			this._anyDataSet = anydata;
			this.defineNodeRoot();
			this._currentRow = null;
		}

		protected struct SortStructure
		{
			public object value;
			public int index;
		}
		private void swap(SortStructure[] Array, int Left, int Right)
		{
			SortStructure temp = Array[Left];
			Array[Left] = Array[Right];
			Array[Right] = temp;
		}
		private void Sort(SortStructure[] Array, int Left, int Right, ISortCompare sc)
		{
			int LHold = Left;
			int RHold = Right;
			Random ObjRan = new Random();
			int Pivot = ObjRan.Next(Left, Right);
			swap(Array, Pivot, Left);
			Pivot = Left;
			Left++;

			while (Right >= Left)
			{
				if (sc.Compare(Array[Left].value, Array[Pivot].value) >= 0 && sc.Compare(Array[Right].value, Array[Pivot].value) < 0)
					swap(Array, Left, Right);
				else if (sc.Compare(Array[Left].value, Array[Pivot].value) >= 0)
					Right--;
				else if (sc.Compare(Array[Right].value, Array[Pivot].value) < 0)
					Left++;
				else
				{
					Right--;
					Left++;
				}
			}
			swap(Array, Pivot, Right);
			Pivot = Right;
			if (Pivot > LHold)
				this.Sort(Array, LHold, Pivot, sc);
			if (RHold > Pivot + 1)
				this.Sort(Array, Pivot + 1, RHold, sc);
		}


		/*
		public static AnyDataSet orderBy(IIterator iterator, string field)
		{
			return AnyDataSet.orderBy(iterator, field, 'A');
		}
	    
		public static AnyDataSet orderBy(IIterator iterator, string field, char order)
		{
			AnyDataSet result = new AnyDataSet();
	    	
			while (iterator.hasNext())
			{
				SingleRow sr = iterator.moveNext();
	
				IIterator itResult = result.getIterator();
			
				XmlNode nodeBefore = null;
				bool added = false;
				while (itResult.hasNext())
				{
					SingleRow srResult = itResult.moveNext();
					bool compare;
					if (order == 'A')
					{
						compare = (srResult.getField(field).CompareTo(sr.getField(field)) < 0);
					}
					else 
					{
						compare = (srResult.getField(field).CompareTo(sr.getField(field)) > 0);
					}
					if (compare)
					{
						nodeBefore = srResult.getDomObject();
					}
					else 
					{
						if (nodeBefore == null)
						{
							result.appendRow();
						}
						else
						{
							result.insertRowBefore(nodeBefore);
						}
						added = true;
						break;
					}
				}
	
				if (!added)
				{
					result.appendRow();
				}
	    		
				string[] arr = sr.getFieldNames();
				foreach (string fieldname in arr)
				{
					result.addField(fieldname, sr.getField(fieldname));
				}
	    		
			}
	
			return result;   	
		}
		*/

	}

	public interface ISortCompare
	{
		int Compare(object o1, object o2);
	}

	public class SortCompareNumber : ISortCompare
	{
		public int Compare(object o1, object o2)
		{
			Double i1 = 0, i2 = 0;
			Double.TryParse(o1.ToString(), out i1);
			Double.TryParse(o2.ToString(), out i2);
			return ((i1 == i2) ? 0 : ((i1 < i2) ? -1 : 1));
		}
	}

	public class SortCompareString : ISortCompare
	{
		public int Compare(object o1, object o2)
		{
			string s1 = "", s2 = "";
			if (o1 != null)
			{
				s1 = o1.ToString();
			}
			if (o2 != null)
			{
				s2 = o2.ToString();
			}
			return (s1.CompareTo(s2));
		}
	}

	public class SortCompareDate : ISortCompare
	{
		protected com.xmlnuke.classes.DATEFORMAT dateFormat = com.xmlnuke.classes.DATEFORMAT.YMD;

		public com.xmlnuke.classes.DATEFORMAT DateFormat
		{
			get { return this.dateFormat; }
			set { this.dateFormat = value; }
		}

		public int Compare(object o1, object o2)
		{
			DateTime d1, d2;
			try
			{
				d1 = util.DateUtil.ConvertDate(o1.ToString(), this.dateFormat);
			}
			catch
			{
				d1 = new DateTime(2500, 1, 1);
			}

			try
			{
				d2 = util.DateUtil.ConvertDate(o2.ToString(), this.dateFormat);
			}
			catch
			{
				d2 = new DateTime(2500, 1, 1);
			}

			return d1.CompareTo(d2);
		}
	}
}