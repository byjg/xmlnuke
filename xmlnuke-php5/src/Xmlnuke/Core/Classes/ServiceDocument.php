<?php

namespace Xmlnuke\Core\Classes;

use DOMDocument;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\XMLTransform;
use ByJG\Util\XmlUtil;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rdfdocument
 *
 * @author joao.gilberto
 */
class ServiceDocument extends XmlnukeCollection implements IXmlnukeDocument
{
	/**
	*@desc XmlnukeDocument constructor
	*@param string $pageTitle
	*@param string $desc
	*/
	public function __construct($pageTitle = "", $desc = "")
	{
		//if (Context::getInstance()->get("raw") == "")
		//	Context::getInstance()->set("raw", "json");

		$this->_xmlTransform = XMLTransform::Model;
	}

	/**
	*@desc Generate page, processing yours childs using the parent.
	*@return DOMDocument
	*/
	public function makeDomObject()
	{
		$xmlDoc = XmlUtil::CreateXmlDocument();
		$root = XmlUtil::CreateChild($xmlDoc, "root" );

		// Process ALL XmlnukeDocumentObject existing in Collection.
		//----------------------------------------------------------
		parent::generatePage($root);
		//----------------------------------------------------------

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
		return $this;
	}

}

?>
