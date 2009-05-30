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

namespace com.xmlnuke.util
{
	/// <summary>
	/// Utility file functions
	/// </summary>
	public class FileUtil
	{
		/// <summary>
		/// Doens't need constructor because all methods are static.
		/// </summary>
		public FileUtil() { }

		/// <summary>
		/// Check that file exists.
		/// </summary>
		/// <param name="filename">Filename</param>
		/// <returns>True if exists</returns>
		public static bool Exists(string filename)
		{
			return System.IO.File.Exists(filename);
		}
		public static bool Exists(processor.FilenameProcessor filename)
		{
			return System.IO.File.Exists(filename.FullQualifiedNameAndPath());
		}

		/// <summary>
		/// Retrive an array from specified folder
		/// </summary>
		/// <param name="folder">Folder</param>
		/// <returns>Array of subfolders</returns>
		public static string[] RetrieveSubFolders(string folder)
		{
			string[] aux = System.IO.Directory.GetDirectories(folder);
			System.Collections.Specialized.StringCollection resultAux = new System.Collections.Specialized.StringCollection();

			for (int i = 0; i < aux.Length; i++)
			{
				if ((aux[i].IndexOf("CVS") < 0) && (aux[i].IndexOf(".svn") < 0))
				{
					resultAux.Add(aux[i]);
				}
			}

			string[] result = new string[resultAux.Count];
			resultAux.CopyTo(result, 0);

			// Bug Fixes on Mono 1.15 (CopyTo) The code may commented
			//			string[] result = System.IO.Directory.GetDirectories(folder);

			return result;
		}

		public static string[] RetrieveFilesFromFolder(string folder, string pattern)
		{
			string[] result = System.IO.Directory.GetFiles(folder, pattern);
			return result;
		}

		/// <summary>
		/// Extract path from specified filename
		/// </summary>
		/// <param name="FullFileName">Filename</param>
		/// <returns>Path</returns>
		public static string ExtractFilePath(string FullFileName)
		{
			return FullFileName.Substring(0, FullFileName.LastIndexOf(FileUtil.Slash()));
		}

		/// <summary>
		/// Extract path from specified filename
		/// </summary>
		/// <param name="FullFileName">Filename</param>
		/// <returns>Filename</returns>
		public static string ExtractFileName(string FullFileName)
		{
			return FullFileName.Substring(FullFileName.LastIndexOf(FileUtil.Slash()) + 1);
		}

		/// <summary>
		/// Read text file from disk and return string
		/// </summary>
		/// <param name="filename">Filename</param>
		/// <returns>String</returns>
		public static string QuickFileRead(string filename)
		{
			System.IO.FileStream fStream = new System.IO.FileStream(filename, System.IO.FileMode.Open, System.IO.FileAccess.Read, System.IO.FileShare.Read);
			System.IO.StreamReader sRead = new System.IO.StreamReader(fStream);
			try
			{
				string result = sRead.ReadToEnd();
				return result;
			}
			finally
			{
				sRead.Close();
			}
		}

		/// <summary>
		/// Save a string to disk
		/// </summary>
		/// <param name="filename">Filename</param>
		/// <param name="content">String content</param>
		public static void QuickFileWrite(string filename, string content)
		{
			System.IO.FileStream fStream = new System.IO.FileStream(filename, System.IO.FileMode.Create);
			System.IO.StreamWriter sWrite = new System.IO.StreamWriter(fStream);
			try
			{
				sWrite.Write(content);
			}
			finally
			{
				sWrite.Close();
			}
		}

		/// <summary>
		/// Delete a file from disk
		/// </summary>
		/// <param name="file">FilenameProcessor</param>
		public static void DeleteFile(processor.FilenameProcessor file)
		{
			System.IO.File.Delete(file.FullQualifiedNameAndPath());
		}

		/// <summary>
		/// Delete a file from disk
		/// </summary>
		/// <param name="file">Filename and path</param>
		public static void DeleteFile(string file)
		{
			System.IO.File.Delete(file);
		}

		/// <summary>
		/// Delete all files from folder and specified extension from disk
		/// </summary>
		/// <param name="file">FilenameProcessor to extract extension</param>
		public static void DeleteFilesFromPath(processor.FilenameProcessor file)
		{
			string[] files = System.IO.Directory.GetFiles(file.PathSuggested());
			foreach (string f in files)
			{
				if (f.IndexOf(file.Extension()) >= 0)
				{
					FileUtil.DeleteFile(f);
				}
			}
		}

