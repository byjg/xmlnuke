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
*@package xmlnuke
*@subpackage xmlnukeobject
*/

namespace Xmlnuke\Core\Classes;

/**
 * @Xmlnuke:NodeName ChartObject
 */
class ChartObject
{

	protected $_Id;

	protected $_Title;

	protected $_TitleVertical;

	protected $_TitleHorizontal;

	protected $_Width;

	protected $_Height;

	protected $_Is3d;

	protected $_MinValueX;

	protected $_MaxValueX;

	protected $_MinValueY;

	protected $_MaxValueY;

	protected $_ChartType;

	/**
	 *
	 * @var \Xmlnuke\Core\AnyDataset\AnyDataSet
	 */
	protected $_Serie;

	public function __construct($title = "")
	{
		$this->_Id = 'chart' . rand(1000,9999) . rand(1000, 9999);
		$this->_Width = 800;
		$this->_Height = 600;
		$this->_Title = $title;
		$this->_ChartType = \Xmlnuke\Core\Enum\ChartType::Column;
		$this->_Serie = new \Xmlnuke\Core\AnyDataset\AnyDataSet();
	}

	public function getId()
	{
		return $this->_Id;
	}

	public function setId($Id)
	{
		$this->_Id = $Id;
	}

		public function getTitle()
	{
		return $this->_Title;
	}

	public function setTitle($Title)
	{
		$this->_Title = $Title;
	}

	public function getChartType()
	{
		return $this->_ChartType;
	}

	public function setChartType($ChartType)
	{
		$this->_ChartType = $ChartType;
	}

	public function getTitleVertical()
	{
		return $this->_TitleVertical;
	}

	public function setTitleVertical($TitleVertical)
	{
		$this->_TitleVertical = $TitleVertical;
	}

	public function getTitleHorizontal()
	{
		return $this->_TitleHorizontal;
	}

	public function setTitleHorizontal($TitleHorizontal)
	{
		$this->_TitleHorizontal = $TitleHorizontal;
	}

	public function getWidth()
	{
		return $this->_Width;
	}

	public function setWidth($Width)
	{
		$this->_Width = $Width;
	}

	public function getHeight()
	{
		return $this->_Height;
	}

	public function setHeight($Height)
	{
		$this->_Height = $Height;
	}

	public function getIs3d()
	{
		return $this->_Is3d;
	}

	public function setIs3d($Is3d)
	{
		$this->_Is3d = $Is3d;
	}

	public function getMinValueX()
	{
		return $this->_MinValueX;
	}

	public function setMinValueX($MinValueX)
	{
		$this->_MinValueX = $MinValueX;
	}

	public function getMaxValueX()
	{
		return $this->_MaxValueX;
	}

	public function setMaxValueX($MaxValueX)
	{
		$this->_MaxValueX = $MaxValueX;
	}

	public function getMinValueY()
	{
		return $this->_MinValueY;
	}

	public function setMinValueY($MinValueY)
	{
		$this->_MinValueY = $MinValueY;
	}

	public function getMaxValueY()
	{
		return $this->_MaxValueY;
	}

	public function setMaxValueY($MaxValueY)
	{
		$this->_MaxValueY = $MaxValueY;
	}

	/**
	 *
	 * @param string $name
	 * @param \Xmlnuke\Core\Enum\ChartColumnType $type
	 * @param string $data
	 */
	public function addSerie($name, $type, $data)
	{
		$iter = $this->_Serie->getIterator();
		if ($iter->Count() == 0)
		{
			$this->_Serie->addField('data_0', "'$name'");

			foreach ($data as $item)
			{
				if ($type == \Xmlnuke\Core\Enum\ChartColumnType::String)
				{
					$item = "'$item'";
				}
				else if ($type == \Xmlnuke\Core\Enum\ChartColumnType::Number)
				{
					$item = intval($item);
				}
				else
				{
					throw new \UnexpectedValueException('Unexpected Chat Column Type - ' . $type);
				}

				$this->_Serie->appendRow();
				$this->_Serie->addField('data_0', $item);
			}
		}
		else
		{
			$serieCount = null;
			foreach($iter as $row)
			{
				if ($serieCount == null)
				{
					$serieCount = count($row->getRawFormat());
					$row->addField("data_$serieCount", "'$name'");
				}
				else if (count($data) > 0)
				{
					if ($type == \Xmlnuke\Core\Enum\ChartColumnType::String)
					{
						$item = "'" . array_shift($data) . "'";
					}
					else if ($type == \Xmlnuke\Core\Enum\ChartColumnType::Number)
					{
						$item = intval(array_shift($data));
					}
					else
					{
						throw new \UnexpectedValueException('Unexpected Chat Column Type - ' . $type);
					}

					$row->addField("data_$serieCount", $item);
				}
			}
		}
	}

	/**
	 *
	 * @param type $iter
	 * @param array $info { column: "", type: "ChartColumnType", name: "" }
	 */
	public function addSeriesIterator($iter, $info, $totalPieChart = false)
	{
		// Extract Data
		$data = array();
		foreach ($iter as $row)
		{
			for($i=0;$i<count($info);$i++)
			{
				$data[$info[$i]['column']][] = $row->getField($info[$i]['column']);
			}
		}

		// Add Series with the extract data
		if (count($data) > 0)
		{
			if (!$totalPieChart)
			{
				foreach ($info as $infoCol)
				{
					$this->addSerie($infoCol['name'], $infoCol['type'], $data[$infoCol['column']]);
				}
			}
			else
			{
				$column = array();
				$sum = array( );
				for($i=0;$i<count($info);$i++)
				{
					if ($info[$i]['type'] == \Xmlnuke\Core\Enum\ChartColumnType::Number)
					{
						$column[] = $info[$i]['name'];
						$sum[] = array_sum($data[$info[$i]['column']]);
					}
				}
				$this->addSerie('Labels', \Xmlnuke\Core\Enum\ChartColumnType::String, $column);
				$this->addSerie('Sum', \Xmlnuke\Core\Enum\ChartColumnType::Number, $sum);
				$this->setChartType(\Xmlnuke\Core\Enum\ChartType::Pie);
			}
		}
	}

	public function getSerie()
	{
		return $this->_Serie->getIterator();
	}




}
?>
