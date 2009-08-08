/*
** Authored by Timothy Gerard Endres
** <mailto:time@gjt.org>  <http://www.trustice.com>
**
 * MODIFIED By João Gilberto Magalhaes
 * for Xmlnuke Project (switch from Console to a Class)
 * http://www.xmlnuke.com
 * 
** This work has been placed into the public domain.
** You may use this work in any way and for any purpose you wish.
**
** THIS SOFTWARE IS PROVIDED AS-IS WITHOUT WARRANTY OF ANY KIND,
** NOT EVEN THE IMPLIED WARRANTY OF MERCHANTABILITY. THE AUTHOR
** OF THIS SOFTWARE, ASSUMES _NO_ RESPONSIBILITY FOR ANY
** CONSEQUENCE RESULTING FROM THE USE, MODIFICATION, OR
** REDISTRIBUTION OF THIS SOFTWARE.
**
*/

using System;
using System.IO;
using System.Collections;

using ICSharpCode.SharpZipLib;
using ICSharpCode.SharpZipLib.Zip.Compression.Streams;
using ICSharpCode.SharpZipLib.GZip;
using ICSharpCode.SharpZipLib.BZip2;
using ICSharpCode.SharpZipLib.Tar;

namespace com.xmlnuke.classes
{
    /// <summary>
    /// The tar class implements a simplistic version of the
    /// traditional UNIX tar command. It currently supports
    /// creating, listing, and extracting from archives. 
    /// It supports GZIP, unix compress and bzip2 compression
    /// GNU long filename extensions are supported, POSIX extensions are not yet supported...
    /// </summary>
    public class Tar
    {
	    /// <summary>
	    /// The compresion to use when creating archives.
	    /// </summary>
	    public enum Compression
	    {
		    None,
		    Compress,
		    Gzip,
		    Bzip2
	    }

	    /// <summary>
	    /// Operation to perform on archive
	    /// </summary>
	    public enum Operation
	    {
		    List,
		    Create,
		    Extract
	    }

	    #region Instance Fields
	    /// <summary>
	    /// Flag that determines if verbose feedback is to be provided.
	    /// </summary>
	    //bool verbose;
    	
	    /// <summary>
	    /// What kind of <see cref="Compression"/> to use.
	    /// </summary>
	    Compression compression = Compression.None;

	    /// <summary>
	    /// The <see cref="Operation"/> to perform.
	    /// </summary>
	    Operation operation = Operation.List;

	    /// <summary>
	    /// True if we are not to overwrite existing files.  (Unix noKlobber option)
	    /// </summary>
	    bool keepOldFiles;
    	
	    /// <summary>
	    /// True if we are to convert ASCII text files from local line endings
	    /// to the UNIX standard '\n'.
	    /// </summary>
	    bool asciiTranslate;
    	
	    /// <summary>
	    /// The archive name provided on the command line, '-' if stdio.
	    /// </summary>
	    string archiveName;
    	
	    /// <summary>
	    /// The blocking factor to use for the tar archive IO. Set by the '-b' option.
	    /// </summary>
	    int blockingFactor;
    	
	    /// <summary>
	    /// The userId to use for files written to archives. Set by '-U' option.
	    /// </summary>
	    int userId;
    	
	    /// <summary>
	    /// The userName to use for files written to archives. Set by '-u' option.
	    /// </summary>
	    string userName;
    	
	    /// <summary>
	    /// The groupId to use for files written to archives. Set by '-G' option.
	    /// </summary>
	    int groupId;
    	
	    /// <summary>
	    /// The groupName to use for files written to archives. Set by '-g' option.
	    /// </summary>
	    string groupName;

        /// <sumary>
        /// 
        /// </sumary>
        ArrayList _files = new ArrayList();
        public void AddFile(string file)
        {
            this._files.Add(file);
        }
        public void AddFile(ICollection file)
        {
            this._files.AddRange(file);
        }

        protected ArrayList _errors = new ArrayList();
        protected ArrayList _result = new ArrayList();
        protected ArrayList _filesTar = null;
        protected ArrayList _direcsTar = null;

        protected string _rootPath = null;
        #endregion

	    /// <summary>
	    /// Initialise default instance of <see cref="Tar"/>.
	    /// Sets up the default userName with the system 'UserName' property.
	    /// </summary>
	    public Tar(string tarName, Compression compress)
	    {
		    this.blockingFactor = TarBuffer.DefaultBlockFactor;
		    this.userId   = 0;
    		
		    string sysUserName = Environment.UserName;
		    this.userName = ((sysUserName == null) ? "" : sysUserName);
    		
		    this.groupId   = 0;
		    this.groupName = "None";

            this.compression = compress;
            this.archiveName = tarName;
	    }

