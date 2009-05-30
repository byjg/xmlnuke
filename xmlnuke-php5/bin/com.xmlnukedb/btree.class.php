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

class BTree
{

	const T = 3;

	/** Indicates how many data items are stored in this node.  For all
	*  nodes except the root of the tree, this will be somewhere between
	*  (t-1) and (2t-1).
	*@var int
	*/
	private $_n;

	/** An array of data items.  Each such array will be allocated (2t-1)
	*  slots.
	*@var IBTreeNode
	*/
	private $_keys;

	/** An array of pointers to child nodes.  Each such array will be
	*  allocated 2t slots.
	*@var BTree
	*/
	private $_children;

	//end of line delimiters
	const LF = "\n";


	/** Construct an empty B-tree node; we mark this constructor as
	*  private because the values in the constructed node will not
	*  be valid until further initialization has been carried out.
	*@param IBTreeNode $value = null
	*/
	private function BTree($value = null)
	{
		if($value==null)
		{
			$this->_n  = 0;
		}
		else
		{
			$this->_n       = 1;
			$this->_keys[0] = $value;
		}
	}

	/** 
	*Determine whether the root node of a (non-null) B-tree is full.
	*@return bool
	*/
	public function full()
	{
		return ($this->_n == 2*self::T-1);
	}

	/** Determine whether a particular value occurs in this tree.
	*  Parameters : $value type IBTreeNode/$btree type BTree
	*@param IBTreeNode $value
	*@param BTree $btree
	*@return bool
	*/
	public static function contains($value, $btree)
	{
		while ($btree!=null)
		{
			$i = 0;
			for (; $i<$btree->_n; $i++)
			{
				if ($value->equalsTo($btree->_keys[$i]))
				{
					return true;
				}
				else if ($value->lessThan($btree->_keys[$i]))
				{
					break;
				}
			}
			$btree = $btree->_children[$i];
		}
		return false;
	}

	/**
	*@param IBTreeNode $value
	*@param BTree $btree
	*@return IBTreeNode;
	*/
	public static function containsNode($value, $btree)
	{
		while ($btree!=null)
		{
			$i = 0;
			for (; $i<$btree->_n; $i++)
			{
				if ($value->equalsTo($btree->_keys[$i]))
				{
					return $btree->_keys[$i];
				}
				else if ($value->lessThan($btree->_keys[$i]))
				{
					break;
				}
			}
			$btree = @$btree->_children[$i];
		}
		return null;
	}


	/** Insert a value into a B-tree node.
    *@param  IBTreeNode $value
    *@param  BTree $BTree
    *@return BTree
    */
	public static function insert($value, $btree)
	{
		if ($btree==null)
		{
			return new BTree($value);
		}
		else
		{
			/**
			*@var IBTreeNode
			*/
			$bnode=self::containsNode($value, $btree);
			if ($bnode == null)
			{

				if ($btree->full())
				{
					$root       = new BTree($btree->_keys[self::T-1]);
					$root->_children[0] = $btree;
					$root->_children[1] = $btree->split();
					$btree	    = $root;
				}
				// At this point, we can guarantee that btree
				// is a non-null and non-full BTree node.

				self::insertNonFull($value, $btree);
			}
			else
			{			       
				$values = $value->values(); 
				$bnodeValues = $bnode->values();
				
				foreach($values as $o )
				{
					$found = array_search($o, $bnodeValues);
					if ($found === false)
					{
						$bnode->addValue($o);
					}
				}
			}
		}

		return $btree;
	}


	/** Insert a value into a non-full (and non-null) B-tree node.
    *@param  IBTreeNode $value
    *@param  BTree $BTree
    *@return BTree
    */
	private static function insertNonFull($value, $btree)
	{
		$i = 0;
		while ($i<$btree->_n && $value->greaterThan($btree->_keys[$i]))
		{
			$i++;
		}
		if (@$btree->_children[$i]==null)
		{		       // Leaf node
			$btree->shiftUp($i, $value);
			$btree->_children[$i+1] = null;
		}
		elseif ($btree->_children[$i]->full())
		{	       // Full child
			$pivot = $btree->_children[$i]->_keys[self::T-1];
			$btree->shiftUp($i, $pivot);
			$btree->_children[$i+1] =  $btree->_children[$i]->split();
			self::insertNonFull($value,  $btree->_children[($value->greaterThan($pivot)) ? ($i+1) : $i]);
		}
		else
		{				       // Non-full child
			self::insertNonFull($value, $btree->_children[$i]);
		}
	}


