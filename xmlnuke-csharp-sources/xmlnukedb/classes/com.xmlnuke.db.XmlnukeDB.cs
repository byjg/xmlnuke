using System;
using System.Collections;
using System.Collections.Specialized;
using System.Xml;

namespace com.xmlnuke.db
{
	
	
	/// <summary>
	/// TODO - Add class summary
	/// </summary>
	/// <remarks>
	/// 	created by - Administrator
	/// 	created on - 07/12/2003 17:42:08
	/// </remarks>
	public class XmlnukeDB  {
		
		private BTree _btree;
		private string _btreeDir;
		private string _repositoryDir;
		private PersistUtil _persistUtil;

		/// <summary>
		/// Default constructor - initializes all fields to default values
		/// </summary>
		public XmlnukeDB(bool hashedDir, string repositoryDir, string lang) : this(hashedDir, repositoryDir, lang, false)
		{}
		
		public XmlnukeDB(bool hashedDir, string repositoryDir, string lang, bool createPath)
		{
			this._persistUtil = new PersistUtil(repositoryDir, lang, createPath);
            this._persistUtil.HashedDir = hashedDir;
			this._btree = null;
			this._repositoryDir = repositoryDir + PersistUtil.getSlash() + lang;
			this._btreeDir =  this._repositoryDir + PersistUtil.getSlash() + "index.cs.btree";
			if ((!System.IO.File.Exists(this._btreeDir)) && (!createPath))
			{
				throw new System.IO.DirectoryNotFoundException("The specified repository '" + this._btreeDir + "' does not exist or is invalid because could not find the index file");
			}
		}

		public BTree btree
		{
			get
			{
				return this._btree;
			}
			set
			{
				this._btree = value;
			}
		}
		
		public XmlDocument getDocument(string document)
		{
			return getDocument(document, "page"); // Somente se houver XPath
		}
		
		public XmlDocument getDocument(string document, string rootNode)
		{
			return this._persistUtil.getDocument(document, rootNode);
		}
		
		public ArrayList searchDocuments(string words, bool includeAllDocs)
		{
			if (this._btree == null)
			{
				throw new Exception("You must have a valid BTree. Try recreateIndex.");
			}
			else
			{
		    	ArrayList arr = BTreeUtil.searchDocuments(words.Trim(), this._btree, includeAllDocs);
				return arr;
			}
		}
		
		public void saveDocument(string documentName, System.IO.Stream stream)
		{
			XmlDocument xml = new XmlDocument();
			xml.Load(stream);
			this.saveDocument(documentName, xml);
		}
		
		public void saveDocument(string documentName, string xmlstr)
		{
			XmlDocument xml = new XmlDocument();
			xml.LoadXml(xmlstr);
			this.saveDocument(documentName, xml);
		}
		
		public void saveDocument(string documentName, XmlDocument xml)
		{
			this._btree = this._persistUtil.saveDocument(documentName, xml, this._btree);
		}
		
		public void importDocuments(string directory)
		{
			this._btree = this._persistUtil.importDocuments(directory, this._btree, true);
		}
		
		public void importDocuments(string directory, string filemask, bool addLangSufix)
		{
			this._btree = this._persistUtil.importDocuments(directory, this._btree, true, filemask, addLangSufix);
		}
		
		public void loadIndex()
		{
			this._btree = BTree.load(this._btreeDir);
		}

		public void saveIndex()
		{
			BTree.save(this._btree, this._btreeDir);
		}

		public void recreateIndex()
		{
			this._btree = null;
			this._btree = this._persistUtil.importDocuments(this._repositoryDir, this._btree, false);	
			/*
			 * Used if recreate index of ALL LANGUAGES
			 * /
			string[] directories = System.IO.Directory.GetDirectories(this._repositoryDir);
			foreach (string langDir in directories)
			{
				this._btree = this.importDocuments(langDir, this._btree, false);	
			}
			*/
			//this._btree = this._persistUtil.importDocuments(this._btree);
			//this._btree = this._persistUtil.importDocuments(this._persistUtil.
		}
		
		public bool existsDocument(string documentName)
		{
			return this._persistUtil.existsDocument(documentName);
		}
	}
}
