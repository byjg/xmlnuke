/** An implementation of B-trees: balanced multiway search trees.
 *
 *  Mark P Jones, April 2001
 */

/**
TODO:
 - Mudar de INT para uma classe NO que implementa: MENOR, IGUAL, CHAVE, VALOR
 - Possibilitar que elementos inseridos não sejam repetidos. Dessa forma, criar uma colecao em KEYS para garantir (chave continua sendo unica, mas valor que esse algoritmo nao tem, seria uma colecao). 
 - Implementar os movimentos de NEXT e PREVIOUS na árvore.
*/

using System;
using System.Collections;

namespace com.xmlnuke.db
{

	public class BTree {
	    /** The constant t determines the maximum number of data items that
	     *  can be stored at each node.
	     */
	    public static int t = 3;
		
	    /** Indicates how many data items are stored in this node.  For all
	     *  nodes except the root of the tree, this will be somewhere between
	     *  (t-1) and (2t-1).
	     */
	    private int n;
	
	    /** An array of data items.  Each such array will be allocated (2t-1)
	     *  slots.
	     */
	    private IBTreeNode[] keys;
	
	    /** An array of pointers to child nodes.  Each such array will be
	     *  allocated 2t slots.
	     */
	    private BTree[] children;
	
	    /** Construct an empty B-tree node; we mark this constructor as
	     *  private because the values in the constructed node will not
	     *  be valid until further initialization has been carried out.
	     */
	    private BTree() {
	        this.n        = 0;
	        this.keys     = new IBTreeNode[2*t-1];
	        this.children = new BTree[2*t];
	    }
	
	    /** Construct a B-tree node containing just a single value.
	     */
	    private BTree(IBTreeNode value) : this() {
	        this.n       = 1;
	        this.keys[0] = value;
	    }
	
	    /** Determine whether the root node of a (non-null) B-tree is full.
	     */
	    public bool full() {
	        return (n==2*t-1);
	    }
	
	    /** Determine whether a particular value occurs in this tree.
	     */
	    public static bool contains(IBTreeNode value, BTree btree) {
	        while (btree!=null) {
	            int i = 0;
	            for (; i<btree.n; i++) {
	                if (value.equalsTo(btree.keys[i])) {
	                    return true;
	                } else if (value.lessThan(btree.keys[i])) {
	                    break;
	                }
	            }
	            btree = btree.children[i];
	        }
	        return false;
	    }
	
	    public static IBTreeNode containsNode(IBTreeNode value, BTree btree) {
	        while (btree!=null) {
	            int i = 0;
	            for (; i<btree.n; i++) {
	                if (value.equalsTo(btree.keys[i])) {
	                    return btree.keys[i];
	                } else if (value.lessThan(btree.keys[i])) {
	                    break;
	                }
	            }
	            btree = btree.children[i];
	        }
	        return null;
	    }
	

	    /** Insert a value into a B-tree node.
	     */
	    public static BTree insert(IBTreeNode value, BTree btree) {
	        if (btree==null) {
	            return new BTree(value);
	        } else {
	        	IBTreeNode bnode=BTree.containsNode(value, btree);
	        	if (bnode == null)
	        	{
	        	
		        	if (btree.full()) {
			            BTree root       = new BTree(btree.keys[t-1]);
			            root.children[0] = btree;
			            root.children[1] = btree.split();
			            btree            = root;
			        }
			        // At this point, we can guarantee that btree
			        // is a non-null and non-full BTree node.
			        insertNonFull(value, btree);
	        	}
	        	else
	        	{
	        		foreach(object o in value.values())
	        		{
	        			if (!bnode.values().Contains(o))
	        			{
	        				bnode.values().Add(o);
	        			}
	        		}
	        	}
	        }
	        return btree;
	    }
	
