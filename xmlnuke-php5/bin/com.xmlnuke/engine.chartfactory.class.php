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

class ChartFactory
{
	/**
	* Doesn't need constructor because all methods are statics.
	*/
	public function ModuleFactory()
	{}

	/**
		* Locate and create custom module if exists. Otherwise throw exception.
		*
		* @param string $modulename
		* @param Context $context
		* @param object $o
		* @return IModule
		*/
	public static function getChart($chartname, $context)
	{
		// 1 <xmlnukedir>/lib/<subdir>/<modulename>.class.php

		$chartname = strtolower($chartname);

		$path = "lib".FileUtil::Slash();
		$path .= str_replace('.', FileUtil::Slash(), $chartname);
		
		$chart = basename($path);
		$path = dirname($path);

		if (!ChartFactory::tryLoadChart($path, $chart))
		{
			throw new NotFoundException("Chart \"$chartname\" not found.");
		}

		try
		{
			$class = new ReflectionClass($chart);
			$result = $class->newInstance($context);
			$chartObject = $result->getChartObject();
		}
		catch (Exception $e)
		{
			throw new NotFoundException($e->getMessage());
		}

		return $chartObject;
	}

	/**
	 * Try include module class library to load it
	 *
	 * @param string $pathRoot
	 * @param string $module
	 * @return bool
	 */
	private static function tryLoadChart($pathRoot, $chart)
	{
		$chartToLoad = "$pathRoot" . FileUtil::Slash() . "$chart.class.php";
		$found = file_exists($chartToLoad);
		if ($found)
		{
			include_once($chartToLoad);
		}
		return $found;
	}
}
?>