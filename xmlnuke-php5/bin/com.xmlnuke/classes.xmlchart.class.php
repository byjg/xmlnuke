<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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
/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/

/**
 * Enum to define ChartSeriesFormat
 */
class ChartSeriesFormat
{
	Const Line = 'line';
	Const Bar = 'bar';
	Const Area = 'area';
	Const Pie = 'pie';
	Const Dot = 'dot';
	Const Column = 'column';
	Const Mixed = '--mixed--';
}

/**
 * Enum to define ChartSeriesFormat
 */
class SeriesNotesType
{
	Const Tooltip = 'tooltip';
	Const Legend = 'legend';
	Const Note = 'note';
}

/**
 * Enum to define ChartOutput
 */
class ChartOutput
{
	const Flash = 1;
	const Image = 2;
}

/**
 * Struct for Series
 *
 */
class ChartSeries
{
	public $fieldname;
	public $name;
	/**
	 * @var ChartSeriesFormat
	 */
	public $type;
	public $borderColor;
	public $fillColor;
	/**
	 * @var SeriesNotes
	 */
	public $seriesNotes;
}

/**
 * Struct for Series
 *
 */
class SeriesNotes
{
	public $fieldname;
	/**
	 * @var SeriesNotesType
	 */
	public $type;
	
	/**
	 * 
	 * @param string $fieldname
	 * @param $type
	 */
	public function __construct($fieldname, $type)
	{
		$this->fieldname = $fieldname;
		$this->type = $type;	
	}
}


