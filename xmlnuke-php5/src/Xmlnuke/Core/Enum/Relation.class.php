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

namespace Xmlnuke\Core\Enum;

/**
 * Constants to represent relational operators.
 *
 * Use this in AddRelation method.
 * @package xmlnuke
 */
class Relation
{
	/**
	 * "Equal" relational operator
	 */
	const Equal = 0;

	/**
	 * "Less than" relational operator
	 */
	const LessThan = 1;

	/**
	 * "Greater than" relational operator
	 */
	const GreaterThan = 2;

	/**
	 * "Less or Equal Than" relational operator
	 */
	const LessOrEqualThan = 3;
	/**
	 * "Greater or equal than" relational operator
	 */
	const GreaterOrEqualThan = 4;
	/**
	 * "Not equal" relational operator
	 */
	const NotEqual = 5;
	/**
	 * "Starts with" unary comparator
	 */
	const StartsWith = 6;
	/**
	 * "Contains" unary comparator
	 */
	const Contains = 7;
}
