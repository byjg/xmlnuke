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
using System.Reflection;
using com.xmlnuke.exceptions;

namespace com.xmlnuke.module
{
	/// <summary>
	/// Locate and create custom user modules. 
	/// <p>
	/// All modules must follow these rules: 
	/// <ul>
	/// <li>implement IModule interface or inherit from BaseModule (recommended); </li>
	/// <li>Compile into XMLNuke engine or have the file name com.xmlnuke.module.[modulename]; </li>
	/// <li>Have com.xmlnuke.module namespace. </li>
	/// </ul>
	/// </p>
	/// </summary>
	public class ModuleFactory
	{
		/// <summary>
		/// Doesn't need constructor because all methods are statics.
		/// </summary>
		public ModuleFactory()
		{ }

		/// <summary>
		/// Locate and create custom module if exists. Otherwise throw exception.
		/// </summary>
		/// <param name="modulename"></param>
		/// <param name="context"></param>
		/// <param name="o"></param>
		/// <returns></returns>
		public static IModule GetModule(string modulename, engine.Context context, object o)
		{
			IModule result;
			Assembly asm;

			asm = Assembly.GetExecutingAssembly();
			result = (IModule)asm.CreateInstance("com.xmlnuke.module." + modulename, true);
			if (result == null)
			{
				// Try read full module name in current instance
				result = (IModule)asm.CreateInstance(modulename, true);

				// If doesnt exists in current instance, try the follow options:
				// Assembly: com.xmlnuke.module.<xml>.dll 
				// Class: com.xmlnuke.module.<xml>
				// - or -
				// Assembly: <xml>.dll 
				// Class: <xml>
				if (result == null)
				{
					string moduleToLoad = "";
					string classToLoad = "";
					if (modulename.IndexOf(".") < 0)
					{
						moduleToLoad = "com.xmlnuke.module." + modulename;
						classToLoad = "com.xmlnuke.module." + modulename;

					}
					else
					{
						moduleToLoad = modulename.Substring(0, modulename.LastIndexOf("."));
						classToLoad = modulename;
					}

					try
					{
						asm = Assembly.Load(moduleToLoad);
						result = (IModule)asm.CreateInstance(classToLoad, true);
						if (result == null)
						{
							throw new NotFoundException("Class " + classToLoad + " not found in assembly " + moduleToLoad + ".dll");
						}
					}
					catch (System.IO.FileNotFoundException)
					{
						throw new NotFoundException("Assembly " + moduleToLoad + ".dll not found");
					}
				}
			}

			processor.XMLFilenameProcessor mod = new processor.XMLFilenameProcessor(modulename, context);
			if (!util.FileUtil.isWindowsOS())
			{
				// Mono Have problems to pass a NULL value from an interface.
				if (o == null)
				{
					o = "Anything";
				}
			}

			result.Setup(mod, context, o);
			if (result.requiresAuthentication())
			{
				if (!context.IsAuthenticated())
				{
					throw new NotAuthenticatedException("You need login to access this feature");
				}
				else
				{
					if (!result.accessGranted())
					{
						result.processInsufficientPrivilege();
					}
				}
			}

			return result;
		}
	}
}