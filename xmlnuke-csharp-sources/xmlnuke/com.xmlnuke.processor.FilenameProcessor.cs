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
using System.Collections.Specialized;
using com.xmlnuke.engine;
using com.xmlnuke.util;

namespace com.xmlnuke.processor
{

	public enum ForceFilenameLocation
	{
		UseWhereExists,
		PathFromRoot,
		SharedPath,
		PrivatePath,
		DefinePath
	}

	/// <summary>
	/// 
	/// </summary>
	#region XMLFilenameProcessor class
	public class XMLFilenameProcessor : FilenameProcessor
	{
		public XMLFilenameProcessor(string singlename, engine.Context context)
			: base(singlename, context)
		{
			// The function to manipulate HASHED XML files is in BTREEUTILS... 
			// So nothing to change HERE (instead XMLCacheFileName and XSLCacheFileName).
			this._filenameLocation = ForceFilenameLocation.PrivatePath;
		}

		public override string Extension()
		{
			return ".xml";
		}

		public override string SharedPath()
		{
			return this.PrivatePath();
		}

		public override string PrivatePath()
		{
			return _context.XmlPath;
		}

		public override string FullName(string xml, string xsl, string languageId)
		{
			return this.addLanguage(xml, languageId);
		}
	}
	#endregion

    #region AdminModulesXMLFilenameProcessor
    public class AdminModulesXMLFilenameProcessor : AnydatasetFilenameProcessor
    {
	    public AdminModulesXMLFilenameProcessor(Context context) : base("adminmodules", context)
	    {
		    this._filenameLocation = ForceFilenameLocation.SharedPath;
	    }

	    public override string SharedPath()
	    {
		    return this._context.SharedRootPath + "admin" + FileUtil.Slash();
	    }

	    /**
	     *@param
	     *@return string
	     *@desc Implementing
	     */
	    public override string Extension()
	    {
		    return ".config.xml";
	    }

	    public override string FullName(string xml, string xsl, string languageId)
	    {
		    return xml;
	    }
    }
    #endregion

	/// <summary>
	/// 
	/// </summary>
	#region XSLFilenameProcessor class
	public class XSLFilenameProcessor : FilenameProcessor
	{
		public XSLFilenameProcessor(string singlename, engine.Context context) : base(singlename, context) { }

		public override string Extension()
		{
			return ".xsl";
		}

		public override string SharedPath()
		{
			return _context.SharedRootPath + "xsl" + util.FileUtil.Slash();
		}

		public override string PrivatePath()
		{
			return _context.XslPath;
		}

		public override string FullName(string xml, string xsl, string languageId)
		{
			return this.addLanguage(xsl, languageId);
		}

		public override string FullName()
		{
			return this.FullName("", this._singlename, this._languageid);
		}
	}
	#endregion

	/// <summary>
	/// 
	/// </summary>
	#region XMLCacheFilenameProcessor class
	public class XMLCacheFilenameProcessor : FilenameProcessor
	{
		public XMLCacheFilenameProcessor(string singlename, engine.Context context)
			: base(singlename, context)
		{
			if (this._context.CacheHashedDir())
			{
				string complement = ((singlename.Length > 0) ? singlename[0] + util.FileUtil.Slash() + ((singlename.Length > 1) ? singlename[1] + util.FileUtil.Slash() : "") : "");
				this.PathForced = this._context.CachePath + complement;
				if (!System.IO.Directory.Exists(this.PathSuggested()))
				{
					System.IO.Directory.CreateDirectory(this.PathSuggested());
				}
			}
			else
			{
				this._filenameLocation = ForceFilenameLocation.PrivatePath;
			}
		}

		public override string Extension()
		{
			return ".cs.cache.html";
		}

		public override string SharedPath()
		{
			return this.PrivatePath();
		}

		public override string PrivatePath()
		{
			return _context.CachePath;
		}

		public override string FullName(string xml, string xsl, string languageId)
		{
			return xml.Replace(util.FileUtil.Slash(), "#") + "." + addLanguage(xsl, languageId);
		}
	}
	#endregion

