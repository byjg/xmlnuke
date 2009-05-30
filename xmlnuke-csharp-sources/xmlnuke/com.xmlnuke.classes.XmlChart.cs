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

using com.xmlnuke.engine;
using com.xmlnuke.anydataset;

namespace com.xmlnuke.classes
{

	public class ChartSeriesFormat
	{
		public const string Line = "line";
		public const string Bar = "bar";
		public const string Area = "area";
		public const string Pie = "pie";
		public const string Dor = "dot";
		public const string Column = "column";
		public const string Mixed = "--mixed--";
	}

	public enum ChartOutput
	{
		Flash,
		Image
	}

	public struct ChartSeries
	{
		public string fieldname;
		public string name;
		public string type;
		public string borderColor;
		public string fillColor;
	}

	public class XmlChart : XmlnukeDocumentObject
	{
		const int MAXCHARTSERIESNAME = 15;

		protected string _title;
		protected ChartOutput _chartoutput;
		protected IIterator _iterator;
		protected Context _context;

		protected string _defaultChartType;
		protected string _areaBorderColor = "#000000";
		protected string _areaFillColor = "#dddd00";
		protected ArrayList _series = new ArrayList();

		protected int _height = 300;
		protected int _width = 500;

		public XmlChart(Context context, string title, IIterator iterator, ChartOutput chartoutput, string defaultChartType)
		{
			this._context = context;
			this._title = title;
			this._iterator = iterator;
			this._chartoutput = chartoutput;
			this._defaultChartType = defaultChartType;

			this._series.Add(null);
		}

		public void setTitle(string title)
		{
			this._title = title;
		}

		public void addSeries(string fieldname, string name, string bordercolor)
		{
			this.addSeries(fieldname, name, bordercolor, null, null);
		}

		public void addSeries(string fieldname, string name, string bordercolor, string fillcolor, string type)
		{
			ChartSeries serie = new ChartSeries();
			serie.borderColor = bordercolor;
			serie.fieldname = fieldname;
			serie.name = name;
			serie.fillColor = fillcolor;

			if (this._defaultChartType != ChartSeriesFormat.Mixed)
			{
				serie.type = this._defaultChartType;
			}
			else if (type == null)
			{
				throw new Exception("Type is required when default chart type is not Mixed");
			}
			else
			{
				serie.type = type;
			}

			this._series.Add(serie);
		}

		public void setAreaColor(string borderColor, string fillColor)
		{
			this._areaBorderColor = borderColor;
			this._areaFillColor = fillColor;
		}

		public void setLegend(string fieldname, string bordercolor, string fillcolor)
		{
			ChartSeries serie = new ChartSeries();
			serie.borderColor = bordercolor;
			serie.fieldname = fieldname;
			serie.fillColor = fillcolor;
			serie.type = null;

			// First Serie is ALWAYS the Legend!!
			this._series[0] = serie;
		}

		public void setFrame(int width, int height)
		{
			this._width = width;
			this._height = height;
		}

