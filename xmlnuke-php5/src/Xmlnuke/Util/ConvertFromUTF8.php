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

class ConvertFromUTF8
{
	/**
	 * Enter description here...
	 *
	 * @param string $text
	 * @param int $wrap
	 * @return string
	 */
	public static function ISO88591_ASCII($text, $wrap = 0)
	{
		$ISO88591_CONV = 
			array
			(
				161=>"=E1" /*á*/, 169=>"=E9" /*é*/, 173=>"=ED" /*í*/, 179=>"=F3" /*ó*/, 186=>"=FA" /*ú*/, 
				160=>"=E0" /*à*/, 168=>"=E8" /*è*/, 172=>"=EC" /*ì*/, 178=>"=F2" /*ò*/, 185=>"=F9" /*ù*/, 
				163=>"=E3" /*ã*/, 181=>"=F5" /*õ*/, 177=>"=F1" /*ñ*/, 162=>"=E2" /*â*/, 170=>"=EA" /*ê*/, 
				174=>"=EE" /*î*/, 180=>"=F4" /*ô*/, 187=>"=FB" /*û*/, 167=>"=E7" /*ç*/, 129=>"=C1" /*Á*/, 
				137=>"=C9" /*É*/, 141=>"=CD" /*Í*/, 147=>"=D3" /*Ó*/, 154=>"=DA" /*Ú*/, 128=>"=C0" /*À*/, 
				136=>"=C8" /*È*/, 140=>"=CC" /*Ì*/, 146=>"=D2" /*Ò*/, 153=>"=D9" /*Ù*/, 131=>"=C3" /*Ã*/, 
				149=>"=D5" /*Õ*/, 145=>"=D1" /*Ñ*/, 130=>"=C2" /*Â*/, 138=>"=CA" /*Ê*/, 142=>"=CE" /*Î*/, 
				148=>"=D4" /*Ô*/, 155=>"=DB" /*Û*/, 135=>"=C7" /*Ç*/, 188=>"=FC" /*ü*/, 156=>"=DC" /*Ü*/,
				132=>"=C4" /*Ä*/, 133=>"=C5" /*Å*/, 134=>"=C6" /*Æ*/, 139=>"=CB" /*Ë*/,	143=>"=CF" /*Ï*/,
				144=>"=D0" /*Ð*/, 150=>"=D6" /*Ö*/, 152=>"=D8" /*Ø*/, 157=>"=DD" /*Ý*/, 158=>"=DE" /*Þ*/,
				159=>"=DF" /*ß*/, 164=>"=E4" /*ä*/, 165=>"=E5" /*å*/, 166=>"=E6" /*æ*/, 171=>"=EB" /*ë*/,
				175=>"=EF" /*ï*/, 176=>"=F0" /*ð*/, 182=>"=F6" /*ö*/, 184=>"=F8" /*ø*/, 189=>"=FD" /*ý*/,
				191=>"=FF" /*ÿ*/, 190=>"=FE" /*þ*/,
			);
	
		$textPre = str_replace("=", "=3D", $text);
		$result = ConvertFromUTF8::BaseConversion($ISO88591_CONV, $textPre);
		if ($result == $textPre)
			return $text;
		else 
			$result = str_replace(" ", "_", $result);
		
		if ($wrap == 0)
			return "=?iso-8859-1?Q?" . $result . "?=";
		else
		{
			$newResult = "=?iso-8859-1?Q?";
			$contaLinha = 0;
			for ($i=0; $i<strlen($result); $i++)
			{
				if (($result[$i] == "=") && ($contaLinha >= ($wrap-3)) || ($contaLinha >= $wrap))
				{
					$newResult .= "?=\r\n=?iso-8859-1?Q?" . $result[$i];
					$contaLinha = 0;
				}
				else 
				{
					$newResult .= $result[$i];
				}
				
					
				$contaLinha++;
			}
			$newResult .= "?=";
			return $newResult;
		}
	
	}
	