/**
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlChart extends XmlnukeDocumentObject
{
	const MAXCHARTSERIESNAME = 30;
	
	/**
	*@var string
	*/
	protected $_title;
	/**
	*@var int
	*/
	protected $_chartoutput;
	/**
	 * DataSource for Series
	 *
	 * @var IIterator
	 */
	protected $_iterator;
	/**
	 * @var Context
	 */
	protected $_context;
	
	protected $_defaultChartType;
	protected $_areaBorderColor = "#000000";
	protected $_areaFillColor = "#dddd00";
	protected $_series = array();
	protected $_axisMinValue = 0;
	protected $_axisMaxValue = 0;
	protected $_axisStep = 0;
	protected $_axisAutomatic = true;
	
	protected $_height = 300;
	protected $_width = 500;
	
	/**
	 * Constructor
	 *
	 * @param Context $context
	 * @param string $title
	 * @param IIterator $iterator
	 * @param ChartOutput $chartoutput
	 * @param ChartSeriesFormat $defaultChartType
	 * @return XmlChart
	 */
	public function XmlChart($context, $title, $iterator, $chartoutput, $defaultChartType)
	{
		parent::XmlnukeDocumentObject();
		$this->_context = $context;
		$this->_title = $title;
		$this->_iterator = $iterator;
		$this->_chartoutput = $chartoutput;
		$this->_defaultChartType = $defaultChartType;
	}

	public function setTitle($title)
	{
		$this->_title = $title;
	}
	
	public function setAxisLimits($min, $max, $step)
	{
		$this->_axisMinValue = $min;
		$this->_axisMaxValue = $max;
		$this->_axisStep = $step;
		$this->_axisAutomatic = false;
	}
	
	/**
	 * Add a specific Serie to Chart. If fillcolor is null the system uses the default color SCHEMA.
	 *
	 * @param $string $fieldname
	 * @param $string $name
	 * @param $string $bordercolor
	 * @param $string $fillcolor
	 * @param ChartSeriesFormat $type
	 * @param SeriesNotes $seriesNotes
	 */
	public function addSeries($fieldname, $name, $bordercolor, $fillcolor=null, $type = null, $seriesNotes = null)
	{
		$serie = new ChartSeries();
		$serie->borderColor = $bordercolor;
		$serie->fieldname = $fieldname;
		$serie->name = $name;
		$serie->fillColor = $fillcolor;
		
		if ($this->_defaultChartType != ChartSeriesFormat::Mixed)
		{
			$serie->type = $this->_defaultChartType;
		}
		elseif (is_null($type))
		{
			throw new Exception("Type is required when default chart type is not Mixed");
		}
		else
		{
			$serie->type = $type;
		}
		
		if ( ($seriesNotes != null) && !($seriesNotes instanceof SeriesNotes) )
		{
			throw new XMLNukeException("You should pass an SeriesNotesType object");
		}
		else
		{
			$serie->seriesNotes = $seriesNotes;
		}
		
		$this->_series[] = $serie;
	}
		
	public function setAreaColor($borderColor, $fillColor)
	{
		$this->_areaBorderColor = $borderColor;
		$this->_areaFillColor = $fillColor;
	}
	
	public function setLegend($fieldname, $bordercolor, $fillcolor)
	{
		$serie = new ChartSeries();
		$serie->borderColor = $bordercolor;
		$serie->fieldname = $fieldname;
		$serie->fillColor = $fillcolor;
		$serie->type = null;
		
		$this->_series["LEGEND"] = $serie;
	}
	
	public function setFrame($width, $height)
	{
		$this->_width = $width;
		$this->_height = $height;
	}
	
	/**
	*@desc Generate page, processing yours childs.
	*@param DOMNode $current
	*@return void
	*/
	public function generateObject($current)
	{
		if ($this->_context->ContextValue("xcrt") == "")
		{
			$url = new XmlnukeManageUrl(URLTYPE::HTTP, $this->_context->ContextValue("SELFURL"));
			foreach ($this->_context->getPostVariables() as $key=>$value) 
			{
				$url->addParam($key, $value);
			}
			if ($this->_chartoutput == ChartOutput::Image)
			{
				$url->addParam("xcrt", "image");
				$objImg = new XmlnukeImage($url->getUrl(), $this->_title);
				$objImg->generateObject($current);
			}
			elseif ($this->_chartoutput == ChartOutput::Flash)
			{
				$url->addParam("xcrt", "flash");
				$link = urlencode($this->_context->joinUrlBase(str_replace("&amp;", "&", str_replace("&amp;", "&", str_replace("&amp;", "&", $url->getUrl())))));
				
				$flash = new XmlNukeFlash();
				$flash->setMovie($this->_context->joinUrlBase("common/swfchart/charts.swf"));
				$flash->addParam("FlashVars", "library_path=" . $this->_context->joinUrlBase("common/swfchart/charts_library&xml_source=" . $link));
				$flash->setHeight($this->_height);
				$flash->setWidth($this->_width);
				$flash->generateObject($current);
			}
		}
		else 
		{
			$SEPARATOR = "|#|";
			
			ob_clean();
			$dataseries = array();
			$vertical = true;
			while ($this->_iterator->hasNext())
			{
				$value = new ChartSeries();
				$sr = $this->_iterator->moveNext();
				foreach ($this->_series as $key=>$value) 
				{
					$content = ConvertFromUTF8::RemoveAccent($sr->getField($value->fieldname));
					if (strcmp($key,"LEGEND")==0)
					{
						if (strlen($content) > self::MAXCHARTSERIESNAME)
						{
							$content = substr($content, 0, self::MAXCHARTSERIESNAME);
						}
					}

					if ($value->seriesNotes != null)
					{
						$content .= $SEPARATOR . $value->seriesNotes->type;
						$content .= $SEPARATOR . ConvertFromUTF8::RemoveAccent($sr->getField($value->seriesNotes->fieldname));
					}
					else
					{
						$content .= $SEPARATOR . $SEPARATOR;
					}
					
					$dataseries[$key][] = $content;
				}
			}
				
			if ($this->_context->ContextValue("xcrt") == "image")
			{
				require_once(PHPXMLNUKEDIR . "bin/modules/panachart/panachart.php");
	
			    // AREA
			    $ochart = new chart($this->_width, $this->_height, 7, '#eeeeee');
			    $ochart->setTitle($this->_title,"#000000",2);
			    $ochart->setPlotArea(SOLID,"#000000", '#ddddee');
			    $ochart->setLegend(SOLID, "#444444", "#ffffff", 1, '');
			    $ochart->setXAxis('#000000', SOLID, 1, "", '%s');
			    $ochart->setYAxis('#000000', SOLID, 1, "", '%d');
				foreach ($dataseries as $key=>$mixedValue) 
				{
					for ($i=0;$i<sizeof($mixedValue);$i++)
					{
						$value = explode($SEPARATOR, $mixedValue[$i]);
						$mixedValue[$i] = $value[0];
					}

					if (strcmp($key,"LEGEND")==0)
					{
				    	$ochart->setLabels($mixedValue, '#000000', 1, ($vertical? VERTICAL : HORIZONTAL));
					}
					else 
					{
						$type = $this->_series[$key]->type;
						if ($type == ChartSeriesFormat::Column)
						{
							$type = ChartSeriesFormat::Bar;
						}
					    $ochart->addSeries($mixedValue, $type, $this->_series[$key]->name, SOLID, $this->_series[$key]->borderColor, $this->_series[$key]->fillColor);
					}
				}
			    $ochart->setGrid("#bbbbbb", DOTTED, "#bbbbbb", DOTTED);
			    $ochart->plot("");
			    
			    // Chart Types
			    // area, line, bar, impuls, spline, step, dot
				Header("Content-Type: image/png");
				exit();
			}
			elseif ($this->_context->ContextValue("xcrt") == "flash")
			{
				$doc = XmlUtil::CreateXmlDocumentFromStr("<chart/>", false);
				$root = $doc->documentElement;
				
				$chartLabel = XmlUtil::CreateChild($root, "chart_label");
				XmlUtil::AddAttribute($chartLabel, "position", "outsize");
				XmlUtil::AddAttribute($chartLabel, "size", "12");
				XmlUtil::AddAttribute($chartLabel, "color", "FF4400");
				XmlUtil::AddAttribute($chartLabel, "alpha", "100");

				$chartAxis = XmlUtil::CreateChild($root, "chart_guide");
				XmlUtil::AddAttribute($chartAxis, "horizontal", "true");
				XmlUtil::AddAttribute($chartAxis, "vertical", "true");

				$chartAxis = XmlUtil::CreateChild($root, "axis_ticks");
				XmlUtil::AddAttribute($chartAxis, "value_ticks", "true");
				XmlUtil::AddAttribute($chartAxis, "category_ticks", "true");
				XmlUtil::AddAttribute($chartAxis, "position", "centered");
				
				$chartAxis = XmlUtil::CreateChild($root, "axis_value");
				if (!$this->_axisAutomatic)
				{
					XmlUtil::AddAttribute($chartAxis, "min", $this->_axisMinValue);
					XmlUtil::AddAttribute($chartAxis, "max", $this->_axisMaxValue);
					XmlUtil::AddAttribute($chartAxis, "steps", $this->_axisStep);
				}
				XmlUtil::AddAttribute($chartAxis, "size", "12");
				
				$chartLegend = XmlUtil::CreateChild($root, "legend");
				XmlUtil::AddAttribute($chartLegend, "size", "12");
				
				// Create Basic Chart Nodes
				$chart_type = XmlUtil::CreateChild($root, "chart_type", (($this->_defaultChartType != ChartSeriesFormat::Mixed) ? $this->_defaultChartType : ""));
				$chart_data = XmlUtil::CreateChild($root, "chart_data");
				$series_color = XmlUtil::CreateChild($root, "series_color");
				$axis_category = XmlUtil::CreateChild($root, "axis_category");
				XmlUtil::AddAttribute($axis_category, "size", "11");
				
				// Create Top Row
				$row = XmlUtil::CreateChild($chart_data, "row");
				XmlUtil::CreateChild($row, "null");
				foreach ($dataseries["LEGEND"] as $mixedValue) 
				{
					$value = explode($SEPARATOR, $mixedValue);
					XmlUtil::CreateChild($row, "string", $value[0]);
				}
				
				// Create Data Row
				foreach ($dataseries as $key=>$data) 
				{
					if (strcmp($key,"LEGEND")!=0)
					{
						// Add chart series NAME and VALUES
						$row = XmlUtil::CreateChild($chart_data, "row");
						XmlUtil::CreateChild($row, "string", $this->_series[$key]->name);
						foreach ($data as $mixedValue) 
						{
							$value = explode($SEPARATOR, $mixedValue);
							$number = XmlUtil::CreateChild($row, "number", $value[0]);
							if ($value[1] == SeriesNotesType::Legend)
							{
								XmlUtil::AddAttribute($number, "label", $value[2]);
							}
							elseif ($value[1] == SeriesNotesType::Tooltip)
							{
								XmlUtil::AddAttribute($number, "tooltip", $value[2]);
							}
							elseif ($value[1] == SeriesNotesType::Note)
							{
								XmlUtil::AddAttribute($number, "note", $value[2]);
							}
						}

						// Add Series type ONLY if DEFAULT Chart Type is MIXED
						if ($this->_defaultChartType == ChartSeriesFormat::Mixed)
						{
							XmlUtil::CreateChild($chart_type, "string", $this->_series[$key]->type);
						}
						
						// Add color if exists
						if (!is_null($this->_series[$key]->fillColor))
						{
							$fillColor = $this->_series[$key]->fillColor;
							if ($fillColor[0]=="#")
							{
								$fillColor = substr($fillColor, 1);
							}
							XmlUtil::CreateChild($series_color, "color", $fillColor);
						}
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
				
				//echo "<xmp>" . $doc->saveXML() . "</xmp>";
				echo $doc->saveXML();
				Header("Content-Type: text/xml");
				exit;
			}
		}
	}

}

?>
