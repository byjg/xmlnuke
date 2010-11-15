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

namespace com.xmlnuke.module
{
	/// <summary>
	/// IModule is a generic interface used to create custom user modules. All modules need implement this interface.
	/// To create a user module you can implement this interface or inherit from BaseModule class it implements all generic functions.
	/// </summary>
	public interface IModule
	{
		/// <summary>
		/// Create the Language Collection to be use in the Module
		/// </summary>
		/// <returns>The Current LanguageCollection for this module</returns>
		international.LanguageCollection WordCollection();

		/// <summary>
		/// Create the main module page
		/// </summary>
		/// <returns></returns>
		classes.IXmlnukeDocument CreatePage();

		/// <summary>
		/// ModuleFactory call this method to Setup default values to module.
		/// </summary>
		/// <param name="xmlModuleName"></param>
		/// <param name="context"></param>
		/// <param name="customArgs"></param>
		void Setup(processor.XMLFilenameProcessor xmlModuleName, engine.Context context, object customArgs);

		/// <summary>
		/// Check if module result already in cache.
		/// </summary>
		/// <returns>True if already in cache</returns>
		bool hasInCache();

		/// <summary>
		/// Check if module result can be write to cache
		/// </summary>
		/// <returns>True if never use cache</returns>
		bool useCache();

		/// <summary>
		/// Get module result from cache.
		/// </summary>
		/// <returns>Module result</returns>
		string getFromCache();

		/// <summary>
		/// Save module result to cache
		/// </summary>
		/// <param name="content">Module output</param>
		void saveToCache(string content);

		/// <summary>
		/// Erase cache information
		/// </summary>
		void resetCache();

		/// <summary>
		/// Check if current module requires authentication.
		/// </summary>
		/// <returns>True if module requires authentication. False, otherwise.</returns>
		bool requiresAuthentication();

		/// <summary>
		/// Check if current module have access granted. 
		/// </summary>
		/// <returns>True is access granted; false otherwise.</returns>
		bool accessGranted();

		/// <summary>
		/// Returns Default Access Level for this module (only if module requiresAuthentication)
		/// </summary>
		/// <returns>AccessLevel</returns>
		AccessLevel getAccessLevel();

		/// <summary>
		/// If access is granted (user is knows) but it is Insufficient Privilege to access this option (see accessGranted and getAccessLevel), this routine can be adjust module!
		/// </summary>
		void processInsufficientPrivilege();

		/// <summary>
		/// The last method will be executed.
		/// In CSharp the destructor didnt is used because is executed ONLY BY Garbage Collector.
		/// This method is the last option executed.
		/// </summary>
		void finalizeModule();

		string[] getRole();

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        SSLAccess requiresSSL();
	}
}