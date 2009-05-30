using System;
using System.Collections;

namespace com.xmlnuke.db
{
	public interface IBTreeNode
	{
		bool lessThan(IBTreeNode bnode);
		bool greaterThan(IBTreeNode bnode);
		bool equalsTo(IBTreeNode bnode);
		object getKey();
		ArrayList values();
	}
}
