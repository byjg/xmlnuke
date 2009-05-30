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

class XmlNukeDB
{
	private $_btree;
	private $_btreeDir;
	private $_repositoryDir;
	private $_btreeLoaded = false;
	/**
	*@var PersistUtil
	*/
	private $_persistUtil;

	/**
	 * Constructor
	 *
	 * @param bool $hashedDir
	 * @param string $repositoryDir
	 * @param string $lang
	 * @param bool $createdir
	 * @return XmlNukeDB
	 */
	public function XmlNukeDB($hashedDir, $repositoryDir, $lang, $createdir = false)
	{
		$this->_persistUtil = new PersistUtil($repositoryDir, $lang, $createdir);
		$this->_persistUtil->setHashedDir($hashedDir);
		$this->_btree = null;
		$this->_repositoryDir = $repositoryDir.PersistUtil::getSlash().$lang;
		$this->_btreeDir =  $this->_repositoryDir.PersistUtil::getSlash()."index.php.btree";
		$this->_btreeLoaded = false;
	}
	
	private function checkBtreeIsLoaded()
	{
		if (!$this->_btreeLoaded)
		{
			$this->loadIndex();
			$this->_btreeLoaded = true;
		}
	}

	public function getBtree()
	{
		return $this->_btree;
	}

	public function setBtree($value)
	{
		$this->_btree = $value;
	}

	//SOBRECARGA SUPRIMIDA
	//Parameters : string $document / string $rootNode
	public function getDocument($document, $rootNode)
	{
		if ($rootNode == null)
		{
			$rootNode = "page";
		}
		return $this->_persistUtil->getDocument($document, $rootNode);
	}

	//Parameters: string $words, bool $includeAllDocs
	public function searchDocuments($words, $includeAllDocs)
	{
		$this->checkBtreeIsLoaded();
		
		if ($this->_btree == null)
		{
			throw new KernelException(601, "You must have a valid BTree. Try recreateIndex.");
		}
		else
		{
			$arr = BTreeUtil::searchDocuments(strtolower(trim($words)), $this->_btree, $includeAllDocs);

			return $arr;
		}
	}

	//SOBRECARGA SUPRIMIDA
	//@ Esses tres metodos eram antes saveDocument
	//Parameters : string $documentName, Stream $stream
	public function saveDocumentStream($documentName, $stream)
	{
		$xml = new DOMDocument;
		$xmlstr = FileUtil::QuickFileRead($this->_persistUtil->getFullFileName($documentName));
		$this->saveDocumentStr($documentName, $xmlstr);
	}
	//Parameters : Strings
	public function saveDocumentStr($documentName, $xmlstr)
	{
		$xml = XmlUtil::CreateXmlDocumentFromStr($xmlstr);
		self::saveDocumentXML($documentName, $xml);
	}
	//Parameters: string $documentName, DOMDocument $xml
	public function saveDocumentXML($documentName, $xml)	
	{	
		$this->checkBtreeIsLoaded();
		$this->_btree = $this->_persistUtil->saveDocument($documentName, $xml, $this->_btree);
	}

	//Parameters: String
	public function importDocuments($directory, $filemask)
	{
		$this->checkBtreeIsLoaded();

		$this->_btree = $this->_persistUtil->importDocuments($directory, $this->_btree, true, $filemask);
	}

	public function loadIndex()
	{
		$this->_btree = BTree::load($this->_btreeDir);
	}

	public function saveIndex()
	{
		$this->checkBtreeIsLoaded();
		BTree::save($this->_btree, $this->_btreeDir);
	}

	public function recreateIndex()
	{
		$this->checkBtreeIsLoaded();
		$this->_btree = null;
		$this->_btree = $this->_persistUtil->importDocuments($this->_repositoryDir, $this->_btree, false,null);
	}

	//Parameter: String
	public function existsDocument($documentName)
	{
		return $this->_persistUtil->existsDocument($documentName);
	}
}
?>