	/// <summary>
	/// 
	/// </summary>
	#region XSLCacheFilenameProcessor class
	public class XSLCacheFilenameProcessor : FilenameProcessor
	{
		public XSLCacheFilenameProcessor(string singlename, engine.Context context)
			: base(singlename, context)
		{
			if (this._context.CacheHashedDir())
			{
				string complement = ((singlename.Length > 0) ? singlename[0] + util.FileUtil.Slash() + ((singlename.Length > 1) ? singlename[1] + util.FileUtil.Slash() : "") : "");
				this.PathForced = this._context.CachePath + complement;
				if (!System.IO.Directory.Exists(this.PathSuggested()))
				{
					System.IO.Directory.CreateDirectory(this.PathSuggested());
				}
			}
			else
			{
				this._filenameLocation = ForceFilenameLocation.PrivatePath;
			}
		}

		public override string Extension()
		{
			return ".cs.cache.xsl";
		}

		public override string SharedPath()
		{
			return this.PrivatePath();
		}

		public override string PrivatePath()
		{
			return _context.CachePath;
		}

		public override string FullName(string xml, string xsl, string languageId)
		{
			return this.addLanguage(xsl.Replace(util.FileUtil.Slash(), "#"), languageId);
		}
	}
	#endregion

	/// <summary>
	/// 
	/// </summary>
	#region OfflineFilenameProcessor class
	public class OfflineFilenameProcessor : FilenameProcessor
	{
		public OfflineFilenameProcessor(string singlename, engine.Context context)
			: base(singlename, context)
		{
			this._filenameLocation = ForceFilenameLocation.PrivatePath;
		}

		public override string Extension()
		{
			return ".offline.html";
		}

		public override string SharedPath()
		{
			return this.PrivatePath();
		}

		public override string PrivatePath()
		{
			return _context.OfflinePath;
		}

		public override string FullName(string xml, string xsl, string languageId)
		{
			return xml + "." + addLanguage(xsl, languageId);
		}
	}
	#endregion

	/// <summary>
	/// 
	/// </summary>
	#region Anydataset Related Classes
	/// <summary>
	/// 
	/// </summary>
	public abstract class AnydatasetBaseFilenameProcessor : FilenameProcessor
	{
		public AnydatasetBaseFilenameProcessor(string singlename, engine.Context context) : base(singlename, context) { }

		public override string Extension()
		{
			return ".anydata.xml";
		}

		public override string FullName(string xml, string xsl, string languageId)
		{
			return xml;
		}
	}

	/// <summary>
	/// 
	/// </summary>
	public class AnydatasetFilenameProcessor : AnydatasetBaseFilenameProcessor
	{
		public AnydatasetFilenameProcessor(string singlename, engine.Context context) : base(singlename, context) { }

		public override string SharedPath()
		{
			return _context.SharedRootPath + "anydataset" + util.FileUtil.Slash();
		}

		public override string PrivatePath()
		{
			return _context.CurrentSitePath + "anydataset" + util.FileUtil.Slash();
		}
	}

	/// <summary>
	/// 
	/// </summary>
	public class AnydatasetSetupFilenameProcessor : AnydatasetBaseFilenameProcessor
	{
		public AnydatasetSetupFilenameProcessor(string singlename, engine.Context context)
			: base(singlename, context)
		{
			this._filenameLocation = ForceFilenameLocation.SharedPath;
		}

		public override string SharedPath()
		{
			return _context.SharedRootPath + "setup" + util.FileUtil.Slash();
		}

		public override string PrivatePath()
		{
			return this.SharedPath();
		}
	}

	public class AnydatasetLangFilenameProcessor : AnydatasetBaseFilenameProcessor
	{
		public AnydatasetLangFilenameProcessor(string singlename, engine.Context context) : base(singlename, context) { }

		public override string SharedPath()
		{
			return _context.SharedRootPath + "lang" + util.FileUtil.Slash();
		}

		public override string PrivatePath()
		{
			return _context.CurrentSitePath + "lang" + util.FileUtil.Slash();
		}

		public override string Extension()
		{
			return ".lang" + base.Extension();
		}
	}