		/// <summary>
		/// Rename a Directory
		/// </summary>
		/// <param name="oldDirName">The directory you want rename</param>
		/// <param name="newDirName">The new name</param>
		public static void RenameDirectory(string oldDirName, string newDirName)
		{
			//try
			//{
			System.IO.DirectoryInfo dirInfo = new System.IO.DirectoryInfo(oldDirName);
			dirInfo.Create();
			dirInfo.MoveTo(newDirName);
			//}
			//catch(IOException ioe) 
			//{
			//    // most likely given the directory exists or isn't empty
			//    Console.WriteLine(ioe.ToString());
			//}
			//catch(Exception e) 
			//{
			//    // catch any other exceptions
			//    Console.WriteLine(e.ToString());
			//} 
		}

		/// <summary>
		/// Rename a File
		/// </summary>
		/// <param name="oldName">The file you want rename</param>
		/// <param name="newName">The new name</param>
		public static void RenameFile(string oldName, string newName)
		{
			System.IO.File.Move(oldName, newName);
		}


		/// <summary>
		/// Create all subdirectories and directories specified by pathname
		/// Wrapper for System.IO.Directory.CreateDirectory.
		/// Compatibility only with PHP Engine.
		/// </summary>
		/// <param name="pathname">Full path</param>
		public static void ForceDirectories(string pathname)
		{
			System.IO.Directory.CreateDirectory(pathname);
		}

		/// <summary>
		/// Remove the directory specified by basedir and all subdirectories.
		/// </summary>
		/// <param name="basedir"></param>
		public static void ForceRemoveDirectories(string basedir)
		{
			string[] dirs = System.IO.Directory.GetDirectories(basedir);
			foreach (string dir in dirs)
			{
				FileUtil.ForceRemoveDirectories(dir);
			}
			string[] files = System.IO.Directory.GetFiles(basedir);
			foreach (string file in files)
			{
				FileUtil.DeleteFile(file);
			}
			System.IO.Directory.Delete(basedir);
		}


		/// <summary>
		/// Open a remote document from a specific URL
		/// </summary>
		/// <param name="url">The location of remote document</param>
		/// <returns>Stream pointing to remote document</returns>
		public static System.IO.Stream OpenRemoteDocument(string url)
		{
			// Expression Regular:
			// [2]: http or ftp (s0)
			// [4]: Server name (a0)
			// [5]: Full Path   (p0)
			// [6]: Query       (q1)
			//string http=@"^(?<s1>(?<s0>[^:/\?#]+):)?(?<a1>" 
			//      + @"//(?<a0>[^/\?#]*))?(?<p0>[^\?#]*)" 
			//      + @"(?<q1>\?(?<q0>[^#]*))?" 
			//      + @"(?<f1>#(?<f0>.*))?";
			//Regex pat = new Regex(http);
			//string[] urlParts = pat.Split(this._source);

			System.Net.WebClient webclient = new System.Net.WebClient();

			System.IO.Stream stream = webclient.OpenRead(url);

			return stream;
		}


		/// <summary>
		/// Get the full document opened from OpenRemoteDocument
		/// </summary>
		/// <param name="stream">Stream from OpenRemoteDocument</param>
		/// <returns>Document readed</returns>
		public static string ReadRemoteDocument(System.IO.Stream stream, engine.Context context)
		{
			System.IO.StreamReader readStream = new System.IO.StreamReader(stream);

			string retdocument = "";

			retdocument = readStream.ReadToEnd();
			//context.Debug(retdocument);
			readStream.Close();

			return retdocument;
		}

