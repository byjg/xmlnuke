<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Wrapper;

use chart;
use Xmlnuke\Core\Classes\BaseSingleton;
use Xmlnuke\Core\Engine\ChartFactory;
use Xmlnuke\Core\Engine\Context;

/**
 * TODO: This class does not working properly. REDO!
 * TODO: Get an alternative for Image Chart from Google because it is deprecated.
 */
class ChartWrapper extends BaseSingleton implements IOutputWrapper
{
	public function Process()
	{

		$context = Context::getInstance();

		/*
		   You must have pass a parameter called CN.
		   Example: chart.php?cn=NAME

		   XmlNuke will Try load the class called "NAME" and execute the Method:
		   getChartObject()
		*/
		try
		{
			$cn = $context->get("cn");
			if ($cn!="")
			{
				$cn = '\\' . str_replace('.', '\\', $context->get("cn"));
				$chartObj = new $cn();
				$chart = $chartObj->getChartObject();
				//$chart = new \Xmlnuke\Core\Classes\ChartObject();

				$params = array();

				if ($chart->getChartType() == \Xmlnuke\Core\Enum\ChartType::Area)
					$params['cht'] = '1c';
				else if ($chart->getChartType() == \Xmlnuke\Core\Enum\ChartType::Line)
					$params['cht'] = '1c';
				else if ($chart->getChartType() == \Xmlnuke\Core\Enum\ChartType::Pie && !$chart->getIs3d())
					$params['cht'] = 'p';
				else if ($chart->getChartType() == \Xmlnuke\Core\Enum\ChartType::Pie && $chart->getIs3d())
					$params['cht'] = 'p3';
				else if ($chart->getChartType() == \Xmlnuke\Core\Enum\ChartType::Donut)
					$params['cht'] = 'p';
				else if ($chart->getChartType() == \Xmlnuke\Core\Enum\ChartType::Bar)
					$params['cht'] = 'bhg';
				else if ($chart->getChartType() == \Xmlnuke\Core\Enum\ChartType::Column)
					$params['cht'] = 'bvg';
				else
					$params['cht'] = 'bvg';

				/*
				   The formula below is necessary because Google have a maximum limit of 480.000.
				   This is basic rule of three

				   R = W/H --> W = R*H

				   Wo*Ho => (Wo*Ho)/10000
				   R*H² => 30
				    .
				   . .
				   H² = (30*Wo*Ho) / (R*((Wo*Ho)/10000))
				 */
				$size = ($chart->getWidth() * $chart->getHeight()) / 10000;
				if ($size > 30)
				{
					$ratio = $chart->getWidth() / $chart->getHeight();
					$chart->setHeight( intval(sqrt( (30 * $chart->getWidth() * $chart->getHeight()) / ($ratio * $size) )) );
					$chart->setWidth( intval($ratio * $chart->getHeight()) );
				}

				$params['chs'] = $chart->getWidth() . 'x' . $chart->getHeight();

				$iter = $chart->getSerie();

				$params['chd'] = 't:';
				$data = array();
				foreach ($iter as $serie)
				{
					if (!isset($params['chdl']))
					{
						$serieData = $serie->getRawFormat();
						unset($serieData['data_0']);
						$params['chdl'] = implode('|', $serieData);
					}
					else
					{
						$serieData = $serie->getRawFormat();
						for($i=1;$i<count($serieData);$i++)
						{
							if (!isset($data[$i]))
								$data[$i] = array();

							$data[$i][] = $serieData["data_$i"];
						}
					}
				}
				foreach ($data as $itemData)
				{
					$params['chd'] .= implode(',', $itemData) . "|";
				}
				$params['chd'] = substr($params['chd'], 0, strlen($params['chd'])-1);

				$colors = array('#FFF8A3', '#A9CC8F', '#B2C8D9', '#BEA37A', '#F3AA79', '#B5B5A9', '#E6A5A4',
								'#F8D753', '#5C9746', '#3E75A7', '#7A653E', '#E1662A', '#74796F', '#C4384F',
								'#F0B400', '#1E6C0B', '#00488C', '#332600', '#D84000', '#434C43', '#B30023',
								'#FAE16B', '#82B16A', '#779DBF', '#907A52', '#EB8953', '#8A8D82', '#D6707B',
								'#F3C01C', '#3D8128', '#205F9A', '#63522B', '#DC5313', '#5D645A', '#BC1C39');
				$params['chco'] = str_replace('#', '', implode('|', $colors));

				$strParams = "";
				foreach($params as $key=>$value)
				{
					$strParams .= $key . "=" . str_replace("'", "", $value) . "&";
				}
				$strParams .= 'chds=a';

				print_r($strParams);
				die();


				Header("Content-Type: image/png");
				$imageData = file_get_contents('http://chart.apis.google.com/chart?cht=p3&chd=t:39,47,8,4,2&chs=380x180&chl=IE|Firefox|Chrome|Safari|Opera');
				echo $imageData;
			}

		}
		catch (Exception $ex)
		{
			echo "Chart Error: " . $ex->getMessage();
		}

	}

}