	/**
	 * Remove all accents from UTF8 Chars.
	 *
	 * @param string $text
	 * @return string
	 */
	public static function RemoveAccent($text)
	{
		$ASCII_CONV = 
			array
			(
				161=>"a" /*á*/, 169=>"e" /*é*/, 173=>"i" /*í*/, 179=>"o" /*ó*/, 186=>"u" /*ú*/, 
				160=>"a" /*à*/, 168=>"e" /*è*/, 172=>"i" /*ì*/, 178=>"o" /*ò*/, 185=>"u" /*ù*/, 
				163=>"a" /*ã*/, 181=>"o" /*õ*/, 177=>"n" /*ñ*/, 162=>"a" /*â*/, 170=>"e" /*ê*/, 
				174=>"i" /*î*/, 180=>"o" /*ô*/, 187=>"u" /*û*/, 167=>"c" /*ç*/, 129=>"A" /*Á*/, 
				137=>"E" /*É*/, 141=>"I" /*Í*/, 147=>"O" /*Ó*/, 154=>"U" /*Ú*/, 128=>"A" /*À*/, 
				136=>"E" /*È*/, 140=>"I" /*Ì*/, 146=>"O" /*Ò*/, 153=>"U" /*Ù*/, 131=>"A" /*Ã*/, 
				149=>"O" /*Õ*/, 145=>"N" /*Ñ*/, 130=>"A" /*Â*/, 138=>"E" /*Ê*/, 142=>"I" /*Î*/, 
				148=>"O" /*Ô*/, 155=>"U" /*Û*/, 135=>"C" /*Ç*/, 188=>"u" /*ü*/, 156=>"U" /*Ü*/,
				132=>"A" /*Ä*/, 133=>"A" /*Å*/, 134=>"AE" /*Æ*/, 139=>"E" /*Ë*/, 143=>"I" /*Ï*/,
				144=>"D" /*Ð*/, 150=>"O" /*Ö*/, 152=>"0" /*Ø*/, 157=>"Y" /*Ý*/, 158=>"P" /*Þ*/,
				159=>"B" /*ß*/, 164=>"a" /*ä*/, 165=>"a" /*å*/, 166=>"ae" /*æ*/, 171=>"e" /*ë*/,
				175=>"i" /*ï*/, 176=>"o" /*ð*/, 182=>"o" /*ö*/, 184=>"0" /*ø*/, 189=>"y" /*ý*/,
				191=>"y" /*ÿ*/, 190=>"p" /*þ*/,
			);
	
		return ConvertFromUTF8::BaseConversion($ASCII_CONV, $text);
	}
	
	public static function ToHtmlEntities($text)
	{
		$ASCII_CONV = 
			array
			(
				161=>"&aacute;" /*á*/, 169=>"&eacute;" /*é*/, 173=>"&iacute;" /*í*/, 179=>"&oacute;" /*ó*/, 186=>"&uacute;" /*ú*/, 
				160=>"&agrave;" /*à*/, 168=>"&egrave;" /*è*/, 172=>"&igrave;" /*ì*/, 178=>"&ograve;" /*ò*/, 185=>"&ugrave;" /*ù*/, 
				163=>"&atilde;" /*ã*/, 181=>"&otilde;" /*õ*/, 177=>"&ntilde;" /*ñ*/, 162=>"&acirc;"  /*â*/, 170=>"&ecirc;"  /*ê*/, 
				174=>"&icirc;"  /*î*/, 180=>"&ocirc;"  /*ô*/, 187=>"&ucirc;"  /*û*/, 167=>"&ccedil;" /*ç*/, 129=>"&Aacute;" /*Á*/, 
				137=>"&Eacute;" /*É*/, 141=>"&Iacute;" /*Í*/, 147=>"&Oacute;" /*Ó*/, 154=>"&Uacute;" /*Ú*/, 128=>"&Agrave;" /*À*/, 
				136=>"&Egrave;" /*È*/, 140=>"&Igrave;" /*Ì*/, 146=>"&Ograve;" /*Ò*/, 153=>"&Ugrave;" /*Ù*/, 131=>"&Atilde;" /*Ã*/, 
				149=>"&Otilde;" /*Õ*/, 145=>"&Ntilde;" /*Ñ*/, 130=>"&Acirc;"  /*Â*/, 138=>"&Ecirc;"  /*Ê*/, 142=>"&Icirc;"  /*Î*/, 
				148=>"&Ocirc;"  /*Ô*/, 155=>"&Ucirc;"  /*Û*/, 135=>"&Ccedil;" /*Ç*/, 188=>"&uuml;"   /*ü*/, 156=>"&Uuml;"   /*Ü*/,
				132=>"&Auml;"   /*Ä*/, 133=>"&Aring;"  /*Å*/, 134=>"&AElig;"  /*Æ*/, 139=>"&Euml;"   /*Ë*/, 143=>"&Iuml;"   /*Ï*/,
				144=>"&ETH;"    /*Ð*/, 150=>"&Ouml;"   /*Ö*/, 152=>"&Oslash;" /*Ø*/, 157=>"&Yacute;" /*Ý*/, 158=>"&THORN;"  /*Þ*/,
				159=>"&szlig;"  /*ß*/, 164=>"&auml;"   /*ä*/, 165=>"&aring;"  /*å*/, 166=>"&aelig;"  /*æ*/, 171=>"&euml;"   /*ë*/,
				175=>"&iuml;"   /*ï*/, 176=>"&eth;"    /*ð*/, 182=>"&ouml;"   /*ö*/, 184=>"&oslash;" /*ø*/, 189=>"&yacute;" /*ý*/,
				191=>"&yuml;"   /*ÿ*/, 190=>"&thorn;"  /*þ*/,
			);
	
		return ConvertFromUTF8::BaseConversion($ASCII_CONV, $text);
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
		$result = "";
		for ($i=0; $i<strlen($text); $i++)
		{
			if (ord($text[$i])==195)
			{
				$i++;
				$result .= $vector[ord($text[$i])];
			}
			else 
			{
				$result .= $text[$i];
			}
		}

		return $result;
	}
}
