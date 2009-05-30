/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  CSharp Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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

using System;
using com.xmlnuke.processor;
using System.Collections.Specialized;

namespace com.xmlnuke.international
{
	/// <summary>
	/// LanguageCollection class create a NameValueCollection but only add elements from the current language context
	/// </summary>
	public class LanguageCollection
	{

		private engine.Context _context;
		private NameValueCollection _collection = new NameValueCollection();
		private bool _loadedFromFile = false;
		private string _debugInfo = "";

		/// <summary>
		/// LanguageCollection Constructor
		/// </summary>
		/// <param name="context"></param>
		public LanguageCollection(engine.Context context)
		{
			this._context = context;
		}

		/// <summary>
		/// Add text to specific language. At runtime will be add only the current language.
		/// </summary>
		/// <param name="lang">Language (five letters)</param>
		/// <param name="key">Key to find text</param>
		/// <param name="value">text</param>
		public void addText(string lang, string key, string value)
		{
			if (lang.ToLower() == _context.Language.Name.ToLower())
			{
				if (_collection[key] == null)
				{
					_collection.Add(key, value);
				}
				else
				{
					_collection[key] = value;
				}
			}
		}

		/// <summary>
		/// Get the text from key
		/// </summary>
		/// <param name="key">key</param>
		/// <returns>Text</returns>
		public string Value(string key)
		{
			string retword = _collection[key];
			if (retword == null)
			{
				retword = "[" + key + "?]";
			}
			else
			{
				retword = retword.Replace("\\n", "" + "\n");
			}
			return retword;
		}

		/// <summary>
		/// Get text from key and replace %s parameters
		/// </summary>
		/// <param name="key">Key</param>
		/// <param name="args">Array of string</param>
		/// <returns>Text</returns>
		public string Value(string key, object[] args)
		{
			/*
			 * This code uses {0}, {1}, {2}, parameters
			 * 
			System.Text.StringBuilder sb = new System.Text.StringBuilder();
			sb.AppendFormat(Value(key), args);
			return sb.ToString();
			*/
			string word = Value(key);
			/*
			int iParam = word.IndexOf("%s");
			int iCount = 0;
			while (iParam >= 0)
			{
				word = word.Substring(0, iParam) + args[iCount++] + word.Substring(iParam+2);
				iParam = word.IndexOf("%s");
			
			return word;
			*/
			return String.Format(word, args);
		}
		public string Value(string key, string args)
		{
			return this.Value(key, new object[] { args });
		}

		public void LoadLanguages(processor.AnydatasetBaseFilenameProcessor langFile)
		{
			bool isall = (langFile.ToString() == "_all");

			if (!isall)
			{
				this.LoadLanguages(new AnydatasetLangFilenameProcessor("_all", this._context));
			}

			this._loadedFromFile = false;

			int i = 0;
			while (i++ < 2)
			{
				if ((langFile.FilenameLocation == ForceFilenameLocation.UseWhereExists) || (langFile.FilenameLocation == ForceFilenameLocation.PrivatePath) || (langFile.FilenameLocation == ForceFilenameLocation.SharedPath))
				{
					if (i == 1)
					{
						langFile.FilenameLocation = ForceFilenameLocation.SharedPath;
					}
					else
					{
						langFile.FilenameLocation = ForceFilenameLocation.PrivatePath;
					}
				}
				else
				{
					i = 2;  // Force exit from loop at the end. 
				}

				this._debugInfo += langFile.ToString() + " in " + langFile.FilenameLocation.ToString() + "(\"" + langFile.FullQualifiedNameAndPath() + "\") ";
				if (!langFile.Exists())
				{
					this._debugInfo += "Does not exists; ";
					continue;
				}
				this._debugInfo += "Exists; ";

				string curLang = _context.Language.Name.ToLower();
				NameValueCollection avail = _context.LanguagesAvailable();

				anydataset.AnyDataSet lang = new anydataset.AnyDataSet(langFile);
				anydataset.IteratorFilter itf = new anydataset.IteratorFilter();
				itf.addRelation("LANGUAGE", anydataset.Relation.Equal, curLang);
				anydataset.Iterator it = lang.getIterator(itf);

				bool readIt = it.hasNext();
				if ((!readIt) && (curLang != avail.Keys[0]))
				{
					itf = new anydataset.IteratorFilter();
					itf.addRelation("LANGUAGE", anydataset.Relation.Equal, avail.Keys[0]);
					it = lang.getIterator(itf);
					readIt = it.hasNext();
				}

				if (readIt)
				{
					anydataset.SingleRow sr = it.moveNext();
					string[] names = sr.getFieldNames();
					for (int j = 0; j < names.Length; j++)
					{
						this.addText(curLang, names[j], sr.getField(names[j]));
					}
					this._loadedFromFile = true;
				}
			}
		}

		public void SaveLanguages(processor.AnydatasetBaseFilenameProcessor langFile)
		{
		}

		public bool loadedFromFile()
		{
			return _loadedFromFile;
		}

		public void Debug()
		{
			util.Debug.Print(this._debugInfo);
			util.Debug.Print(this._collection);
		}

	}
}