	#endregion

	/// <summary>
	/// 
	/// </summary>
	#region SnippetFilenameProcessor class
	public class SnippetFilenameProcessor : FilenameProcessor
	{
		public SnippetFilenameProcessor(string singlename, engine.Context context) : base(singlename, context) { }

		public override string Extension()
		{
			return ".inc";
		}

		public override string SharedPath()
		{
			return _context.SharedRootPath + "snippet" + util.FileUtil.Slash();
		}

		public override string PrivatePath()
		{
			return _context.CurrentSitePath + "snippet" + util.FileUtil.Slash();
		}

		public override string FullName(string xml, string xsl, string languageId)
		{
			return "snippet_" + xml;
		}
	}
	#endregion

	/// <summary>
	/// This class is used only for define a PATH for save Upload Files. 
	/// The single name is Just a PATH.
	/// </summary>
	#region UploadFilenameProcessor class
	public class UploadFilenameProcessor : FilenameProcessor
	{

		public UploadFilenameProcessor(string singlename, engine.Context context)
			: base(singlename, context)
		{
			this._filenameLocation = ForceFilenameLocation.PrivatePath;
		}

		public override string Extension()
		{
			return "";
		}

		public override string SharedPath()
		{
			return this._context.SystemRootPath();
		}

		public override string PrivatePath()
		{
			return _context.CurrentSitePath + util.FileUtil.Slash() + "upload" + util.FileUtil.Slash();
		}

		public override string FullName(string xml, string xsl, string languageId)
		{
			return xml;
		}
	}
	#endregion

	/// <summary>
	/// 
	/// </summary>
	#region AnydatasetBackupFilenameProcessor class
	public class AnydatasetBackupFilenameProcessor : AnydatasetBaseFilenameProcessor
	{
		/**
		 * Constructor Method
		 *
		 * @param string singlename
		 * @param Context context
		 */
		public AnydatasetBackupFilenameProcessor(string singlename, com.xmlnuke.engine.Context context)
			: base(singlename, context)
		{
			this._filenameLocation = ForceFilenameLocation.PrivatePath;
		}

		/**
		 * Shared Path
		 *
		 * @return string
		 */
		public override string SharedPath()
		{
			string path = this._context.SharedRootPath + "backup" + util.FileUtil.Slash() + "setup" + util.FileUtil.Slash();
			if (!System.IO.Directory.Exists(path))
				util.FileUtil.ForceDirectories(path);
			return path;
		}

		/**
		 * Private Path
		 *
		 * @return string
		 */
		public override string PrivatePath()
		{
			string path = this._context.CurrentSitePath + "backup" + util.FileUtil.Slash() + "setup" + util.FileUtil.Slash();
			if (!System.IO.Directory.Exists(path))
				util.FileUtil.ForceDirectories(path);
			return path;
		}

		public override string Extension()
		{
			return ".backup.xml";
		}
	}
	#endregion

	/// <summary>
	/// 
	/// </summary>
	#region AnydatasetBackupLogFilenameProcessor class
	public class AnydatasetBackupLogFilenameProcessor : AnydatasetBaseFilenameProcessor
	{
		/**
		 * Constructor Method
		 *
		 * @param string singlename
		 * @param Context context
		 */
		public AnydatasetBackupLogFilenameProcessor(string singlename, com.xmlnuke.engine.Context context)
			: base(singlename, context)
		{
			this._filenameLocation = ForceFilenameLocation.SharedPath;
		}

		/**
		 * Shared Path
		 *
		 * @return string
		 */
		public override string SharedPath()
		{
			return this._context.SharedRootPath + "setup" + util.FileUtil.Slash();
		}

		/**
		 * Private Path
		 *
		 * @return string
		 */
		public override string PrivatePath()
		{
			return this._context.CurrentSitePath + "setup" + util.FileUtil.Slash();
		}

		public override string Extension()
		{
			return ".log.xml";
		}
	}
	#endregion

