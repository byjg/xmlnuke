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
	public class TextFileDataSet
	{

		public const string CSVFILE = "[|,;](?=(?:[^\\\"]*\"[^\\\"]*\")*(?![^\\\"]*\\\"))";
		//public const string CSVFILE2=";(?=(?:[^\\\"]*\"[^\\\"]*\")*(?![^\\\"]*\\\"))";

		protected const string HTTP = "HTTP";
		protected const string FILE = "FILE";

		protected Context _context = null;

		protected string _source;

		protected string[] _fields;

		protected string _fieldexpression;

		protected string _sourceType;


		public TextFileDataSet(Context context, string source, string[] fields)
			:
			this(context, source, fields, TextFileDataSet.CSVFILE)
		{ }

		public TextFileDataSet(Context context, FilenameProcessor source, string[] fields)
			:
			this(context, source.FullQualifiedNameAndPath(), fields, TextFileDataSet.CSVFILE)
		{ }

		public TextFileDataSet(Context context, FilenameProcessor source, string[] fields, string fieldexpression)
			:
			this(context, source.FullQualifiedNameAndPath(), fields, fieldexpression)
		{ }

		public TextFileDataSet(Context context, string source, string[] fields, string fieldexpression)
		{
			this._source = source;
			if (source.IndexOf("http://") < 0)
			{
				if (!FileUtil.Exists(this._source))
				{
					throw new Exception("The specified file " + this._source + " does not exists");
				}

				this._sourceType = TextFileDataSet.FILE;
			}
			else
			{
				this._sourceType = TextFileDataSet.HTTP;
			}


			this._context = context;
			this._fields = fields;

			if ((fieldexpression == "") || (fieldexpression == null))
			{
				this._fieldexpression = TextFileDataSet.CSVFILE;
			}
			else
			{
				this._fieldexpression = fieldexpression;
			}
		}

		/**
		*@access public
		*@param string sql
		*@param array array
		*@return DBIterator
		*/
		public IIterator getIterator()
		{
			//errno = null;
			//errstr = null;
			if (this._sourceType == TextFileDataSet.HTTP)
			{
				// Expression Regular:
				// [2]: http or ftp (s0)
				// [4]: Server name (a0)
				// [5]: Full Path   (p0)
				// [6]: Query       (q1)
				//string http=@"^(?<s1>(?<s0>[^:/\?#]+):)?(?<a1>" 
				//      + @"//(?<a0>[^/\?#]*))?(?<p0>[^\?#]*)" 
				//      + @"(?<q1>\?(?<q0>[^#]*))?" 
				//      + @"(?<f1>#(?<f0>.*))?";
				//Regex pat = new Regex(http);
				//string[] urlParts = pat.Split(this._source);


				System.Net.WebClient webclient = new System.Net.WebClient();

				System.IO.Stream stream = webclient.OpenRead(this._source);

				IIterator it = new TextFileIterator(this._context, stream, this._fields, this._fieldexpression);
				return it;
			}
			else
			{
				System.IO.FileStream file = System.IO.File.OpenRead(this._source);
				if (file.Handle.Equals(null))
				{
					throw new Exception("TextFileDataSet File open error");
				}
				else
				{
					try
					{
						IIterator it = new TextFileIterator(this._context, file, this._fields, this._fieldexpression);
						return it;
					}
					catch (Exception ex)
					{
						file.Close();
						throw ex;
					}
				}
			}
		}

	}
}