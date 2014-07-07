<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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

namespace Xmlnuke\Util;

class ConvertToUTF8
{
	public static function FromHtmlEntities($text)
	{
		$HTML_ENTITIES = 
			array
			(
				"&aacute;"=>161 /*á*/, "&eacute;"=>169 /*é*/, "&iacute;"=>173 /*í*/, "&oacute;"=>179 /*ó*/, "&uacute;"=>186 /*ú*/, 
				"&agrave;"=>160 /*à*/, "&egrave;"=>168 /*è*/, "&igrave;"=>172 /*ì*/, "&ograve;"=>178 /*ò*/, "&ugrave;"=>185 /*ù*/, 
				"&atilde;"=>163 /*ã*/, "&otilde;"=>181 /*õ*/, "&ntilde;"=>177 /*ñ*/, "&acirc;"=>162  /*â*/, "&ecirc;"=>170  /*ê*/, 
				"&icirc;"=>174  /*î*/, "&ocirc;"=>180  /*ô*/, "&ucirc;"=>187  /*û*/, "&ccedil;"=>167 /*ç*/, "&Aacute;"=>129 /*Á*/, 
				"&Eacute;"=>137 /*É*/, "&Iacute;"=>141 /*Í*/, "&Oacute;"=>147 /*Ó*/, "&Uacute;"=>154 /*Ú*/, "&Agrave;"=>128 /*À*/, 
				"&Egrave;"=>136 /*È*/, "&Igrave;"=>140 /*Ì*/, "&Ograve;"=>146 /*Ò*/, "&Ugrave;"=>153 /*Ù*/, "&Atilde;"=>131 /*Ã*/, 
				"&Otilde;"=>149 /*Õ*/, "&Ntilde;"=>145 /*Ñ*/, "&Acirc;"=>130  /*Â*/, "&Ecirc;"=>138  /*Ê*/, "&Icirc;"=>142  /*Î*/, 
				"&Ocirc;"=>148  /*Ô*/, "&Ucirc;"=>155  /*Û*/, "&Ccedil;"=>135 /*Ç*/, "&uuml;"=>188   /*ü*/, "&Uuml;"=>156   /*Ü*/,
				"&Auml;"=>132   /*Ä*/, "&Aring;"=>133  /*Å*/, "&AElig;"=>134  /*Æ*/, "&Euml;"=>139   /*Ë*/, "&Iuml;"=>143   /*Ï*/,
				"&ETH;"=>144    /*Ð*/, "&Ocirc;"=>148  /*Ô*/, "&Ouml;"=>150   /*Ö*/, "&Oslash;"=>152 /*Ø*/, "&Uring;"=>155  /*Û*/, 
				"&Uuml;"=>156   /*Ü*/, "&Yacute;"=>157 /*Ý*/, "&THORN;"=>158  /*Þ*/, "&szlig;"=>159  /*ß*/, "&auml;"=>164   /*ä*/, 
				"&aring;"=>165  /*å*/, "&aelig;"=>166  /*æ*/, "&euml;"=>171   /*ë*/, "&iuml;"=>175   /*ï*/, "&eth;"=>176    /*ð*/, 
				"&ouml;"=>182   /*ö*/, "&oslash;"=>184 /*ø*/, "&uml;"=>188    /*ü*/, "&yacute;"=>189 /*ý*/, "&yuml;"=>191   /*ÿ*/, 
				"&thorn;"=>190 /*þ*/,
			);
	
		return ConvertToUTF8::BaseConversion($HTML_ENTITIES, $text);
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param string[] $vector
	 * @param string $text
	 * @return string
	 */
	protected static function BaseConversion($vector, $text)
	{
		foreach ($vector as $key=>$value) 
		{
			$text = str_replace($key, chr(195).chr($value), $text);
		}
		
		return $text;
	}
	
}
