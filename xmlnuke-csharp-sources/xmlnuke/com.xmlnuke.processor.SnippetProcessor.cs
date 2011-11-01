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
using System.Xml;
using System.IO;

namespace com.xmlnuke.processor
{
	/// <summary>
	/// This class is a XmlUrlResolver descendant. It process the XSL file and add the proper snippets. If this XSL file already process use from cache.
	/// </summary>
	class SnippetProcessor : XmlUrlResolver
	{
		public static System.Collections.Hashtable myHash = new System.Collections.Hashtable();
		private engine.Context _context;
		private processor.XSLFilenameProcessor _file;

		/// <summary>
		/// SnippetProcessor constructor
		/// </summary>
		/// <param name="context">XmlNuke context</param>
		/// <param name="file">XSL file to be processed</param>
		public SnippetProcessor(engine.Context context, processor.XSLFilenameProcessor file)
		{
			this._context = context;
			this._file = file;
		}

		protected string _fileCacheName = "";
		protected Stream _fileCacheStream = null;
		protected StreamWriter _fileCacheWriter = null;
		
		protected void OpenCache(string filename)
		{
			this._fileCacheName = filename;
			this._fileCacheStream = null;
			this._fileCacheWriter = null;
			
			if (!this._context.NoCache)
			{
				try
				{
					this._fileCacheStream = new System.IO.FileStream(this._fileCacheName, System.IO.FileMode.Create);
				}
				catch
				{
					System.Web.HttpContext.Current.Response.Write("<br/><b>Warning:</b> I could not write to cache on file '" + this._fileCacheName + "'. Switching to nocache=true. <br/>");
					this._context.NoCache = true;
				}
			}
			//else
			//{
			//	System.Web.HttpContext.Current.Response.Write("No Cache");
			//}

			if (this._fileCacheStream == null)
			{
				this._fileCacheStream = new System.IO.MemoryStream();
			}
			this._fileCacheWriter = new System.IO.StreamWriter(this._fileCacheStream, System.Text.UTF8Encoding.UTF8);
		}

		protected void WriteToCache(string content)
		{
			this._fileCacheWriter.Write(content);
		}

		protected Stream CloseCache()
		{
			if (this._fileCacheStream is MemoryStream)
			{
				this._fileCacheWriter.Flush();
				((System.IO.MemoryStream)this._fileCacheStream).Seek(0, System.IO.SeekOrigin.Begin);
				return this._fileCacheStream;
			}
			else
			{
				this._fileCacheWriter.Close();
				this._fileCacheStream.Close();
				return new System.IO.FileStream(this._fileCacheName, System.IO.FileMode.Open, System.IO.FileAccess.Read, System.IO.FileShare.Read);
			}
		}
	

		/// <summary>
		/// Return the XSL with snippet to/from cache.
		/// </summary>
		/// <param name="absoluteUri"></param>
		/// <param name="role"></param>
		/// <param name="ofObjectToReturn"></param>
		/// <returns></returns>
		override public object GetEntity(Uri absoluteUri, string role, Type ofObjectToReturn)
		{
			processor.XSLCacheFilenameProcessor xslCache = new processor.XSLCacheFilenameProcessor(this._file.ToString(), this._context);
			Stream processedDoc;

			// Create a new stream representing the file to be written to,
			// and write the stream cache the stream
			// from the external location to the file (only if doesnt exist)
			if (!util.FileUtil.Exists(xslCache.FullQualifiedNameAndPath()) || this._context.NoCache || this._context.Reset)
			{

				System.IO.StreamReader sRead = new System.IO.StreamReader((System.IO.Stream)base.GetEntity(absoluteUri, role, ofObjectToReturn), System.Text.Encoding.UTF8);
				try
				{
					this.OpenCache(xslCache.FullQualifiedNameAndPath());
					try
					{
						string line = sRead.ReadLine();
						int iStart;
						int iEnd;
						while (line != null)
						{
							iStart = line.IndexOf("<xmlnuke-");
							while (iStart >= 0)
							{
								iEnd = line.IndexOf(">", iStart + 1);
								string snippetFile = line.Substring(iStart + 9, iEnd - iStart - 10);
								processor.SnippetFilenameProcessor snippet = new com.xmlnuke.processor.SnippetFilenameProcessor(snippetFile.Trim(), this._context);
								System.IO.FileStream fStreamSnippet = new System.IO.FileStream(snippet.FullQualifiedNameAndPath(), System.IO.FileMode.Open, System.IO.FileAccess.Read, System.IO.FileShare.Read);
								System.IO.StreamReader sReadSnippet = new System.IO.StreamReader(fStreamSnippet);
								try
								{
									line = line.Substring(0, iStart) + "\r\n" + sReadSnippet.ReadToEnd() + line.Substring(iEnd + 1);
								}
								finally
								{
									sReadSnippet.Close();
									fStreamSnippet.Close();
								}
								iStart = line.IndexOf("<xmlnuke-");
							}
							this.WriteToCache(line);
							line = sRead.ReadLine();
						}
					}
					finally
					{
						// Close Writing to cache and send back the results to XslSnippet
						processedDoc = this.CloseCache();
					}
					return processedDoc;
				}
				catch (Exception ex)
				{
					if (util.FileUtil.Exists(xslCache.FullQualifiedNameAndPath()))
					{
						com.xmlnuke.util.FileUtil.DeleteFile(xslCache);
					}
					throw ex;
				}
				finally
				{
					sRead.Close();
				}
			}
			return new System.IO.FileStream(xslCache.FullQualifiedNameAndPath(), System.IO.FileMode.Open, System.IO.FileAccess.Read, System.IO.FileShare.Read);

		}

		public static Uri getUriFromXsl(processor.XSLFilenameProcessor xslFile, engine.Context context)
		{
			//System.Uri uri = snippetProcessor.ResolveUri(null, xslFile.FullQualifiedNameAndPath());
			if (!xslFile.Exists()) // Try internally if XSL exists in Private and Shared directory
			{
				if (!xslFile.UseFileFromAnyLanguage())
				{
					throw new System.IO.FileNotFoundException("XSL document \"" + xslFile.FullQualifiedName() + "\" not found in local site or shared location.");
				}
			}
			return util.FileUtil.getUriFromFile(xslFile.FullQualifiedNameAndPath());
		}
	}

}