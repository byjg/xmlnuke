/*

This file is a part of SLNToMake.

Copyright (c) 2002, 2003 Jaroslaw Kowalski <jaak@polbox.com>
All rights reserved.

Redistribution and use in source and binary forms, with or without 
modification, are permitted provided that the following conditions 
are met:

* Redistributions of source code must retain the above copyright notice, 
this list of conditions and the following disclaimer. 

* Redistributions in binary form must reproduce the above copyright notice,
this list of conditions and the following disclaimer in the documentation
and/or other materials provided with the distribution. 

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF 
THE POSSIBILITY OF SUCH DAMAGE.
*/

using System;
using System.Xml;
using System.Collections;
using System.IO;
using System.Diagnostics;
using System.Text.RegularExpressions;
using System.Reflection;

namespace SlnToMake
{
	class ProjectInfo
	{
		public readonly string name;
		public readonly string guid;
		public readonly string csprojpath;
		public string makename;
		public string makename_ext;
		public XmlDocument doc;
		public string assembly_name;
		public string src;

		public string ext_refs = "";
		public string switches = "";

		public ProjectInfo(string name, string guid, string csprojpath)
		{
			this.name = name;
			this.guid = guid;
			this.csprojpath = csprojpath;

			makename = name.Replace('.','_').Replace('-','_').ToUpper();
			makename_ext = makename + "_EXT";

			// convert backslashes to slashes
			
			csprojpath = csprojpath.Replace("\\", "/");

			doc = new XmlDocument();
			doc.Load(csprojpath);
			
			XmlElement root = doc.DocumentElement;
						
			System.Xml.XmlNamespaceManager nm = new System.Xml.XmlNamespaceManager(doc.NameTable);
			nm.AddNamespace("nm", "http://schemas.microsoft.com/developer/msbuild/2003");
			
			XmlElement settingsNode = (XmlElement)root.SelectSingleNode("/nm:Project/nm:PropertyGroup/nm:OutputType", nm);
			XmlElement assemblyNode = (XmlElement)root.SelectSingleNode("/nm:Project/nm:PropertyGroup/nm:AssemblyName", nm);

			switch (settingsNode.InnerText)
			{
				case "Library":
					makename_ext = makename + "_DLL";
					assembly_name = assemblyNode.InnerText.ToLower() + ".dll";
					switches += " /target:library";
					break;

				case "Exe":
					makename_ext = makename + "_EXE";
					assembly_name = assemblyNode.InnerText.ToLower() + ".exe";
					switches += " /target:exe";
					break;

				case "WinExe":
					makename_ext = makename + "_EXE";
					assembly_name = assemblyNode.InnerText.ToLower() + ".exe";
					switches += " /target:winexe";
					break;

				default:
					throw new NotSupportedException("Unsupported OutputType: " + settingsNode.InnerText);
				
			}

			src = "";

			string basePath = Path.GetDirectoryName(csprojpath);
			if (String.IsNullOrEmpty(basePath))
			{
				basePath = ".";
			}

			foreach (XmlElement el in doc.SelectNodes("/nm:Project/nm:ItemGroup/nm:Compile", nm))
			{
				if (src != "")
				{
					src += " \\\n\t";
				};
				string s = String.Format(@"{0}{1}{2}", basePath, Maker.slash, el.GetAttribute("Include"));

				s = s.Replace("\\", "/");
				if (Maker.slash != "/")
					s = s.Replace("/", Maker.slash);
				else
					s = s.Replace(" ", "\\ ");
			
				src += s;
				
			}
		}
	}

	public class Maker
	{
		static Hashtable projNameInfo = new Hashtable();
		static Hashtable projGuidInfo = new Hashtable();
		public static string slash;
		public static bool unixMode = false;

		static void ParseSolution(string fname)
		{
			FileStream fis = new FileStream(fname,FileMode.Open, FileAccess.Read, FileShare.Read);
			StreamReader reader = new StreamReader(fis);
			Regex regex = new Regex(@"Project\(""\{(.*)\}""\) = ""(.*)"", ""(.*)"", ""(\{.*\})""");

			while (true)
			{
				string s = reader.ReadLine();
				Match match;

				match = regex.Match(s);
				if (match.Success)
				{
					string projectName = match.Groups[2].Value;
					string csprojPath = match.Groups[3].Value;
					string projectGuid = match.Groups[4].Value;

					if (csprojPath.EndsWith(".csproj") && !csprojPath.StartsWith("http://"))
					{
						ProjectInfo pi = new ProjectInfo(projectName, projectGuid, csprojPath);

						projNameInfo[projectName] = pi;
						projGuidInfo[projectGuid] = pi;
					}
				};

				if (s.StartsWith("Global"))
				{
					break;
				};
			}
		}