		/// <summary>
		/// Get a remote document and transform to XmlDocument
		/// </summary>
		/// <param name="url">The location of remote document</param>
		/// <returns>The dom XmlDocument</returns>
		public static System.Xml.XmlDocument GetRemoteXMLDocument(string url, engine.Context context)
		{
			System.IO.Stream stream = FileUtil.OpenRemoteDocument(url);
			string xmlString = FileUtil.ReadRemoteDocument(stream, context);

			if ((xmlString.Trim() == "") || (xmlString == null))
			{
				//context.Debug("Error Reading url: " + url);
				return XmlUtil.CreateXmlDocumentFromStr("<error>Error reading url: " + url + "</error>");
			}
			else
			{
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&(quot|#34);", "\"");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&(amp|#38);", "&");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&(nbsp|#160);", " ");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&aacute;", "á");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&eacute;", "é");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&iacute;", "í");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&oacute;", "ó");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&uacute;", "ú");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&atilde;", "ã");
				xmlString = System.Text.RegularExpressions.Regex.Replace(xmlString, "&ccedil;", "ç");

				System.Xml.XmlDocument xmldoc = XmlUtil.CreateXmlDocumentFromStr(xmlString);
				return xmldoc;
			}
		}

		/// <summary>
		/// Get the Slash based on Operational System. 
		/// </summary>
		/// <returns>Back Slash in Windows and Slash in *nix systems.</returns>
		public static string Slash()
		{
			return isWindowsOS() ? "\\" : "/";
		}

		/// <summary>
		/// Adjusts slashs in a specific path based on Operation System.
		/// </summary>
		/// <param name="path"></param>
		/// <returns>Path with back slashes in Windows and Path with Slashes in *nix systems</returns>
		public static string AdjustSlashes(string path)
		{
			string search, replace;
			if (isWindowsOS())
			{
				search = "/";
				replace = "\\";
			}
			else
			{
				search = "\\";
				replace = "/";
			}
			return path.Replace(search, replace);
		}

		/// <summary>
		/// Get Uri object from a single string path. Plataform Independent.
		/// </summary>
		/// <param name="absolutepath"></param>
		/// <returns>The URI object</returns>
		public static Uri getUriFromFile(string absolutepath)
		{
			string result = "";
			if (isWindowsOS())
			{
				result = "file:///" + absolutepath;
				result = result.Replace("\\", "/");
			}
			else
			{
				result = "file://" + absolutepath;
			}
			return new System.Uri(result);
		}

		/// <summary>
		/// Get the current .NET plataform.
		/// </summary>
		/// <returns>True if runs on any Windows plataform, false *nix plataforms.</returns>
		public static bool isWindowsOS()
		{
			return System.Environment.OSVersion.Platform.ToString().ToLower().IndexOf("win") >= 0;
		}

		protected static void prepareHeader(string mimeType, string downloadName)
		{
			System.Web.HttpContext.Current.Response.Clear();
			System.Web.HttpContext.Current.Response.AddHeader("Content-Disposition", "inline; filename=\"" + downloadName + "\"");
			System.Web.HttpContext.Current.Response.AddHeader("Content-Type", mimeType + "; name=\"" + downloadName + "\"");
		}

		public static void ResponseCustomContent(string mimeType, string content)
		{
			FileUtil.ResponseCustomContent(mimeType, content, "download.ext");
		}
		public static void ResponseCustomContent(string mimeType, string content, string downloadName)
		{
			FileUtil.prepareHeader(mimeType, downloadName);
			System.Web.HttpContext.Current.Response.Write(content);
			System.Web.HttpContext.Current.ApplicationInstance.CompleteRequest();
		}

		public static void ResponseCustomContent(string mimeType, char[] content)
		{
			FileUtil.ResponseCustomContent(mimeType, content, "download.ext");
		}
		public static void ResponseCustomContent(string mimeType, char[] content, string downloadName)
		{
			FileUtil.prepareHeader(mimeType, downloadName);
			System.Web.HttpContext.Current.Response.Write(content);
			System.Web.HttpContext.Current.ApplicationInstance.CompleteRequest();
		}

		public static void ResponseCustomContentFromFile(string mimeType, string filename)
		{
			FileUtil.ResponseCustomContentFromFile(mimeType, filename, null);
		}

		public static void ResponseCustomContentFromFile(string mimeType, string filename, string downloadName)
		{
			if (String.IsNullOrEmpty(downloadName))
			{
				downloadName = filename;
			}
			downloadName = util.FileUtil.ExtractFileName(downloadName);

			if (!util.FileUtil.Exists(filename))
			{
				System.Web.HttpContext.Current.Response.Clear();
				System.Web.HttpContext.Current.Response.Status = "404 Not Found";
				System.Web.HttpContext.Current.Response.StatusCode = 404;
				System.Web.HttpContext.Current.Response.StatusDescription = "HTTP/1.1 404 Not Found";
				System.Web.HttpContext.Current.Response.Write("<h1>404 Not Found</h1>");
				System.Web.HttpContext.Current.Response.Write("Filename " + downloadName + " not found!<br>");
			}
			else
			{
				FileUtil.prepareHeader(mimeType, downloadName);
				System.Web.HttpContext.Current.Response.WriteFile(filename);
			}
			System.Web.HttpContext.Current.ApplicationInstance.CompleteRequest();
			System.Web.HttpContext.Current.Response.End();
		}
	}
}