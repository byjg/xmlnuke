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
using System.Text.RegularExpressions;
using com.xmlnuke.engine;
using com.xmlnuke.processor;
using com.xmlnuke.util;

namespace com.xmlnuke.anydataset
{
	public class TextFileIterator : IIterator
	{
		/**
		*@var Context
		*/
		private Context _context;

		protected string[] _fields;

		protected string _fieldexpression;

		protected System.IO.StreamReader _stream;

		protected string _currentLine;

		protected bool _checkHasNext;

		/**
		*@access public
		*@return IIterator
		*/
		public TextFileIterator(Context context, System.IO.Stream stream, string[] fields, string fieldexpression)
		{
			this._context = context;
			this._fields = fields;
			this._fieldexpression = fieldexpression;
			this._stream = new System.IO.StreamReader(stream);
			this._currentLine = null;
			this._checkHasNext = false;
		}

		/**
		*@access public
		*@return int
		*/
		public int Count()
		{
			return -1;
		}

		/**
		*@access public
		*@return bool
		*/
		public bool hasNext()
		{
			if (this._stream != null)
			{
				this._currentLine = this._stream.ReadLine();
				this._checkHasNext = true;
				if (this._currentLine == null)
				{
					this._stream.Close();
					this._stream = null;
				}
			}
			return (this._currentLine != null);
		}

		/**
		*@access public
		*@return SingleRow
		*/
		public SingleRow moveNext()
		{
			if (!this._checkHasNext || (this._currentLine == null))
				this.hasNext();

			this._checkHasNext = false;
			if (this._currentLine == null)
			{
				return null;
			}
			else
			{
				Regex regex = new Regex(this._fieldexpression);
				string[] cols = regex.Split(this._currentLine);

				AnyDataSet any = new AnyDataSet();
				any.appendRow();
				for (int i = 0; (i < this._fields.Length) && (i < cols.Length); i++)
				{
					any.addField(this._fields[i].ToString(), cols[i]);
				}
				IIterator it = any.getIterator();
				SingleRow sr = it.moveNext();
				return sr;
			}
		}
	}
}