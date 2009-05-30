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
 * AnyDataSet is a simple way to store data using only XML file.
 * Your structure is hierarquical and each "row" contains "fields" but these structure can vary for each row.
 * Anydataset files have extension ".anydata.xml" and have many classes to put and get data into anydataset xml file.
 * Anydataset class just read and write files. To search elements you need use AnyIterator and IteratorFilter. Each row have a class SingleRow.
 * @see "com.xmlnuke.anydataset.SingleRow"
 * @see "com.xmlnuke.anydataset.AnyIterator"
 * @see "com.xmlnuke.anydataset.IteratorFilter"
 * 
 * <example>
 * <code>
 * &lt;anydataset&gt;
 *		&lt;row&gt;
 *			&lt;field name="fieldname1"&gt;value of fieldname 1&lt;/field&gt;
 *			&lt;field name="fieldname2"&gt;value of fieldname 2&lt;/field&gt;
 *			&lt;field name="fieldname3"&gt;value of fieldname 3&lt;/field&gt;
 *		&lt;/row&gt;
 *		&lt;row&gt;
 *			&lt;field name="fieldname1"&gt;value of fieldname 1&lt;/field&gt;
 *			&lt;field name="fieldname4"&gt;value of fieldname 4&lt;/field&gt;
 *		&lt;/row&gt;
 * &lt;/anydataset&gt;
 * </code>
 */

class AnyDataSet {
	/**
	 *@access private
	 *@var DOMDocument
	 *@desc Internal structure to store anydataset elements
	 */
	private $_anyDataSet;
	/**
	 *@access private
	 *@var SingleRow
	 *@desc Internal structure represent the current SingleRow
	 */
	private $_singleRow;
	/**
	 *@access private
	 *@var DOMNode
	 *@desc XML node represents ANYDATASET node
	 */
	private $_nodeRoot;
	/**
	 *@access private
	 *@var DOMNode
	 *@desc Current node anydataset works
	 */
	private $_currentRow;
	/**
	 *@var string
	 *@desc Path to anydataset file
	 */
	private $_path;
	
	/**
	 *@access public
	 *@return void
	 *@param AnydatasetBaseFilenameProcessor $file
	 *@desc AnyDataSet constructor
	 */
	public function AnyDataSet($file = null) {
		$this->_path = null;
		if ($file == null) {
			$this->CreateNew ();
		} else {
			if (! is_string ( $file )) {
				$this->_path = $file->FullQualifiedNameAndPath ();
			} else {
				$this->_path = $file;
			}
			$this->CreateFrom ( $this->_path );
		}
	}
	
	/**
	 *@access private
	 *@return void
	 *@desc Private method used to create Empty Anydataset
	 */
	private function CreateNew() {
		$this->_anyDataSet = XmlUtil::CreateXmlDocumentFromStr ( "<anydataset/>" );
		//$this->_nodeRoot = XmlUtil::selectSingleNode( $this->_anyDataSet, "anydataset" );
		$this->_nodeRoot = $this->_anyDataSet->getElementsByTagName ( "anydataset" )->item ( 0 );
	}
	
	/**
	 *@access private
	 *@return void
	 *@param string $filepath - Path and Filename to be read
	 *@desc Private method used to read and populate anydataset class from specified file
	 */
	private function CreateFrom($filepath) {
		if (! FileUtil::Exists ( $filepath )) {
			$this->CreateNew ();
		} else {
			$this->_anyDataSet = XmlUtil::CreateXmlDocumentFromFile ( $filepath );
			//$this->_nodeRoot = XmlUtil::selectSingleNode( $this->_anyDataSet, "anydataset" );
			$this->_nodeRoot = $this->_anyDataSet->getElementsByTagName ( "anydataset" )->item ( 0 );
		}
	}
	
	/**
	 *@access public
	 *@return string - XML String
	 *@desc Returns the AnyDataSet XML representative structure.
	 */
	public function XML() {
		return $this->_anyDataSet->saveXML ();
	}
	
	/**
	 *@access public
	 *@return DOMDocument - XmlDocument object
	 *@desc Returns the AnyDataSet XmlDocument representive object
	 */
	public function getDomObject() {
		return $this->_anyDataSet;
	}
	
	/**
	 *@access public
	 *@param AnydatasetBaseFilenameProcessor $file
	 *@return void
	 *@desc Save the AnyDataSet file to disk. All operations running in memory. You need save to disk to persist data.
	 */
	public function Save($file = null) {
		if (! is_null ( $file )) {
			if (! is_string ( $file )) {
				$this->_path = $file->FullQualifiedNameAndPath ();
			} else {
				$this->_path = $file;
			}
		}
		if (is_null ( $this->_path )) {
			throw new DataBaseException ( 1000, "No such file path to save anydataset" );
		}
		XmlUtil::SaveXmlDocument ( $this->_anyDataSet, $this->_path );
	}
	