	    /** Insert a value into a non-full (and non-null) B-tree node.
	     */
	    private static void insertNonFull(IBTreeNode value, BTree btree) {
	        int i = 0;
	        while (i<btree.n && value.greaterThan(btree.keys[i])) {
	            i++;
	        }
	        if (btree.children[i]==null) {			// Leaf node
	            btree.shiftUp(i, value);
	            btree.children[i+1] = null;
	        } else if (btree.children[i].full()) {		// Full child
	            IBTreeNode pivot = btree.children[i].keys[t-1];
	            btree.shiftUp(i, pivot);
	            btree.children[i+1] = btree.children[i].split();
	            insertNonFull(value, btree.children[(value.greaterThan(pivot)) ? (i+1) : i]);
	        } else {					// Non-full child
	            insertNonFull(value, btree.children[i]);
	        }
	    }
	
	    /** Shift up the keys and children from a specified position onwards
	     *  to make room for a new entry in a non-full BTree node.  After
	     *  the shift, the caller will need to set children[i+1] to the
	     *  appropriate values.
	     */
	    private void shiftUp(int i, IBTreeNode key) {
	        for (int j=n; j>i; j--) {
	            keys[j]       = keys[j-1];
	            children[j+1] = children[j];
	        }
	        keys[i] = key;
	        n++;
	    }
	
	    /** Split a full B-tree node, modifying the receiver (the left half)
	     *  and returning the new node (the right half).  We assume that this
	     *  method is invoked only on full tree nodes, meaning that btree.n
	     *  will be 2*t-1.
	     */
	    private BTree split() {
	        BTree right = new BTree();
	        for (int i=0; i<t-1; i++) {
	            right.keys[i]      = this.keys[t+i];
	            right.children[i]  = this.children[t+i];
	            this.children[t+i] = null;
	        }
	        right.children[t-1]  = this.children[2*t-1];
	        this.children[2*t-1] = null;
	        this.n               = (t-1);
	        right.n              = (t-1);
	        return right;
	    }
	    
		// **
		// ** Adicionado por João Gilberto
		// **
		
	    public static void save(BTree btree, string filename)
	    {
	   		System.IO.StreamWriter stream = System.IO.File.CreateText(filename);
	    	try
	    	{
	    		BTree.save(btree, stream);
	    	}
	    	finally
	    	{
			    stream.Close();
	    	}
	    }
	    
	    private static void save(BTree btree, System.IO.StreamWriter stream)
	    {
	        if (btree==null) {
	            //stream.Write("-");
	        } else {
	            int i = 0;
	            for (; i<btree.n; i++) {
	                stream.WriteLine(btree.keys[i].getKey().ToString());
	        		foreach(string s in btree.keys[i].values())
	        		{
	        			stream.WriteLine("+" + s);
	        		}
	                save(btree.children[i], stream);
	            }
	            save(btree.children[i], stream);
	        }
	    }
	    
	    public static BTree load(string filename)
	    {
	   		BTree btree = null;
	   		System.IO.StreamReader stream = System.IO.File.OpenText(filename);
	    	try
	    	{
	    		IBTreeNode bnode = null;
		    	string linha = stream.ReadLine();
	    		while (linha != null)
	    		{
		    		if (linha[0] != '+')
		    		{
			    		bnode = new BTreeNode(linha);
			    		btree = BTree.insert(bnode, btree);
		    		}
		    		else
		    		{
			    		bnode.values().Add(linha.Substring(1));
		    		}
	    			linha = stream.ReadLine();
	    		}
	    		
	    	}
	    	finally
	    	{
			    stream.Close();
	    	}
	    	return btree;
	    }
	    
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	    // ------------------------------------------------------------------------
	    // The following code is included for the purposes of testing only.
	    // ------------------------------------------------------------------------
	
	    /** Print out a flat text representation of a B-tree.
	     */
	    public static void print(BTree btree) {
	        if (btree==null) {
	            Console.Write("-");
	        } else {
	            Console.Write("(");
	            int i = 0;
	            for (; i<btree.n; i++) {
	                print(btree.children[i]);
	                Console.Write(" " + btree.keys[i].getKey() + btree.keys[i].values().Count.ToString() + " ");
	            }
	            print(btree.children[i]);
	            Console.Write(")");
	        }
	    }
	
	}

}
