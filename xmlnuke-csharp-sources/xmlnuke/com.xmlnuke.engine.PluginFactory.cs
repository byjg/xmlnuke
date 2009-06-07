using System;
using System.Collections.Generic;
using System.Text;
using System.Reflection;
using com.xmlnuke.exceptions;

namespace com.xmlnuke.engine
{
	public class PluginFactory
	{
		public static object LoadPlugin(string className)
		{
			return PluginFactory.LoadPlugin(className, null, null, null, null);
		}

		public static object LoadPlugin(string className, object param1)
		{
			return PluginFactory.LoadPlugin(className, param1, null, null, null);
		}

		public static object LoadPlugin(string className, object param1, object param2)
		{
			return PluginFactory.LoadPlugin(className, param1, param2, null, null);
		}

		public static object LoadPlugin(string className, object param1, object param2, object param3)
		{
			return PluginFactory.LoadPlugin(className, param1, param2, param3, null);
		}

		/// <summary>
		/// Instantiate a class by Reflection. 
		/// </summary>
		/// <param name="className">Fullname space</param>
		/// <param name="param1"></param>
		/// <param name="param2"></param>
		/// <param name="param3"></param>
		/// <param name="param4"></param>
		/// <returns></returns>
		public static object LoadPlugin(string className, object param1, object param2, object param3, object param4)
		{
			Assembly asm;
			object result;

			object[] paramArray = null;

			// Define Parameters
			if (param1 == null)
			{
				paramArray = new object[] { };
			}
			else if (param2 == null)
			{
				paramArray = new object[] { param1 };
			}
			else if (param3 == null)
			{
				paramArray = new object[] { param1, param2 };
			}
			else if (param4 == null)
			{
				paramArray = new object[] { param1, param2, param3 };
			}
			else
			{
				paramArray = new object[] { param1, param2, param3, param4 };
			}

			asm = Assembly.GetExecutingAssembly();
			try
			{
				// Try instance object from current assembly executing
				result = asm.CreateInstance(
					className,
					true,
					BindingFlags.CreateInstance | BindingFlags.NonPublic | BindingFlags.Public | BindingFlags.Instance,
					null,
					paramArray,
					null,
					null
				);

				if (result == null)
				{
					string assemblyName = className.Substring(0, className.LastIndexOf("."));

					asm = Assembly.Load(assemblyName);
					result = asm.CreateInstance(
						className,
						true,
						BindingFlags.CreateInstance | BindingFlags.NonPublic | BindingFlags.Public | BindingFlags.Instance,
						null,
						paramArray,
						null,
						null
					);
				}
			}
			catch (Exception ex)
			{
				string message = ex.Message;
				if (ex.InnerException != null)
				{
					message += " " + ex.InnerException.Message;
				}
				throw new NotFoundException("Error on loading class '" + className + "'. Reason: " + message);
			}


			if (result == null)
			{
				throw new NotFoundException("Class '" + className + "' not found");
			}
			else
			{
				return result;
			}
		}
	}
}
