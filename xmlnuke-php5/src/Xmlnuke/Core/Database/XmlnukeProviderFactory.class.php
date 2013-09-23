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

/**
 * Class to create and manipulate Several Data Types
 *
 */
namespace Xmlnuke\Core\Database;

class XmlnukeProviderFactory
{
	/**
	 * Each provider have your own model for pass parameter. This method define how each provider name define the parameters
	 *
	 * @param ConnectionManagement $connData
	 * @return string
	 */
	public static function GetParamModel($connData)
	{
		if ($connData->getExtraParam("parammodel") != "")
		{
			return $connData->getExtraParam("parammodel");
		}
		elseif ($connData->getDriver() == "sqlrelay")
		{
			return "?";
		}
		else
		{
			return ":_";
		}
	}

	/**
	 * Transform generic parameters [[PARAM]] in a parameter recognized by the provider name based on current DbParameter array.
	 *
	 * @param ConnectionManagement $connData
	 * @param string $SQL
	 * @param array $param
	 * @return string
	 */
	public static function ParseSQL($connData, $SQL, $params)
	{
		$paramSubstName = XmlnukeProviderFactory::GetParamModel ( $connData );
		foreach ( $params as $key => $value )
		{
			$arg = str_replace ( "_", XmlnukeProviderFactory::KeyAdj ( $key ), $paramSubstName );
			$SQL = str_replace ( "[[" . $key . "]]", $arg, $SQL );
		}

		$SQL = preg_replace("/\[\[[\w\d\-\.]+\]\]/", "null", $SQL);

		return $SQL;
	}

	public static function KeyAdj($key)
	{
		return str_replace ( ".", "_", $key );
	}

}


?>