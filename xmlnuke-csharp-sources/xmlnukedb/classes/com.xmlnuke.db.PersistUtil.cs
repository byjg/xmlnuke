using System;
using System.Collections;
using System.Collections.Specialized;
using System.Xml;

namespace com.xmlnuke.db
{
	
	public class PersistUtil
	{
		
		private string _lang;
		private string _repositoryDir;

        protected bool _hashedDir = true;

        public bool HashedDir
        {
            get
            {
                return this._hashedDir;
            }
            set
            {
                this._hashedDir = value;
            }
        }

		/// <summary>
		/// Default constructor - initializes all fields to default values
		/// </summary>
		public PersistUtil(string repositoryDir, string lang) : this(repositoryDir, lang, false)
		{}
		
		public PersistUtil(string repositoryDir, string lang, bool createPath)
		{
			this._repositoryDir = repositoryDir;
			this._lang = lang;
			if (createPath)
			{
				System.IO.Directory.CreateDirectory(repositoryDir);
			}
		}

		public string getFullFileName(string documentName)
		{
            if (this.HashedDir)
            {
                return (this._repositoryDir +
                    PersistUtil.getSlash() + this._lang +
                    PersistUtil.getSlash() + documentName[0] +
                    PersistUtil.getSlash() + documentName[1] +
                    PersistUtil.getSlash() + documentName);
            }
            else
            {
                return (this._repositoryDir +
                    PersistUtil.getSlash() + this._lang +
                    PersistUtil.getSlash() + documentName);
            }
		}
		
		public bool existsDocument(string documentName)
		{
			return System.IO.File.Exists(this.getFullFileName(documentName));
		}
		
		public string getName(string document)
		{
			int i=document.IndexOf('#');
			if (i >= 0)
			{
				return document.Substring(0,i);
			}
			else
			{
				return document;
			}
		}
		
		public string getXPath(string document)
		{
			int i=document.IndexOf('#');
			if (i >= 0)
			{
				return document.Substring(i+1);
			}
			else
			{
				return "";
			}
		}
		
		public string getPathFromFile(string filename)
		{
			int i = filename.LastIndexOf(PersistUtil.getSlash());
			if (i >= 0)
			{
				return filename.Substring(0, i);
			}
			else
			{
				return filename;
			}
		}
		
		public string getNameFromFile(string filename, bool addLangSufix)
		{
			string result; 

			int i = filename.LastIndexOf(PersistUtil.getSlash());
			if (i >= 0)
			{
				result = filename.Substring(i+1);
			}
			else
			{
				result = filename;
			}
			if (addLangSufix)
			{
				i = result.LastIndexOf(".xml");
				if (i < 0)
				{
					result = result.Replace(".","_") + this._lang + ".xml";
				}
				else
				{
					int j = result.LastIndexOf("." + this._lang + ".xml");
					if (j < 0)
					{
						result = result.Substring(0, i).Replace(".","_") + "." + this._lang + ".xml";
					}
				}
			}

			return result;
		}

		public XmlDocument getDocument(string document, string rootNode)
		{
			string documentName = this.getName(document);
			string xpath = this.getXPath(document);
			
			if (!this.existsDocument(documentName))
			{
				throw new System.IO.FileNotFoundException("Document " + document + " doesn't exists in repository");
			}
			
			documentName = this.getFullFileName(documentName);
			
			XmlDocument doc = new XmlDocument();
			if (xpath == "")
			{
				doc.Load(documentName);
			}
			else
			{
				doc.LoadXml("<" + rootNode + " />");
				XmlDocument source = new XmlDocument();
				source.Load(documentName);
				XmlNodeList nodes = source.SelectNodes(xpath);
				foreach (XmlNode node in nodes)
				{
					XmlNode newNode = doc.ImportNode(node, true);
					doc.DocumentElement.AppendChild(newNode);
				}
			}
			
			return doc;
		}
		
		/*
		 *  This routine used only in RecreateIndex and didnt make sense have this name.
		 * /
		public BTree importDocuments(BTree btree)
		{
			return this.importDocuments(this._repositoryDir, btree, false);			
		}
		*/
		
		public BTree importDocuments(string directory, BTree btree, bool saveDocs)
		{
			return importDocuments(directory, btree, saveDocs, "*.xml", false);
		}

		public BTree importDocuments(string directory, BTree btree, bool saveDocs, string filemask, bool addLangSufix)
		{
			string[] files = System.IO.Directory.GetFiles(directory, filemask);
			
			XmlDocument xmldoc = new XmlDocument();
			xmldoc.XmlResolver = null;

			foreach(string file in files)
			{
	   	 		xmldoc.Load(file);
				if (saveDocs)
				{
					btree = this.saveDocument(this.getNameFromFile(file, addLangSufix), xmldoc, btree);
				}
				else
				{
					btree = BTreeUtil.navigateNodes(xmldoc.DocumentElement, this.getNameFromFile(file, false) + "#/", btree);
				}
			}
			
			string[] directories = System.IO.Directory.GetDirectories(directory);
			foreach (string dir in directories)
			{
				btree = this.importDocuments(dir, btree, saveDocs, filemask, addLangSufix);	
			}
			
			return btree;
			
		}

		public BTree saveDocument(string documentName, XmlDocument xml, BTree btree)
		{
			btree = BTreeUtil.navigateNodes(xml.DocumentElement, documentName + "#/", btree);
			
			documentName = this.getFullFileName(documentName);
			System.IO.Directory.CreateDirectory(this.getPathFromFile(documentName));
			
   			XmlTextWriter xmlWriter = new XmlTextWriter(documentName, null);
			try
			{
				xmlWriter.Formatting = Formatting.Indented;
				xmlWriter.IndentChar = '\t';
				xmlWriter.Indentation = 1;
				xml.Save(xmlWriter);
			}
			finally
			{
				xmlWriter.Close();
			}
			
			return btree;
		}

		public static char getSlash()
		{
			char slash;
			if (PersistUtil.isWindowsOS())
			{
				slash = '\\';
			}
			else
			{
				slash = '/';
			}
			return slash;
		}

		public static bool isWindowsOS()
		{
			return System.Environment.OSVersion.Platform.ToString().ToLower().IndexOf("win") >= 0;
		}

	}
}
