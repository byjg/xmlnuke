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
*Enum to abstract relational operators. Used on AddRelation method.
*/
class Relation
{
	/**
	*@desc = operator
	*/
	const Equal = 0;
	
	/**
	*@desc < operator
	*/
	const LessThan = 1;
	
	/**
	*@desc > operator
	*/
	const GreaterThan = 2;
	
	/**
	*@desc <= operator
	*/
	const LessOrEqualThan = 3;
	/**
	*@desc >= operator
	*/
	const GreaterOrEqualThan = 4;
	/**
	*@desc != operator
	*/
	const NotEqual = 5;
	/**
	*@desc != operator
	*/
	const StartsWith = 6;
	/**
	*@desc != operator
	*/
	const Contains = 7;
}


/**
*IteratorFilter class abstract XPATH commands to filter an AnyDataSet XML. Used on getIterator method.
*/
class IteratorFilter
{

	/**
	*@var string
	*/
	private $_xpathFilter;

	/**
	*@var string
	*/
	private $_sqlFilter;

	/**
	*@var string
	*/
	private $_sqlParam;

	/**
	*@desc IteratorFilter Constructor
	*/
	public function IteratorFilter()
	{
		$this->_xpathFilter = "";
		$this->_sqlFilter = "";
		$this->_sqlParam = array();
	}

	/**
	*@param 
	*@return string - XPath String
	*@desc Get the XPATH string
	*/
	public function getXPath()
	{
		if ($this->_xpathFilter == "")
		{
			return "/anydataset/row";
		}
		else
		{
			return "/anydataset/row[".$this->_xpathFilter."]";
		}
	}

	/**
	 * Get the SQL string
	 *
	 * @param string $tableName
	 * @param array &$params
	 * @param string $returnFields
	 * @param string $paramSubstName If ended with "_" the program subst by argname; 
	 * @return string
	 */
	public function getSql($tableName, &$params, $returnFields = "*")
	{
		$params = array();
		
		$sql = "select $returnFields from " . $tableName;
		if ($this->_sqlFilter != "")
		{
			$sql .= " where " . $this->_sqlFilter . " ";
			$params = $this->_sqlParam;
		}
				
		return $sql;
	}
	
	/**
	*@param string $name - Field name
	*@param Relation $relation - Relation enum
	*@param string $value - Field string value
	*@return string - Xpath String
	*@desc Private method to get a Xpath string to a single string comparison
	*/
	private function getStrXpathRelation($name, $relation, $value)
	{
		$str = is_numeric($value)?"":"'";
		$field = "field[@name='".$name."'] ";
		$value = " $str$value$str ";
		
		$result = "";
		switch ($relation)
		{
			case Relation::Equal:
			{
				$result = $field . "=" . $value;
				break;
			}
			case Relation::GreaterThan:
			{
				$result = $field . ">" . $value;
				break;
			}
			case Relation::LessThan:
			{
				$result = $field . "<" . $value;
				break;
			}
			case Relation::GreaterOrEqualThan:
			{
				$result = $field . ">=" . $value;
				break;
			}
			case Relation::LessOrEqualThan:
			{
				$result = $field . "<=" . $value;
				break;
			}
			case Relation::NotEqual:
			{
				$result = $field . "!=" . $value;
				break;
			}
			case Relation::StartsWith:
			{
				$result = " starts-with($field, $value) ";
				break;
			}
			case Relation::Contains:
			{
				$result = " contains($field, $value) ";
				break;
			}
		}
		return $result;
	}

	private function getStrSqlRelation($name, $relation, $value)
	{
		//$str = is_numeric($value)?"":"'";
		$value = trim($value);
		$paramName = $name;
		$i = 0;
		while (array_key_exists($paramName, $this->_sqlParam))
		{
			$paramName = $name . ($i++);
		}

		$this->_sqlParam[$paramName] = $value;
		
		$result = "";
		$field = " $name ";
		$valueparam = " [[" . $paramName . "]] ";
		switch ($relation)
		{
			case Relation::Equal:
			{
				$result = $field . "=" . $valueparam;
				break;
			}
			case Relation::GreaterThan:
			{
				$result = $field . ">" . $valueparam;
				break;
			}
			case Relation::LessThan:
			{
				$result = $field . "<" . $valueparam;
				break;
			}
			case Relation::GreaterOrEqualThan:
			{
				$result = $field . ">=" . $valueparam;
				break;
			}
			case Relation::LessOrEqualThan:
			{
				$result = $field . "<=" . $valueparam;
				break;
			}
			case Relation::NotEqual:
			{
				$result = $field . "!=" . $valueparam;
				break;
			}
			case Relation::StartsWith:
			{
				$this->_sqlParam[$paramName] = $value . "%";
				$result = $field . " like " . $valueparam;
				break;
			}
			case Relation::Contains:
			{
				$this->_sqlParam[$paramName] = "%" . $value . "%";
				$result = $field . " like " . $valueparam;
				break;
			}
		}
		
		return $result;
	}
	
	/**
	*@param string $name - Field name
	*@param Relation $relation - Relation enum
	*@param string $value - Field string value
	*@return void
	*@desc Add a single string comparison to filter.
	*/
	public function addRelation($name, $relation, $value)
	{
		if (($this->_xpathFilter != "") && (substr($this->_xpathFilter, strlen($this->_xpathFilter)-2, 2) != "( ") )
		{
			$this->_xpathFilter = $this->_xpathFilter." and ";
			$this->_sqlFilter = $this->_sqlFilter." and ";
		}
		$this->_xpathFilter = $this->_xpathFilter .$this->getStrXpathRelation($name, $relation, $value);
		$this->_sqlFilter = $this->_sqlFilter . $this->getStrSqlRelation($name, $relation, $value);
	}

	/**
	*@param string $name - Field name
	*@param Relation $relation - Relation enum
	*@param string $value - Field string value 
	*@return void
	*@desc Add a single string comparison to filter. This comparison use the OR operator.
	*/
	public function addRelationOr($name, $relation, $value)
	{
		if ( ($this->_xpathFilter != "")  && (substr($this->_xpathFilter, strlen($this->_xpathFilter)-2, 2) != "( ") )
		{
			$this->_xpathFilter = $this->_xpathFilter." or ";
			$this->_sqlFilter = $this->_sqlFilter." or ";
		}
		$this->_xpathFilter = $this->_xpathFilter .$this->getStrXpathRelation($name, $relation, $value);
		$this->_sqlFilter = $this->_sqlFilter . $this->getStrSqlRelation($name, $relation, $value);
	}

	/**
	 * Add a "("
	 *
	 */
	public function startGroup()
	{
		if (substr($this->_xpathFilter, strlen($this->_xpathFilter)-2, 2) == ") ")
		{
			$this->_xpathFilter = $this->_xpathFilter." or ";
			$this->_sqlFilter = $this->_sqlFilter." or ";
		}
		elseif ($this->_xpathFilter != "")
		{
			$this->_xpathFilter = $this->_xpathFilter." and ";
			$this->_sqlFilter = $this->_sqlFilter." and ";
		}
		$this->_xpathFilter = $this->_xpathFilter . " ( ";
		$this->_sqlFilter = $this->_sqlFilter . " ( ";
	}
	
	/**
	 * Add a ")"
	 *
	 */
	public function endGroup()
	{
		$this->_xpathFilter = $this->_xpathFilter . " ) ";
		$this->_sqlFilter = $this->_sqlFilter . " ) ";
	}
}
?>