    /// <summary>
    /// 
    /// </summary>
    #region AdminModulesLangFilenameProcessor
    public class AdminModulesLangFilenameProcessor : AnydatasetLangFilenameProcessor
    {
	    /**
	     *@param string $singlename
	     *@param Context $context
	     *@return void
	     *@desc
	     */
	    public AdminModulesLangFilenameProcessor(Context context) : base("adminmodules", context)
	    {
		    this._filenameLocation = ForceFilenameLocation.UseWhereExists;
	    }

	    /**
	     *@param
	     *@return string
	     *@desc
	     */
	    public override string SharedPath()
	    {
		    return this._context.SharedRootPath + "admin" + FileUtil.Slash();
	    }
    }
    #endregion

	/// <summary>
	/// 
	/// </summary>
	#region BackupFilenameProcessor class
	public class BackupFilenameProcessor : FilenameProcessor
	{
		/**
		*@param string singlename
		*@param Context context
		*@return void 
		*@desc 
		*/
		public BackupFilenameProcessor(string singlename, com.xmlnuke.engine.Context context)
			: base(singlename, context)
		{
			this._filenameLocation = ForceFilenameLocation.PrivatePath;
		}

		/**
		*@param 
		*@return string 
		*@desc 
		*/
		public override string Extension()
		{
			return ".csharp.xmlnuke.module";
		}

		/**
		*@param string xml
		*@param string xsl
		*@param string languageId
		*@return string 
		*@desc 
		*/
		public override string FullName(string xml, string xsl, string languageId)
		{
			return xml;
		}

		/**
		*@param 
		*@return string 
		*@desc 
		*/
		public override string SharedPath()
		{
			return this._context.SharedRootPath + "backup" + util.FileUtil.Slash();
		}

		/**
		*@param 
		*@return string 
		*@desc 
		*/
		public override string PrivatePath()
		{
			return this._context.CurrentSitePath + "backup" + util.FileUtil.Slash();
		}
	}
	#endregion

	/// <summary>
	/// FilenameProcessor is the class who process the Single argument filename (example: home or page) and get directory and localized informations about this file from FilenameType and XmlNukeContext.
	/// </summary>
	#region FilenameProcessor abstract class
	public abstract class FilenameProcessor
	{
		protected engine.Context _context = null;
		protected string _singlename = "";
		protected ForceFilenameLocation _filenameLocation = ForceFilenameLocation.UseWhereExists;
		protected string _languageid = null;
		protected string _path = "";

		/// <summary>
		/// FilenameProcessor constructor.
		/// </summary>
		/// <param name="singlename">The SingleName filename</param>
		/// <param name="context">The com.xmlnuke.engine.Context class</param>
		public FilenameProcessor(string singlename, engine.Context context)
		{
			if (!singlename.Contains(".."))
			{
				this._singlename = singlename;
				this._context = context;
				this._languageid = this._context.Language.Name.ToLower();
				this._filenameLocation = ForceFilenameLocation.UseWhereExists;
			}
			else
			{
				throw new Exception("Invalid file name");
			}
		}

		/// <summary>
		/// Return the SingleName
		/// </summary>
		/// <returns></returns>
		public override string ToString()
		{
			return _singlename;
		}

		/// <summary>
		/// Add the XmlNuke context language to the SingleName
		/// </summary>
		/// <param name="name">SingleName</param>
		/// <param name="languageId">Language Name (5 letter format)</param>
		/// <returns>Return the SingleName plus languageId</returns>
		protected string addLanguage(string name, string languageId)
		{
			return name + "." + languageId;
		}

		/// <summary>
		/// Remove language information from a filename
		/// </summary>
		/// <param name="name"></param>
		/// <returns></returns>
		public string removeLanguage(string name)
		{
			int i;
			i = name.IndexOf(this.Extension());
			if (i >= 0)
			{
				name = name.Substring(0, i);
			}
			i = name.LastIndexOf(".");
			if (i >= 0)
			{
				name = name.Substring(0, i);
			}
			return name;
		}

		/// <summary>
		/// Return the proper extension to the file. Uses the FilenameType enum.
		/// </summary>
		/// <returns>Extension</returns>
		public abstract string Extension();