	/** Insert a value into a B-tree node.
	*@param int $i
	*@package IBTreeNode $key
	*@return void
	*/
	private function shiftUp($i, $key)
	{
		for ($j=$this->_n; $j>$i; $j--)
		{
			$this->_keys[$j] = $this->_keys[$j-1];
			$this->_children[$j+1] = @$this->_children[$j];
		}
		$this->_keys[$i] = $key;
		$this->_n++;
	}

	
	/** Split a full B-tree node, modifying the receiver (the left half)
	*  and returning the new node (the right half).  We assume that this
	*  method is invoked only on full tree nodes, meaning that btree.n
	*  will be 2*t-1.
	*@return BTree
	*/
	private function split()
	{
		$right = new BTree();

		for ($i=0; $i<self::T-1; $i++)
		{
			$right->_keys[$i]      = $this->_keys[self::T+$i];
			$right->_children[$i]  = $this->_children[self::T+$i];
			$this->_children[self::T+$i];
		}

		$right->_children[self::T-1]  = $this->_children[2*self::T-1];
		$this->_children[2*self::T-1] = null;
		$this->_n	       = (self::T-1);
		$right->_n	      = (self::T-1);

		return $right;

	}

	/**
	*@param BTree $btree
	*@param string $filename
	*@return void
	*/
	public static function save($btree, $filename)
	{
		$handle = fopen($filename, "w");

		self::saveLoop($btree,$handle);

		fclose ($handle);

	}

	//Metodo anteriormente chamado de save
	//Parameters: $btree type BTree / $handle
	private static function saveLoop($btree,$handle)
	{
		if ($btree==null)
		{
			return null;
		}
		else
		{
			$i = 0;
			for (; $i<$btree->_n; $i++)
			{
				fwrite($handle,$btree->_keys[$i]->getKey().self::LF);
				foreach($btree->_keys[$i]->values() as $s)
				{
					fwrite($handle,"+".$s.self::LF);
				}
				self::saveLoop(@$btree->_children[$i],$handle);
			}
			self::saveLoop($btree->_children[$i],$handle);
		}

	}

	//Parameter: $filename type string
	public static function load($filename)
	{
		$btree = null;

		if (!file_exists($filename))
		{
			return null;
		}
		$lines = file($filename);

		foreach ($lines as $i=>$linha)
		{
			if (substr($linha,0,1) != '+')
			{
				$bnode = new BTreeNode($linha);
				$btree = BTree::insert($bnode, $btree);
			}
			else
			{
				$bnode->addValue(substr($linha,1));
			}
		}
		return $btree;
	}

}



/*METODO DE TESTE

public static function teste()
{   echo 'LF :'.self::LF;
echo 't '.self::T;
echo 'Entrou no teste<br>';

echo '<BR>Teste de construtor<br>';
$array = array(10,20,30,40,50);
$BtreeNode= new BTreeNode('chave',$array);

$Btree= new BTree($BtreeNode);

echo 'Numero de linhas '.$Btree->_n.'<br>';
echo 'KEY: '.$Btree->_keys[0]->getKey().'<br>';
echo 'VALUES: ';
print_r($Btree->_keys[0]->values());
echo '<br>Fim de teste de construtor<br>';

echo '<br>Teste do metodo save<br>';
$fileName = "../apagar_modificado.txt";
self::save($Btree,$fileName);

echo '<br>Fim de teste do metodo save<br>';

echo '<br>Teste do metodo load<br>';
$fileName = "../apagar_modificado.txt";
$btree2 = self::load($fileName);
echo '<br>btree2-KEY:  '.$btree2->_keys[0]->getKey().'<br>';
echo 'btree2-VALUES: ';
print_r($btree2->_keys[0]->values());
$fileName2 = "../teste.txt";
self::save($btree2,$fileName2);
echo '<br>Fim de teste do metodo load<br>';

echo '<br>Saiu do teste';
}

}
METODO DE TESTE*/


?>