	/**
	 * @access public
	 * @param SingleRow $sr
	 * @return void
	 * @desc Append one row to AnyDataSet.
	 */
	public function appendRow($sr = null) 
	{
		if ($sr != null)
		{
			if ($sr instanceof SingleRow )
			{
				$this->_currentRow = XmlUtil::CreateChild ( $this->_nodeRoot, "row", "" );
				XmlUtil::AddNodeFromNode($this->_currentRow, $sr->getDomObject());
				$this->_singleRow = new SingleRow ( $this->_currentRow );
			}
			else 
			{
				throw new Exception("You must pass a SingleRow object");
			}
		}
		else 
		{
			$this->_currentRow = XmlUtil::CreateChild ( $this->_nodeRoot, "row", "" );
			$this->_singleRow = new SingleRow ( $this->_currentRow );
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param IIterator $it
	 */
	public function import($it)
	{
		while ($it->hasNext())
		{
			$sr = $it->moveNext();
			$this->appendRow($sr);
		}
	}
	
	/**
	 *@access public
	 *@param int or DOMNode - Row number or node to be added before
	 *@return void
	 *@desc Insert one row before specified position.
	 */
	public function insertRowBefore($row) {
		if ($row instanceof DOMNode) {
			$this->_currentRow = XmlUtil::CreateChildBeforeNode ( "row", "", $row );
			$this->_singleRow = new SingleRow ( $this->_currentRow );
		} elseif ($row > $this->_nodeRoot->childNodes->length - 1) {
			$this->appendRow ();
		} else {
			$this->_currentRow = XmlUtil::CreateChildBefore ( $this->_nodeRoot, "row", "", $row );
			$this->_singleRow = new SingleRow ( $this->_currentRow );
		}
	}
	
	/**
	 *@access public
	 *@param DOMNode $row - Row number (sequential)
	 *@return 
	 *@desc Remove specified row position.
	 */
	public function removeRow($row) {
		$this->_nodeRoot->removeChild ( $row );
	}
	
	/**
	 *@access public
	 *@param string $name - Field name
	 *@param string $value - Field value
	 *@return void
	 *@desc Add a single string field to an existing row
	 */
	public function addField($name, $value) {
		$this->_singleRow->AddField ( $name, $value );
	}
	
	/**
	 *@access public
	 *@param AnyIteratorFilter $itf
	 *@return IIterator
	 *@desc Get an Iterator filtered by an IteratorFilter
	 */
	public function getIterator($itf = null) {
		if ($itf == null) {
			//return new AnyIterator(XmlUtil::selectNodes($this->_nodeRoot, ""));
			return new AnyIterator ( $this->getDomObject ()->getElementsByTagname ( "row" ) );
		} else {
			$xpath = new DOMXPath ( $this->_anyDataSet );
			$xnl = $xpath->query ( $itf->getXPath () );
			return new AnyIterator ( $xnl );
		}
	}
	
	/**
	 *@access public
	 *@param IteratorFilter $itf
	 *@param string $fieldName
	 *@return array
	 *@desc 
	 */
	public function getArray($itf, $fieldName) {
		$it = $this->getIterator ( $itf );
		$result = array ();
		while ( $it->hasNext () ) {
			$sr = $it->moveNext ();
			$result [] = $sr->getField ( $fieldName );
		}
		return $result;
	}
	
	public function Sort($field) {
		$array = array ();
		
		$anydataNode = $this->_nodeRoot;
		
		$row = $anydataNode->childNodes->item ( 0 );
		
		if (! $row) {
			return;
		}
		
		while ( $anydataNode->childNodes->length > 0 ) {
			$array [] = $anydataNode->removeChild ( $anydataNode->firstChild );
		}
		
		for($i = 0; $i < $row->childNodes->length; $i ++) {
			if ($row->childNodes->item ( $i )->getAttribute ( 'name' ) == $field) {
				break;
			}
		}
		
		$array = $this->quicksort_exec ( $array, $field, $i );
		
		foreach ( $array as $row ) {
			$anydataNode->appendChild ( $row );
		}
		
		return;
	}
	
	protected function quicksort_exec($seq, $field, $pos) {
		if (! count ( $seq ))
			return $seq;
		
		$k = $seq [0];
		$x = $y = array ();
		
		for($i = 1; $i < count ( $seq ); $i ++) {
			$fieldNode = $seq [$i]->childNodes->item ( $pos );
			if ($fieldNode->nodeValue <= $k->childNodes->item ( $pos )->nodeValue) {
				$x [] = $seq [$i];
			} else {
				$y [] = $seq [$i];
			}
		}
		
		return array_merge ( $this->quicksort_exec ( $x, $field, $pos ), array ($k ), $this->quicksort_exec ( $y, $field, $pos ) );
	}
	
	public static function orderBy($iterator, $field, $order = "A") {
		$result = new AnyDataSet ( );
		
		while ( $iterator->hasNext () ) {
			$sr = $iterator->moveNext ();
			
			$itResult = $result->getIterator ();
			
			$nodeBefore = null;
			$added = false;
			while ( $itResult->hasNext () ) {
				$srResult = $itResult->moveNext ();
				if ($order == "A") {
					$compare = ($srResult->getField ( $field ) < $sr->getField ( $field ));
				} else {
					$compare = ($srResult->getField ( $field ) > $sr->getField ( $field ));
				}
				if ($compare) {
					$nodeBefore = $srResult->getDomObject ();
				} else {
					if (is_null ( $nodeBefore )) {
						$result->appendRow ();
					} else {
						$result->insertRowBefore ( $nodeBefore );
					}
					$added = true;
					break;
				}
			}
			
			if (! $added) {
				$result->appendRow ();
			}
			
			$arr = $sr->getFieldNames ();
			foreach ( $arr as $key => $fieldname ) {
				$result->addField ( $fieldname, $sr->getField ( $fieldname ) );
			}
		
		}
		
		return $result;
	}

}

?>
