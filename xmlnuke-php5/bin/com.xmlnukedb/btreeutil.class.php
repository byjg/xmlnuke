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

class BTreeUtil
{
	public static $COM_ACENTO = 
		array(129=>"A", 161=>"a", 137=>"E", 169=>"e", 141=>"I", 173=>"i", 147=>"O", 179=>"o", 
			154=>"U", 186=>"u", 131=>"A", 163=>"a", 149=>"O", 181=>"o", 128=>"A", 160=>"a", 
			136=>"E", 168=>"e", 140=>"I", 172=>"i", 146=>"O", 178=>"o", 153=>"U", 185=>"u", 
			130=>"A", 162=>"a", 138=>"E", 170=>"e", 142=>"I", 174=>"i", 148=>"O", 180=>"o", 
			155=>"U", 155=>"u", 132=>"A", 164=>"a", 139=>"E", 171=>"e", 143=>"I", 175=>"i", 
			150=>"O", 182=>"o", 156=>"U", 188=>"u", 145=>"N", 177=>"n", 135=>"C", 167=>"c");

	const VALID_CHAR = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

	/**
	*@param string $documento
	*@param string $origem
	*@param BTree $btree
	*@return BTree
	*/
	private static function insertTokens($documento, $origem, $btree)
	{
		//$documento = utf8_decode($documento);
		//$documento = strtolower($documento);

		$ComAcento = self::$COM_ACENTO;
		$ValidChar = self::VALID_CHAR;

		$palavra = "";
		$Letra = " ";

		// Futuramente esse trecho de código poderá ser retirado para permitir a localização exata do documento.
		$j = strpos($origem,'#');
		if ($j >= 0)
		{
			$origem = substr($origem, 0, $j);
		}
		
		for($i=0, $length = strlen($documento); $i<$length; $i++)
		{
			$Letra = $documento[$i];
			if((ord($Letra) == 195) || (ord($Letra) == 227)) // IS A UTF8 SPECIAL CHAR
			{
				$i++;
				$ii = ord($documento[$i]);
				$Letra = $ComAcento[$ii];
			}

			// Determinar se a letra é válida
			if (strpos($ValidChar,$Letra) !== false)
			{
				$palavra =$palavra.$Letra;
			}
			else
			{
				// Se a palavra formada possuir tamanho menor que dois, ignorar.
				if (strlen($palavra) > 2)
				{
					$btree = BTree::insert(new BTreeNode(strtolower($palavra), $origem), $btree);
				}
				$palavra = "";
			}
		}
		// Necessário repetir essa linha para inserir a última palavra!!
		if (strlen($palavra) > 2)
		{
			$btree = BTree::insert(new BTreeNode(strtolower($palavra), $origem), $btree);
		}

		return $btree;
	}

	/**
	*@param string $word
	*@param BTree $btree
	*@param bool $includeAllDocs
	*/
	public static function searchDocuments($word, $btree, $includeAllDocs)
	{
		$ComAcento = self::$COM_ACENTO;
		$ValidChar = self::VALID_CHAR;

		$wordValue = strtolower($word);
		$searchFor = explode(" ",$wordValue);

		$resultAux = array();

		for($i=0, $lengthSearch = count($searchFor); $i<$lengthSearch; $i++)
		{
			$aux = "";
			for($j=0, $strlen = strlen($searchFor[$i]); $j<$strlen; $j++)
			{
				$Letra = substr($searchFor[$i],$j,1);
				if((ord($Letra) == 195) || (ord($Letra) == 227)) // IS A UTF8 SPECIAL CHAR
				{
					$j++;
					$jj = ord(substr($searchFor[$i],$j,1));
					$Letra = $ComAcento[$jj];
				}
				$aux = $aux.$Letra;
			}
			$searchFor[$i] = $aux;
			$bnode = BTree::containsNode(new BTreeNode($searchFor[$i],null), $btree);
			// Se nao existir o no e nao for para incluir tudo, entao houve insucesso na busca
			if ($bnode == null)
			{
				if (!$includeAllDocs)
				{
					return null;
				}
			}
			// Caso contrário o elemento é adicionado a uma lista temporaria
			else
			{
				foreach($bnode->values()as $s)
				{
					$aux = @$resultAux[$s];
					if (($aux == null) || ($aux == ""))
					{
						$resultAux[$s] = "1";
					}
					else
					{
						$resultAux[$s] = (intval($aux) + 1);
					}
				}

			}
		}
		// Verificar se os elementos podem ser incluidos na resposta.
		// No caso de incluir todos os elementos, sao todos os nos localizados.
		// Ja no caso de obter apenas os elementos procurados, deve-se verificar se a quantidade
		// de elementos inseridos é igual a quantidade de palavras encontradas.
		$result = array();
		//$Coll = new Collection($result);

		foreach($resultAux as $s=>$value)
		{
			if ((intval($value) == count($searchFor)) || $includeAllDocs)
			{
				$result[]=$s;
			}
		}
		if (count($result) == 0)
		{
			$result = null;
		}
		return $result;
	}

	/**
	*@param DOMNode $node
	*@param string $xpath
	*@param BTree $btree
	*@return BTree
	*/
	public static function navigateNodes($node, $xpath, $btree)
	{
		if (!is_null($node))
		{
			if ($node->nodeType == XML_TEXT_NODE)
			{
				$btree = BTreeUtil::insertTokens($node->nodeValue, $xpath, $btree);
			}
			if ($node->hasChildNodes() )
			{

				$btree = self::navigateNodes($node->firstChild, $xpath.$node->nodeName."/",$btree);
			}
			// Ver se coloco Attributes aqui.... Acho que nao.
			$btree = self::navigateNodes($node->nextSibling, $xpath, $btree);
		}

		return $btree;

	}

}


?>