		override public void generateObject(XmlNode current)
		{
			if (this._context.ContextValue("xcrt") == "")
			{
				XmlnukeManageUrl url = new XmlnukeManageUrl(URLTYPE.HTTP, this._context.ContextValue("SELFURL"));
				foreach (string key in this._context.getPostVariables())
				{
					url.addParam(key, this._context.ContextValue(key));
				}
				if (this._chartoutput == ChartOutput.Image)
				{
					url.addParam("xcrt", "image");
					XmlnukeImage objImg = new XmlnukeImage(url.getUrl(), this._title);
					objImg.generateObject(current);
				}
				else if (this._chartoutput == ChartOutput.Flash)
				{
					url.addParam("xcrt", "flash");
					string link = System.Web.HttpUtility.UrlEncode(this._context.joinUrlBase(url.getUrl()).Replace("&amp;", "&"));

					XmlnukeFlash flash = new XmlnukeFlash();
					flash.setMovie(this._context.joinUrlBase("common/swfchart/charts.swf"));
					flash.addParam("FlashVars", "library_path=" + this._context.joinUrlBase("common/swfchart/charts_library&xml_source=" + link));
					flash.setHeight(this._height);
					flash.setWidth(this._width);
					flash.generateObject(current);
				}
			}
			else
			{
				System.Web.HttpContext.Current.Response.Clear();

				ArrayList[] dataseries = new ArrayList[this._series.Count];
				//bool vertical = true;
				while (this._iterator.hasNext())
				{
					SingleRow sr = this._iterator.moveNext();
					for (int key = 0; key < this._series.Count; key++)
					{
						ChartSeries value = (ChartSeries)this._series[key];
						string content = sr.getField(value.fieldname);
						if (key != 0) // != LEGEND
						{
							if (content.Length > MAXCHARTSERIESNAME)
							{
								content = content.Substring(0, MAXCHARTSERIESNAME);
							}
						}
						if (dataseries[key] == null)
						{
							dataseries[key] = new ArrayList();
						}
						dataseries[key].Add(content);
					}
				}

				if (this._context.ContextValue("xcrt") == "image")
				{
					// Uses ZedGraph
				}
				else if (this._context.ContextValue("xcrt") == "flash")
				{
					XmlDocument doc = util.XmlUtil.CreateXmlDocumentFromStr("<chart/>");
					XmlNode root = doc.DocumentElement;

					// Create Basic Chart Nodes
					XmlNode chart_type = util.XmlUtil.CreateChild(root, "chart_type", ((this._defaultChartType != ChartSeriesFormat.Mixed) ? this._defaultChartType : ""));
					XmlNode chart_data = util.XmlUtil.CreateChild(root, "chart_data");
					XmlNode series_color = util.XmlUtil.CreateChild(root, "series_color");
					XmlNode axis_category = util.XmlUtil.CreateChild(root, "axis_category");
					util.XmlUtil.AddAttribute(axis_category, "size", "11");

					// Create Top Row
					XmlNode row = util.XmlUtil.CreateChild(chart_data, "row");
					util.XmlUtil.CreateChild(row, "null");

					// Extract Legend!
					foreach (object o in dataseries[0])
					{
						util.XmlUtil.CreateChild(row, "string", o.ToString());
					}

					// Create Data Row
					for (int key = 1; key < dataseries.Length; key++)
					{
						ChartSeries curSerie = (ChartSeries)this._series[key];
						// Add chart series NAME and VALUES
						row = util.XmlUtil.CreateChild(chart_data, "row");
						util.XmlUtil.CreateChild(row, "string", curSerie.name);
						foreach (object data in dataseries[key])
						{
							util.XmlUtil.CreateChild(row, "number", data.ToString());
						}

						// Add Series type ONLY if DEFAULT Chart Type is MIXED
						if (this._defaultChartType == ChartSeriesFormat.Mixed)
						{
							util.XmlUtil.CreateChild(chart_type, "string", curSerie.type);
						}

						// Add color if exists
						if (!String.IsNullOrEmpty(curSerie.fillColor))
						{
							string fillColor = curSerie.fillColor;
							if (fillColor[0] == '#')
							{
								fillColor = fillColor.Substring(1);
							}
							util.XmlUtil.CreateChild(series_color, "color", fillColor);
						}
					}

					/*
					* line
					* column (default)
					* stacked column
					* floating column
					* 3d column
					* stacked 3d column
					* parallel 3d column
					* pie
					* 3d pie
					* bar
					* stacked bar
					* floating bar
					* area
					* stacked area
					* candlestick
					* scatter
					* polar 
			
				
					In mixed charts, only area, column, and line chart types are valid. The area portion of the chart is always displayed in the back layer. The column portion of the chart is displayed in the middle layer. The line portion of the chart is displayed in front layer. Example:
					*/

					//echo "<xmp>" . doc.saveXML() . "</xmp>";
					System.Web.HttpContext.Current.Response.ContentType = "text/xml";
					System.Web.HttpContext.Current.Response.Write(doc.OuterXml);
					System.Web.HttpContext.Current.Response.End();
				}
			}
		}

	}

}