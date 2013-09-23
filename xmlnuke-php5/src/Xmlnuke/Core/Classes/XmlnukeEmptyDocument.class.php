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
 * Implements a XMLNuke Document. 
 * 
 * Any module in XMLNuke must return a IXmlnukeDocument object. This class is a concrete implementaion of the interface. 
 * 
 * You can implement your own document, like HumanML for example and use this in your module. 
 * 
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Classes;

class  XmlnukeEmptyDocument extends XmlnukeCollection implements IXmlnukeDocument
{

	protected $_rootNode = "xmlnuke";

	/**
	*@desc XmlnukeDocument constructor
	*@param string $pageTitle
	*@param string $desc
	*/
	public function __construct($rootNode = "xmlnuke")
	{
		$this->_rootNode = $rootNode;
	}	
		
	/**
	*@desc Generate page, processing yours childs using the parent.
	*@return DOMDocument
	*/
	public function makeDomObject()
	{
		$xmlDoc = XmlUtil::CreateXmlDocument();

		// Create the First first NODE ELEMENT!
		$nodePage = $xmlDoc->createElement($this->_rootNode);
		$xmlDoc->appendChild($nodePage);

		// Process ALL XmlnukeDocumentObject existing in Collection.
		//----------------------------------------------------------
		parent::generatePage($nodePage);
		//----------------------------------------------------------

		/*
		// Finalize the Create Page Execution
		XmlUtil::CreateChild($nodeMeta, "created", $created);
		XmlUtil::CreateChild($nodeMeta, "modified", date("d/M/y h:m:s"));
		$elapsed = microtime(true)-$createdTimeStamp;
		XmlUtil::CreateChild($nodeMeta, "timeelapsed", intval($elapsed/3600) . ":" . intval($elapsed/60)%60 . ":" . $elapsed%60 . "." . substr(intval((($elapsed - intval($elapsed))*1000))/1000, 2) );
		XmlUtil::CreateChild($nodeMeta, "timeelapsedsec", $elapsed );
		*/

		return $xmlDoc;
	}
	
	/**
	 * Returns a IXmlnukeDocument. 
	 * 
	 * In the newer versions you can simply return the object
	 * 
	 * @deprecated since version 3.0
	 * @package xmlnuke
	 * @return IXmlnukeDocument
	 */
	public function generatePage($obj = null)
	{
		if ($obj != null)
			throw  new InvalidArgumentException("You do not need pass an argument for XmlnukeDocument generatePage()");

		return $this;
	}

}

?>