		/// <summary>
		/// Return the Path Suggested from XMLNuke Context. This path uses the FilenameType enum.
		/// </summary>
		/// <returns>Return the Path</returns>
		public string PathSuggested()
		{
			string ret = this.PrivatePath();

			switch (this._filenameLocation)
			{
				case ForceFilenameLocation.PathFromRoot:
					{
						ret = _context.SharedRootPath;
						break;
					}
				case ForceFilenameLocation.UseWhereExists:
					{
						// Test:
						// - If File in PrivatePath exists (this is the first option!!)
						// - If file in SharedPath exists (this is the second option!!)
						// - Otherwise references 
						if (util.FileUtil.Exists(this.PrivatePath() + this.FullQualifiedName()))
						{
							ret = this.PrivatePath();
						}
						else if (util.FileUtil.Exists(this.SharedPath() + this.FullQualifiedName()))
						{
							ret = this.SharedPath();
						}
						else
						{
							ret = this.PrivatePath();
						}
						break;
					}
				case ForceFilenameLocation.PrivatePath:
					{
						ret = this.PrivatePath();
						break;
					}
				case ForceFilenameLocation.SharedPath:
					{
						ret = this.SharedPath();
						break;
					}
				case ForceFilenameLocation.DefinePath:
					{
						ret = this._path;
						break;
					}
			}
			return ret;
		}

		public abstract string SharedPath();

		public abstract string PrivatePath();

		/// <summary>
		/// Return the Path, FileName with LanguageID and Extension
		/// </summary>
		/// <returns>Full Path</returns>
		public string FullQualifiedNameAndPath()
		{
			return this.PathSuggested() + this.FullQualifiedName();
		}

		/// <summary>
		/// Return the FileName with LanguageID and Extension
		/// </summary>
		/// <returns>Full Name</returns>
		public virtual string FullQualifiedName()
		{
			return this.FullName() + this.Extension();
		}

		/// <summary>
		/// Return the FileName with specific single name, LanguageID and Extension
		/// </summary>
		/// <param name="xml">XML to be used</param>
		/// <param name="xsl">XSL to be used</param>
		/// <param name="languageId">LanguageID to be used</param>
		/// <returns>Full Name</returns>
		public abstract string FullName(string xml, string xsl, string languageId);

		/// <summary>
		/// Return the FileName with LanguageID from XMLNuke context
		/// </summary>
		/// <returns>Full name</returns>
		public virtual string FullName()
		{
			return this.FullName(this._singlename, this._context.Xsl, this._languageid);
		}

		/// <summary>
		/// Get or sets the property PathFromRoot. Affects the property PathSuggested. If true get the path from ROOTDIR config, otherwise get the suggested path. Default: FALSE.
		/// </summary>
		public ForceFilenameLocation FilenameLocation
		{
			get
			{
				return _filenameLocation;
			}
			set
			{
				_filenameLocation = value;
			}
		}

		public string PathForced
		{
			set
			{
				this._path = value;
				this._filenameLocation = ForceFilenameLocation.DefinePath;
			}
		}

		public engine.Context getContext()
		{
			return _context;
		}

		public string LanguageId
		{
			get
			{
				return _languageid;
			}
			set
			{
				_languageid = value.ToLower();
			}
		}


		public static string StripLanguageInfo(string filename)
		{
			int i = filename.LastIndexOf(".");
			filename = filename.Substring(0, i);
			i = filename.LastIndexOf(".");
			return filename.Substring(0, i);
		}

		public bool Exists()
		{
			return util.FileUtil.Exists(this.FullQualifiedNameAndPath());
		}

		public bool UseFileFromAnyLanguage()
		{
			if (!this.Exists())
			{
                NameValueCollection langAvail = this._context.LanguagesAvailable();
                if (langAvail["en-us"] == "")
                {
                    langAvail["en-us"] = "English (Default)";
                }

                foreach (string key in langAvail.Keys)
				{
					this.LanguageId = key;
					if (this.Exists())
					{
						break;
					}
				}
			}
			return this.Exists();
		}

	}
	#endregion
}