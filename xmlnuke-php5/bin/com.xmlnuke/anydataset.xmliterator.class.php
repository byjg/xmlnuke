<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes
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

class XmlIterator extends GenericIterator
{
	/**
	 * Enter description here...
	 *
	 * @var Context
	 */
	private $_context = null;

	/**
	 * Enter description here...
	 *
	 * @var DOMNodeList
	 */
	private $_nodeList = null;

	/**
	 * String
	 *
	 * @var string
	 */
	private $_rowNode = null;
	/**
	 * Enter description here...
	 *
	 * @var string[]
	 */
	private $_colNodes = null;

	/**
	 * Enter description here...
	 *
	 * @var int
	 */
	private $_current = 0;

	public function XmlIterator($context, $nodeList, $colNodes)
	{
		if (!($nodeList instanceof DOMNodeList))
		{
			throw new Exception("XmlIterator: Wrong node list type");
		}
		if (!is_array($colNodes))
		{
			throw new Exception("XmlIterator: Wrong column node type");
		}

		$this->_context = $context;
		$this->_nodeList = $nodeList;
		$this->_colNodes = $colNodes;

		$this->_current = 0;
	}

	public function Count()
	{
		return $this->_nodeList->length;
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
		if ($this->_current < $this->Count())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		if (!$this->hasNext())
		{
			throw new Exception("No more records. Did you used hasNext() before moveNext()?");
		}

		$node = $this->_nodeList->item($this->_current++);

		$sr = new SingleRow();

		foreach ($this->_colNodes as $key=>$colxpath)
		{
			$nodecol = XmlUtil::selectSingleNode($node, $colxpath);
			if (is_null($nodecol))
			{
				$sr->AddField(strtolower($key), "");
			}
			else
			{
				//Debug::PrintValue($nodecol);
				$sr->AddField(strtolower($key), $nodecol->nodeValue);
			}
		}

		return 	$sr;
	}

 	function key()
 	{
 		return $this->_current;
 	}

}
?>