		static int Usage()
		{
			Console.WriteLine("USAGE: SlnToMake.exe [-u (unix mode)|-w (windows mode)] [-t (no project targets)] filename.sln");
			return 1;
		}

		static int Main(string[] args)
		{
			int i = 0;
			bool noCommonTargets = false;
			bool noProjectTargets = false;
			bool noFlags = false;

			while (i < args.Length && args[i].StartsWith("-"))
			{
				switch (args[i][1])
				{
					case 'u':
						unixMode = true;
						i++;
						break;

					case 'w':
						unixMode = false;
						i++;
						break;

					case 'c':
						noCommonTargets = true;
						i++;
						break;

					case 't':
						noProjectTargets = true;
						i++;
						break;

					case 'f':
						noFlags = true;
						i++;
						break;

					default:
						return Usage();
				}
			}

			if (unixMode)
			{
				slash = "/";
			}
			else
			{
				slash = "\\";
			}

			if (i >= args.Length)
				return Usage();

			string sln = args[i];
			TextWriter makefile = null;

			makefile = Console.Out;

			try
			{
				string d = Path.GetDirectoryName(sln);
                if (d != "")
                {
                    Directory.SetCurrentDirectory(d);
                    sln = Path.GetFileName(sln);
                }
				ParseSolution(sln);

				if (unixMode)
				{
					makefile.WriteLine("ifndef TARGET");
					makefile.WriteLine("\terror You must provide TARGET when making");
					makefile.WriteLine("endif");
				}
				else
				{
					makefile.WriteLine("!if !defined(TARGET)");
					makefile.WriteLine("!error You must provide TARGET when making");
					makefile.WriteLine("!endif");
				}
				makefile.WriteLine();

				if (!noFlags)
				{
					if (unixMode)
					{
						makefile.WriteLine("CSC=gmcs");
						makefile.WriteLine();
						makefile.WriteLine("ifdef RELEASE");
						makefile.WriteLine("CSCFLAGS=/nologo /optimize+ /d:TRACE");
						makefile.WriteLine("else");
						makefile.WriteLine("CSCFLAGS=/nologo /debug+ /d:TRACE,DEBUG");
						makefile.WriteLine("endif");
					}
					else
					{
						makefile.WriteLine("CSC=csc");
						makefile.WriteLine("CSCFLAGS=/nologo");
						makefile.WriteLine();
						makefile.WriteLine("!if defined(RELEASE)");
						makefile.WriteLine("CSCFLAGS=$(CSCFLAGS) /optimize+ /d:TRACE");
						makefile.WriteLine("!else");
						makefile.WriteLine("CSCFLAGS=$(CSCFLAGS) /debug+ /d:TRACE,DEBUG");
						makefile.WriteLine("!endif");
					}
					makefile.WriteLine();
				}
				else
				{
					makefile.WriteLine("!if !defined(CSC)");
					makefile.WriteLine("!error You must provide CSC when making");
					makefile.WriteLine("!endif");
					makefile.WriteLine();
				}

				foreach (ProjectInfo pi in projNameInfo.Values)
				{
					makefile.WriteLine("{0}=$(TARGET){1}{2}", pi.makename_ext, slash, pi.assembly_name);
					makefile.WriteLine("{0}_PDB=$(TARGET){1}{2}", pi.makename, slash, pi.assembly_name.Replace(".dll",".pdb"));
					makefile.WriteLine("{0}_SRC={1}", pi.makename, pi.src);
					makefile.WriteLine();
				}
				
				foreach (ProjectInfo pi in projNameInfo.Values)
				{
					string refs = "";
					string deps = "";
                    string copies = "";
						
					System.Xml.XmlNamespaceManager nm = new System.Xml.XmlNamespaceManager(pi.doc.NameTable);
					nm.AddNamespace("nm", "http://schemas.microsoft.com/developer/msbuild/2003");

					foreach (XmlElement el in pi.doc.SelectNodes("/nm:Project/nm:ItemGroup/nm:Reference", nm))
					{
						if (refs != "")
							refs += " ";

						string assemblyName = el.GetAttribute("Include");
						string[] aux = assemblyName.Split(',');
						assemblyName = aux[0];

						// HACK - under Unix filenames are case sensitive
						// Under Windows there's no agreement on Xml vs XML ;-)
						
						if (0 == String.Compare(assemblyName, "System.Xml", true))
						{
							assemblyName = "System.Xml";
						}
						else
						{
                            XmlNode hintNode = el.SelectSingleNode("nm:HintPath", nm);
							if (hintNode != null)
							{
                                string refHint = SlnToMake.Maker.AdjustMakeDir(Environment.CurrentDirectory, hintNode.InnerText, Path.GetDirectoryName(pi.csprojpath));

				if (unixMode)
					refHint = refHint.Replace("\\", "/");

								refs += "/r:" + refHint;
                                if (copies != "")
                                    copies += ( (unixMode) ? "\n" : "\r\n" );
                                copies += "\t" + ( (unixMode) ? "cp " : "copy " ) + refHint + " $(TARGET)";
								continue;
							}
						}
						if (unixMode)
							assemblyName = assemblyName.Replace("\\", "/");
						refs += "/r:" + assemblyName + ".dll";
					}

					foreach (XmlElement el in pi.doc.SelectNodes("/nm:Project/nm:ItemGroup/nm:ProjectReference/nm:Project", nm))
					{
						ProjectInfo pi2 = (ProjectInfo)projGuidInfo[el.InnerText];

						if (refs != "")
							refs += " ";

						if (deps != "")
							deps += " ";

						refs += "/r:$(" + pi2.makename_ext + ")";
						deps += "$(" + pi2.makename_ext + ")";
					}

					makefile.WriteLine("$({0}): $({1}_SRC) {2}", pi.makename_ext, pi.makename, deps);
					makefile.WriteLine("\t$(CSC) $(CSCFLAGS) {2}{3} /out:$({0}) $({1}_SRC)", pi.makename_ext, pi.makename, refs, pi.switches);
					makefile.WriteLine(copies);
					makefile.WriteLine();
				}

				if (!noCommonTargets)
				{
					makefile.WriteLine();
					makefile.WriteLine("# common targets");
					makefile.WriteLine();
					makefile.Write("all:\t");

					bool first = true;

					foreach (ProjectInfo pi in projNameInfo.Values)
					{
						if (!first)
						{
							makefile.Write(" \\\n\t");
						};
						makefile.Write("$({0})", pi.makename_ext);
						first = false;
					}
					makefile.WriteLine();
					makefile.WriteLine();

					makefile.WriteLine("clean:");

					foreach (ProjectInfo pi in projNameInfo.Values)
					{
						if (unixMode)
						{
							makefile.WriteLine("\t-rm -f \"$({0})\" 2> /dev/null", pi.makename_ext);
							makefile.WriteLine("\t-rm -f \"$({0}_PDB)\" 2> /dev/null", pi.makename);
						}
						else
						{
							makefile.WriteLine("\t-del \"$({0})\" 2> nul", pi.makename_ext);
							makefile.WriteLine("\t-del \"$({0}_PDB)\" 2> nul", pi.makename);
						}
					}
					makefile.WriteLine();
				}

				if (!noProjectTargets)
				{
					makefile.WriteLine();
					makefile.WriteLine("# project names as targets");
					makefile.WriteLine();
					foreach (ProjectInfo pi in projNameInfo.Values)
					{
						makefile.WriteLine("{0}: $({1})", pi.name, pi.makename_ext);
					}
				}
				return 0;
			}
			catch (Exception e)
			{
				Console.WriteLine("EXCEPTION: {0}", e);
				return 1;
			};
		}

        protected static string AdjustMakeDir(string curDir, string pathFile, string pathRef)
        {
            string refDir = Path.GetDirectoryName(curDir + slash + pathRef + slash + pathFile);
            Directory.SetCurrentDirectory(refDir);
            string refHint = Environment.CurrentDirectory + slash + Path.GetFileName(pathFile);
            string[] curDirArr = curDir.Split(slash[0]);
            string[] refHintArr = refHint.Split(slash[0]);
            string result = "";
            string backDir = "";
            for (int i = 0; ((i < curDirArr.Length) || (i < refHintArr.Length)); i++)
            {
                string p1 = null, p2 = null;
                if (i < curDirArr.Length)
                {
                    p1 = curDirArr[i];
                }
                if (i < refHintArr.Length)
                {
                    p2 = refHintArr[i];
                }
                if (p1 != p2)
                {
                    if ((p1 != null) && (p2 != null))
                    {
                        backDir += slash + "..";
                    }
                    if (p2 != null)
                        result += slash + p2;
                    else
                        result += slash + p1;
                }
            }

            result = "." + backDir + result;
            Directory.SetCurrentDirectory(curDir);
            return result;
        }

    }
}
