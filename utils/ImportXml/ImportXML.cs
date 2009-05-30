using System;
using System.Collections;
using System.Xml;
using System.IO;

namespace com.xmlnuke.db
{
	class ImportXML
	{
		private bool ok = false;
		private bool config_new2old = false;
		private bool config_generic = false;
		private string oldRootDir = "";
		private string newRootDir = "";
		private string language = "";
		private string site = "";


		[STAThread]
		static void Main(string[] args)
		{
			ImportXML convtool = new ImportXML();

			Console.WriteLine();
			Console.WriteLine("XMLNukeDB repository Conversion tool");
			Console.WriteLine("Import XML from old XMLNuke repository to a newer XMLNukeDB repository");
			Console.WriteLine();

			convtool.setupEnvironment(args);

			if (!convtool.ok)
			{
				Console.WriteLine("Usage: ");
				Console.WriteLine("\timportxml [opt] -old:path -new:path -l:lang -site:name");
				Console.WriteLine();
				Console.WriteLine("Where required parameteres are: ");
				Console.WriteLine("\t-old represents the ROOTDIR of old repository (ROOTDIR parameter)");
				Console.WriteLine("\t-new represents the ROOTDIR of new repository (ROOTDIR parameter)");
				Console.WriteLine("\t-l Language to be used");
				Console.WriteLine("\t-site representes the site name");
				Console.WriteLine();
				Console.WriteLine("and optional [opt] parameteres are:");
				Console.WriteLine("\t-newtoold copy xml from a repository to a single directory struct");
				Console.WriteLine("\t-generic These documents not exists in any XMLNuke repository and ");
				Console.WriteLine("\t\twill be imported. No effect with -newtoold option.");
				Console.WriteLine();
				Console.WriteLine("Windows Example:");
				Console.WriteLine("importxml -old:c:\\aspengine\\sites -new:c:\\csharp\\data\\sites -l:pt-br -site:demo");
				Console.WriteLine();
				Console.WriteLine("Linux Example:");
				Console.WriteLine("importxml -old:/aspengine/sites -new:/csharp/data/sites -l:pt-br -site:demo");
				Console.WriteLine();
				return;
			}


			if (!convtool.config_new2old)
			{
				if (!System.IO.Directory.Exists(convtool.oldRootDir)) 
				{
					Console.WriteLine("The path you specified for Old Repository does not exists. Please check it and try again.");
					Console.WriteLine();
					return;
				}
				if (!System.IO.Directory.Exists(convtool.oldRootDir + PersistUtil.getSlash() + convtool.site)) 
				{
					Console.WriteLine("The path you specified for Old Repository exist but does not have the site you specified. Please check it and try again.");
					Console.WriteLine();
					if (!convtool.config_generic)
					{
						return;
					}
					else
					{
						Console.WriteLine("Using GENERIC mode. Ignore this message.");
						Console.WriteLine();
					}
				}
				string startPath;
				string destPath = convtool.newRootDir + PersistUtil.getSlash() + convtool.site + PersistUtil.getSlash() + "xml";
				XmlNukeDB repositorio = new XmlNukeDB(true, destPath, convtool.language, true);
				if (!convtool.config_generic)
				{
					startPath = convtool.oldRootDir + PersistUtil.getSlash() + convtool.site + PersistUtil.getSlash() + "xml";
					repositorio.importDocuments(startPath, "*." + convtool.language + ".xml", false);
				}
				else
				{
					startPath = convtool.oldRootDir;
					repositorio.importDocuments(startPath, "*.xml", true);
				}
				repositorio.saveIndex();
			}
			else
			{
				if (!System.IO.Directory.Exists(convtool.newRootDir)) 
				{
					Console.WriteLine("The path you specified for New Repository does not exists. Please check it and try again.");
					Console.WriteLine();
					return;
				}
				if (!System.IO.Directory.Exists(convtool.newRootDir + PersistUtil.getSlash() + convtool.site)) 
				{
					Console.WriteLine("The path you specified for New Repository exist but does not have the site you specified. Please check it and try again.");
					Console.WriteLine();
					return;
				}
				string startPath = convtool.newRootDir + PersistUtil.getSlash() + convtool.site + PersistUtil.getSlash() + "xml" + PersistUtil.getSlash() + convtool.language ;
				string destPath = convtool.oldRootDir + PersistUtil.getSlash() + convtool.site + PersistUtil.getSlash() + "xml";
				convtool.FileCopy(startPath, destPath, "*.xml", true);
			}
			Console.WriteLine("Finished.\n");

		}

		private void FileCopy(string srcdir, string destdir, string mask, bool recursive)
		{
			DirectoryInfo   dir;
			FileInfo[]      files;
			DirectoryInfo[] dirs;
			string          tmppath;

			//determine if the destination directory exists, if not create it
			if (! Directory.Exists(destdir))
			{
				Directory.CreateDirectory(destdir);
			}

			dir = new DirectoryInfo(srcdir);
            
			//if the source dir doesn't exist, throw
			if (! dir.Exists)
			{
				throw new ArgumentException("source dir doesn't exist -> " + srcdir);
			}

			//get all files in the current dir
			files = dir.GetFiles(mask);

			//loop through each file
			foreach(FileInfo file in files)
			{
				//create the path to where this file should be in destdir
				tmppath=Path.Combine(destdir, file.Name);                

				//copy file to dest dir
				file.CopyTo(tmppath, false);
			}

			//cleanup
			files = null;
            
			//if not recursive, all work is done
			if (! recursive)
			{
				return;
			}

			//otherwise, get dirs
			dirs = dir.GetDirectories();

			//loop through each sub directory in the current dir
			foreach(DirectoryInfo subdir in dirs)
			{
				/* ORIGINAL
				//create the path to the directory in destdir
				tmppath = Path.Combine(destdir, subdir.Name);
				*/

				//recursively call this function over and over again
				//with each new dir.
				FileCopy(subdir.FullName, destdir, mask, recursive);
			}
            
			//cleanup
			dirs = null;
            
			dir = null;
		}

		private bool setupOptionalEnv(string arg)
		{
			if (arg == "-newtoold")
			{
				config_new2old = true;
				return true;
			}
			if (arg == "-generic")
			{
				config_generic = true;
				return true;
			}
			return false;
		}

		private void setupEnvironment(string[] args)
		{
			foreach (string arg in args)
			{
				if (!setupOptionalEnv(arg.ToLower()))
				{

					if (arg[0] != '-')
					{
						return;
					}

					int i = arg.IndexOf(":");
					if (i == -1)
					{
						return;
					}

					string oper = arg.Substring(0, i);
					string param = arg.Substring(i+1);
					switch (oper)
					{
						case "-old":
							oldRootDir = param;
							break;
						case "-new":
							newRootDir = param;
							break;
						case "-l":
							language = param;
							break;
						case "-site":
							site = param;
							break;
					}
				}
			}
			
			ok = (oldRootDir != "") && (newRootDir != "") && (language != "") && (site != "");
		}


	}
}
