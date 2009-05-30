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
using System.Collections;
using System.Collections.Specialized;
using System.Text;
using System.Web;
using System.Reflection;

using System.Data;

using com.xmlnuke.anydataset;
using com.xmlnuke.processor;
using com.xmlnuke.international;

namespace com.xmlnuke.util
{
	public class Debug
	{
		public static void Print(string message)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug: </font></b>" + message + "<br>");
		}

		public static void Print(object[] message)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug: </font></b>" + message.GetType().Name + "[" + message.Length.ToString() + "]: (");
			foreach (object o in message)
			{
				HttpContext.Current.Response.Write(o.ToString() + ", ");
			}
			HttpContext.Current.Response.Write(")<br>");
		}

		public static void Print(ICollection message)
		{
			Debug.Print(message, true);
		}
		protected static void Print(ICollection message, bool header)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			if (header)
				HttpContext.Current.Response.Write("<b><font color='red'>Debug: </font></b>" + message.GetType().Name + "[" + message.Count.ToString() + "]: (");

			IEnumerator enumerator = message.GetEnumerator();
			while (enumerator.MoveNext())
			{
				HttpContext.Current.Response.Write(enumerator.Current.ToString());
				if (enumerator.Current is ICollection)
				{
					HttpContext.Current.Response.Write(" [");
					Debug.Print((ICollection)enumerator.Current, false);
					HttpContext.Current.Response.Write(" ] ");
				}
				HttpContext.Current.Response.Write(", ");
			}
			HttpContext.Current.Response.Write(")<br>");
		}

		public static void Print(NameValueCollection message)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug: </font></b><br>");
			foreach (string s in message.Keys)
			{
				HttpContext.Current.Response.Write("<b>" + s + "</b>=" + message[s] + "<br>");
			}
			HttpContext.Current.Response.Write("<br>");
		}

		public static void Print(IIterator it)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			int contador = 0;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug Iterator: </font></b>" + it.ToString() + "<br>");
			string[] fields = null;
			HttpContext.Current.Response.Write("<table border=1>");
			while (it.hasNext() && (contador++ < 20))
			{
				SingleRow sr = it.moveNext();
				if (fields == null)
				{
					fields = sr.getFieldNames();
					HttpContext.Current.Response.Write("<tr>");
					HttpContext.Current.Response.Write("<td bgcolor=silver><b>#</b></td>");
					foreach (string s in fields)
					{
						HttpContext.Current.Response.Write("<td bgcolor=silver><b>" + s + "</b></td>");
					}
					HttpContext.Current.Response.Write("</tr>");
				}
				//
				HttpContext.Current.Response.Write("<tr>");
				HttpContext.Current.Response.Write("<td>" + contador.ToString() + "</td>");
				foreach (string s in fields)
				{
					HttpContext.Current.Response.Write("<td>" + sr.getField(s) + "</td>");
				}
				HttpContext.Current.Response.Write("</tr>");
			}
			HttpContext.Current.Response.Write("</table>");
		}

		public static void Print(SingleRow sr)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>SingleRow: </font></b>" + sr.ToString() + "<br>");
			string[] fields = sr.getFieldNames();
			HttpContext.Current.Response.Write("<tr>");
			foreach (string s in fields)
			{
				HttpContext.Current.Response.Write("<td bgcolor=silver><b>" + s + "</b></td>");
			}
			HttpContext.Current.Response.Write("</tr>");

			//
			HttpContext.Current.Response.Write("<tr>");
			foreach (string s in fields)
			{
				HttpContext.Current.Response.Write("<td>" + sr.getField(s) + "</td>");
			}
			HttpContext.Current.Response.Write("</table>");
		}

		public static void Print(AnyDataSet anydata)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug AnyDataSet: </font></b>" + anydata.ToString() + "<br>");
			Debug.Print(anydata.getIterator());
		}

		public static void Print(FilenameProcessor fileproc)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<font color='red'><b>Debug: " + fileproc.GetType().Name + "</b></font><br>");
			HttpContext.Current.Response.Write("<b>Path Suggested: </b>" + fileproc.PathSuggested() + "<br>");
			HttpContext.Current.Response.Write("<b>Private Path: </b>" + fileproc.PrivatePath() + "<br>");
			HttpContext.Current.Response.Write("<b>Shared Path: </b>" + fileproc.SharedPath() + "<br>");
			HttpContext.Current.Response.Write("<b>Name: </b>" + fileproc.ToString() + "<br>");
			HttpContext.Current.Response.Write("<b>Extension: </b>" + fileproc.Extension() + "<br>");
			HttpContext.Current.Response.Write("<b>Full Name: </b>" + fileproc.FullQualifiedName() + "<br>");
			HttpContext.Current.Response.Write("<b>Full Qualified Name And Path: </b>" + fileproc.FullQualifiedNameAndPath() + "<br>");
		}

		public static void Print(LanguageCollection langCol)
		{
			HttpContext.Current.Response.Write("<b>" + langCol.GetType().Name + "</b><br>");
			HttpContext.Current.Response.Write("<b>Is Loaded?: </b>" + langCol.loadedFromFile() + "<br/>");
			langCol.Debug();
		}

		public static void Print()
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			Debug.Print("<b>Query String:</b>");
			Debug.Print(HttpContext.Current.Request.QueryString);
			Debug.Print("<b>Form:</b>");
			Debug.Print(HttpContext.Current.Request.Form);
			Debug.Print("<b>Server Variable:</b>");
			Debug.Print(HttpContext.Current.Request.ServerVariables);
		}

		public static void Print(System.Xml.XmlDocument xmlDoc)
		{
			Debug.Print(xmlDoc.DocumentElement);
		}

		public static void Print(System.Xml.XmlNode xmlNode)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<font color='red'><b>Debug: " + xmlNode.GetType().Name + "</b></font><br>");
			HttpContext.Current.Response.Write("<xmp>" + xmlNode.OuterXml + "</xmp>");
		}

		public static void Print(Exception ex)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug Error: </font></b>" + ex.ToString() + "<br>");
			Debug.Print(ex, 0);
		}
		protected static void Print(Exception ex, int errCount)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			if (ex == null)
			{
				HttpContext.Current.Response.Write("<br><b>End</b><br>");
			}
			HttpContext.Current.Response.Write("<br>Iteracao: " + (++errCount).ToString());
			HttpContext.Current.Response.Write("<b>" + ex.Message + "</b><br>");
			HttpContext.Current.Response.Write(ex.StackTrace + "<br><br>");
			Debug.Print(ex.InnerException, errCount);
		}

		public static void Print(System.Data.DataRow row)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug: </font>DataRow: " + row.ToString() + "</b><br>");
			HttpContext.Current.Response.Write("<table>");
			Debug.Print(row, true);
			HttpContext.Current.Response.Write("</table>");
		}
		
		protected static void Print(System.Data.DataRow row, bool showHeader)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			if (showHeader)
			{
				HttpContext.Current.Response.Write("<tr>");
				foreach (DataColumn col in row.Table.Columns)
				{
					HttpContext.Current.Response.Write("<td><b>" + col.ColumnName + "</b></td>");
				}
				HttpContext.Current.Response.Write("</tr>");
			}
			HttpContext.Current.Response.Write("<tr>");
			foreach (DataColumn col in row.Table.Columns)
			{
				HttpContext.Current.Response.Write("<td>" + row[col].ToString() + "</td>");
			}
			HttpContext.Current.Response.Write("</tr>");
		}
		
		public static void Print(System.Data.DataTable table)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug: </font>DataTable: " + table.ToString() + "</b><br>");
			HttpContext.Current.Response.Write("<table>");
			int i = 0;
			foreach (DataRow row in table.Rows)
			{
				Debug.Print(row, (i == 0));
				if (i++ > 100)
					break;
			}
			HttpContext.Current.Response.Write("</table>");
		}
		
		public static void Print(object o)
		{
			if (!String.IsNullOrEmpty(HttpContext.Current.Request["rawxml"])) return;
			HttpContext.Current.Response.Write("<b><font color='red'>Debug: </font></b>Object: " + o.ToString() + "<br>");
			HttpContext.Current.Response.Write("Propriedades Públicas<ul>");

			Type t = o.GetType();

			PropertyInfo[] pi = t.GetProperties();
			foreach (PropertyInfo prop in pi)
			{
				String s = "";
				if (prop.PropertyType.FullName == "System.String")
				{
					s = prop.GetGetMethod() + "=" + prop.GetValue(o, null);
				}
				HttpContext.Current.Response.Write("<li>" + s + "</li>");
			}

			HttpContext.Current.Response.Write("</ul>");
		}

	}
}