        public Tar(string tarName) : this(tarName, Compression.Gzip)
        { }


        public void create(string path)
        {
            this._errors.Clear();
            this._result.Clear();
            this._filesTar = null;
            this._direcsTar = null;
            this._rootPath = path;
            this.operation = Operation.Create;
            this.InstanceMain();
        }

        public void create()
        {
            this.create(null);
        }

        public void extract(string path)
        {
            this._errors.Clear();
            this._result.Clear();
            this._filesTar = null;
            this._direcsTar = null;
            this._rootPath = path;
            this.operation = Operation.Extract;
            this.InstanceMain();
        }

        public void extract()
        {
            this.extract(null);
        }

        public void list()
        {
            this._errors.Clear();
            this._result.Clear();
            this._filesTar = null;
            this._direcsTar = null;
            this.operation = Operation.List;
            this.InstanceMain();
        }

        public ArrayList Errors()
        {
            return this._errors;
        }

        public ArrayList Messages()
        {
            return this._result;
        }

        public ArrayList getFiles()
        {
            if (this._filesTar == null)
            {
                this.list();
            }
            return this._filesTar;
        }
        public ArrayList getDirectories()
        {
            if (this._direcsTar == null)
            {
                this.list();
            }
            return this._direcsTar;
        }

        public void listFiles(XmlBlockCollection paragraph)
        {
            if (this._result.Count >= 0)
            {
                paragraph.addXmlnukeObject(new XmlnukeText("Tar File: ", true));
                paragraph.addXmlnukeObject(new XmlnukeText(" " + this.archiveName));
                paragraph.addXmlnukeObject(new XmlnukeBreakLine());
                paragraph.addXmlnukeObject(new XmlnukeBreakLine());

                paragraph.addXmlnukeObject(new XmlnukeText("*** Message in Tar Backup ***", true, false, false, true));

                //

                for (int i = 0; i < this._result.Count; i++)
                {
                    paragraph.addXmlnukeObject(new XmlnukeText("Filename: ", true));
                    paragraph.addXmlnukeObject(new XmlnukeText(this._result[i].ToString()));
                    paragraph.addXmlnukeObject(new XmlnukeBreakLine());
                }

                string text = "*** End of Messages ***";
                paragraph.addXmlnukeObject(new XmlnukeText(text, true));
                paragraph.addXmlnukeObject(new XmlnukeBreakLine());
            }
        }

        public void showErrors(XmlBlockCollection block)
        {
    	    if (this._errors.Count > 0)
    	    {
    		    block.addXmlnukeObject(new XmlnukeText("Errors:",true,false,false,true));
        		
    		    foreach (object o in this._errors)
    		    {
    			    block.addXmlnukeObject(new XmlnukeText(o.ToString(),false,false,false,true));
    		    }
    	    }
        	
    	    block.addXmlnukeObject(new XmlnukeBreakLine());
        }

        /// <summary>
	    /// This is the "real" main. The class main() instantiates a tar object
	    /// for the application and then calls this method. Process the arguments
	    /// and perform the requested operation.
	    /// </summary>
	    protected void InstanceMain()
	    {
		    TarArchive archive = null;

            if (this.archiveName != null && !this.archiveName.Equals("-"))
            {
			    if (operation == Operation.Create) {
				    if (!Directory.Exists(Path.GetDirectoryName(archiveName))) {
					    this._errors.Add("Directory for archive doesnt exist");
					    return;
				    }
			    }
			    else {
				    if (File.Exists(this.archiveName) == false) {
					    this._errors.Add("File does not exist " + this.archiveName);
					    return;
				    }
			    }
		    }
    		
		    if (operation == Operation.Create)  		               // WRITING
            {
                Stream outStream = File.Create(archiveName);
    			
			    if (outStream != null) {
				    switch (this.compression) {
					    case Compression.Compress:
						    outStream = new DeflaterOutputStream(outStream);
						    break;

					    case Compression.Gzip:
						    outStream = new GZipOutputStream(outStream);
						    break;

					    case Compression.Bzip2:
						    outStream = new BZip2OutputStream(outStream, 9);
					    break;
				    }
				    archive = TarArchive.CreateOutputTarArchive(outStream, this.blockingFactor);
			    }
		    } 
            else                               // EXTRACTING OR LISTING
            {								
			    Stream inStream = File.OpenRead(archiveName);
    			
			    if (inStream != null) 
                {
				    switch (this.compression) 
                    {
					    case Compression.Compress:
						    inStream = new InflaterInputStream(inStream);
						    break;

					    case Compression.Gzip:
						    inStream = new GZipInputStream(inStream);
						    break;
    					
					    case Compression.Bzip2:
						    inStream = new BZip2InputStream(inStream);
						    break;
				    }
				    archive = TarArchive.CreateInputTarArchive(inStream, this.blockingFactor);
			    }
		    }
    		
		    if (archive != null) {						// SET ARCHIVE OPTIONS
			    archive.SetKeepOldFiles(this.keepOldFiles);
			    archive.AsciiTranslate = this.asciiTranslate;
    			
			    archive.SetUserInfo(this.userId, this.userName, this.groupId, this.groupName);
		    }

            if (archive == null)
            {
                this._errors.Add("no processing due to errors");
            }
            else
            {
                archive.ProgressMessageEvent += new ProgressMessageHandler(ShowTarProgressMessage);


                if (operation == Operation.Create)                         // WRITING
                {
                    if (this._rootPath != null)
                        archive.RootPath = _rootPath;
                    for (int argIdx = 0; argIdx < this._files.Count; ++argIdx)
                    {
                        Tar.CreateEntry(archive, this._files[argIdx].ToString());
                    }
                }
                else if (operation == Operation.List)                    // LISTING
                {
                    archive.ListContents();
                }                                                     // EXTRACTING
                else
                {
                    string userDir = (this._rootPath == null) ? Environment.CurrentDirectory : this._rootPath;
                    if (userDir != null)
                    {
                        archive.ExtractContents(userDir);
                    }
                }

                archive.Close();                                   // CLOSE ARCHIVE
            }
	    }

