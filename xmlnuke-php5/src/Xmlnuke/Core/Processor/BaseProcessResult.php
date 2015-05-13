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

namespace Xmlnuke\Core\Processor;

use Xmlnuke\Core\Engine\Context;

class BaseProcessResult implements IProcessResult
{
	/**
	 * This method is used only in the Wrappers
	 * So, it can echo string directly
	 *
	 * @param type $buffer
	 */
	public function SearchAndReplace($buffer)
	{
		$context = Context::getInstance();

		$posi = 0;
		$i = strpos($buffer, "<param-", $posi);
		while ($i !== false)
		{
			echo substr($buffer, $posi, $i-$posi);
			$if = strpos($buffer, "</param-", $i);

			$tamparam = $if-$i-8;
			$var = substr($buffer, $i+7, $tamparam);

			echo $context->get($var);

			$posi = $if + $tamparam + 9;
			$i = strpos($buffer, "<param-", $posi);
		}

		echo substr($buffer, $posi);
	}
}


?>
