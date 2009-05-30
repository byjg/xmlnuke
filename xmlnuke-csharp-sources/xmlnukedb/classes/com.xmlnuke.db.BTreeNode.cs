using System;
using System.Collections;

namespace com.xmlnuke.db
{
	public class BTreeNode : IBTreeNode
	{
		private string _key;
		private ArrayList _values;
		
		public BTreeNode(string key)
		{
			this._key = key;
			_values = new ArrayList();
		}

		public BTreeNode(string key, string value)
		{
			this._key = key.ToLower();
			_values = new ArrayList();
			_values.Add(value);
		}

		public bool lessThan(IBTreeNode bnode)
		{
			return (this.getKey().ToString().CompareTo(bnode.getKey().ToString()) < 0);
		}
		
		public bool greaterThan(IBTreeNode bnode)
		{
			return (this.getKey().ToString().CompareTo(bnode.getKey().ToString()) > 0);
		}
		
		public bool equalsTo(IBTreeNode bnode)
		{
			return ( String.Compare( this.getKey().ToString(), bnode.getKey().ToString(), true ) == 0 );
		}
		
		public object getKey()
		{
			return _key;
		}
		
		public ArrayList values()
		{
			return _values;
		}
		
	}
	
}