        static void CreateEntry(TarArchive archive, string file)
        {
            string oldDir = archive.RootPath;
            Environment.CurrentDirectory = archive.RootPath;

            TarEntry entry = TarEntry.CreateEntryFromFile(file);
            archive.WriteEntry(entry, true);

            //string[] fileNames = GetFilesForSpec(file);
            //if ((fileNames != null) && (fileNames.Length > 0))
            //{
            //    foreach (string name in fileNames)
            //    {
            //        TarEntry entry = TarEntry.CreateEntryFromFile(name);
            //        archive.WriteEntry(entry, true);
            //    }
            //}
            //string[] dirNames = GetDirectoriesForSpec(file);
            //if ((dirNames != null) && (dirNames.Length > 0))
            //{
            //    foreach (string dir in dirNames)
            //    {
            //        TarEntry entry = TarEntry.CreateEntryFromFile(dir);
            //        archive.WriteEntry(entry, true);
            //        //Tar.CreateEntryRecursively(archive, dir);
            //    }
            //}
            Environment.CurrentDirectory = oldDir;
        }

        //static string[] GetDirectoriesForSpec(string spec)
        //{
        //    if (!Directory.Exists(spec))
        //    {
        //        return null; // throw new Exception("File does not exists");
        //    }
        //    else
        //    {
        //        return System.IO.Directory.GetDirectories(spec);
        //    }
        //}
        
        //static string[] GetFilesForSpec(string spec)
        //{
        //    if (!Directory.Exists(spec))
        //    {
        //        if (!File.Exists(spec))
        //        {
        //            return null; // throw new Exception("File does not exists");
        //        }
        //        else
        //        {
        //            return new string[] { spec };
        //        }
        //    }
        //    else
        //    {
        //        return System.IO.Directory.GetFiles(spec);
        //    }
        //}

        /// <summary>
        /// Display progress information on console
        /// </summary>
        public void ShowTarProgressMessage(TarArchive archive, TarEntry entry, string message)
        {
            if (entry.TarHeader.TypeFlag != TarHeader.LF_NORMAL && entry.TarHeader.TypeFlag != TarHeader.LF_OLDNORM)
            {
                //this._result.Add("Entry type " + (char)entry.TarHeader.TypeFlag + " found!");
            }

            if (this._filesTar == null)
            {
                this._filesTar = new ArrayList();
                this._direcsTar = new ArrayList();
            }

            if (message != null)
                this._result.Add(entry.Name + " " + message);
            else
            {
                //string modeString = DecodeType(entry.TarHeader.TypeFlag, entry.Name.EndsWith("/")) + DecodeMode(entry.TarHeader.Mode);
                //string userString = (entry.UserName == null || entry.UserName.Length == 0) ? entry.UserId.ToString() : entry.UserName;
                //string groupString = (entry.GroupName == null || entry.GroupName.Length == 0) ? entry.GroupId.ToString() : entry.GroupName;

                //this._result.Add(string.Format("{0} {1}/{2} {3,8} {4:yyyy-MM-dd HH:mm:ss} {5}", modeString, userString, groupString, entry.Size, entry.ModTime.ToLocalTime(), entry.Name));
                this._result.Add(entry.Name);
                if (entry.IsDirectory)
                    this._direcsTar.Add(entry.Name);
                else
                    this._filesTar.Add(entry.Name);

                //}
                //else
                //{
                //    Console.WriteLine(entry.Name);
                //}
            }

        }

