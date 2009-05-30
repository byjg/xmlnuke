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
*@package com.xmlnuke
*@subpackage xmlnukeobject
*/
class XmlnukeStringXML extends XmlnukeDocumentObject
{
	protected $_xml;
	
	public function XmlnukeStringXML($xml)
	{
		$this->_xml = $xml;
	}
	
	/**
    *@desc Contains specific instructions to generate all XML informations. This method is processed only one time. Usually is the last method processed.
    *@param DOMNode $current
    *@return void
    */
    public function generateObject($current)
    {
            try
            {
                XmlUtil::innerXML($current, $this->_xml);
            }
            catch (Exception $e)
            {
                throw new XmlNukeObjectException(852, "String bad formated.");
            }
    }

}
?>