	    static string DecodeType(int type, bool slashTerminated)
	    {
		    string result = "?";
		    switch (type)
		    {
			    case TarHeader.LF_OLDNORM:       // -jr- TODO this decoding is incomplete, not all possible known values are decoded...
			    case TarHeader.LF_NORMAL:
			    case TarHeader.LF_LINK:
				    if (slashTerminated)
					    result = "d";
				    else
					    result = "-";
				    break;

			    case TarHeader.LF_DIR:
				    result = "d";
				    break;

			    case TarHeader.LF_GNU_VOLHDR:
				    result = "V";
				    break;

			    case TarHeader.LF_GNU_MULTIVOL:
				    result = "M";
				    break;

			    case TarHeader.LF_CONTIG:
				    result = "C";
				    break;

			    case TarHeader.LF_FIFO:
				    result = "p";
				    break;

			    case TarHeader.LF_SYMLINK:
				    result = "l";
				    break;

			    case TarHeader.LF_CHR:
				    result = "c";
				    break;

			    case TarHeader.LF_BLK:
				    result = "b";
				    break;
		    }

		    return result;
	    }

	    static string DecodeMode(int mode)
	    {	

		    const int S_ISUID = 0x0800;
		    const int S_ISGID = 0x0400;
		    const int S_ISVTX = 0x0200;

		    const int S_IRUSR = 0x0100;
		    const int S_IWUSR = 0x0080;
		    const int S_IXUSR = 0x0040;

		    const int S_IRGRP = 0x0020;
		    const int S_IWGRP = 0x0010;
		    const int S_IXGRP = 0x0008;

		    const int S_IROTH = 0x0004;
		    const int S_IWOTH = 0x0002;
		    const int S_IXOTH = 0x0001;


		    System.Text.StringBuilder result = new System.Text.StringBuilder();
		    result.Append((mode & S_IRUSR) != 0 ? 'r' : '-');
		    result.Append((mode & S_IWUSR) != 0 ? 'w' : '-');
		    result.Append((mode & S_ISUID) != 0
				    ? ((mode & S_IXUSR) != 0 ? 's' : 'S')
				    : ((mode & S_IXUSR) != 0 ? 'x' : '-'));
		    result.Append((mode & S_IRGRP) != 0 ? 'r' : '-');
		    result.Append((mode & S_IWGRP) != 0 ? 'w' : '-');
		    result.Append((mode & S_ISGID) != 0
				    ? ((mode & S_IXGRP) != 0 ? 's' : 'S')
				    : ((mode & S_IXGRP) != 0 ? 'x' : '-'));
		    result.Append((mode & S_IROTH) != 0 ? 'r' : '-');
		    result.Append((mode & S_IWOTH) != 0 ? 'w' : '-');
		    result.Append( (mode & S_ISVTX) != 0
				    ? ((mode & S_IXOTH) != 0 ? 't' : 'T')
				    : ((mode & S_IXOTH) != 0 ? 'x' : '-'));

		    return result.ToString();
	    }

	    static string SharpZipVersion()
	    {
		    System.Reflection.Assembly zipAssembly = System.Reflection.Assembly.GetAssembly(new TarHeader().GetType());
		    Version v = zipAssembly.GetName().Version;
		    return "#ZipLib v" + v.Major + "." + v.Minor + "." + v.Build + "." + v.Revision;
	    }
    	
	    /// <summary>
	    /// Print version information.
	    /// </summary>
	    static string Version()
	    {
            string result = "";
		    result += "tar 2.0.6.2\n" ;
		    result += "\n" ;
		    result += string.Format("{0}\n", SharpZipVersion() );
		    result += "Copyright (c) 2007 by João Gilberto Magalhães (Xmlnuke Port)\n" ;
		    result += "Copyright (c) 2002 by Mike Krueger\n" ;
		    result += "Copyright (c) 1998,1999 by Tim Endres (Java version)\n" ;
		    result += "\n" ;
		    result += "This program is free software licensed to you under the\n" ;
		    result += "GNU General Public License. See the accompanying LICENSE\n" ;
		    result += "file, or the webpage <http://www.gjt.org/doc/gpl> or,\n" ;
		    result += "visit www.gnu.org for more details.\n" ;
		    result += "\n" ;

            return result;
	    }
    }
}
