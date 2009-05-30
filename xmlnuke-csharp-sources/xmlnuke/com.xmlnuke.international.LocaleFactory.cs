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
//using System.Reflection;
using System.Globalization;
using System.Threading;

namespace com.xmlnuke.international
{
	/// <summary>
	/// LocaleFactory creates the proper CultureInfo and assign it to CurrentThread. This classes enable output from Data, Currency, numbers and others are localized properly from the Language specified.
	/// </summary>
	public class LocaleFactory
	{
		private LocaleFactory()
		{
		}

		/// <summary>
		/// Static method to Create the CultureInfo and assign it to the CurrentThread
		/// </summary>
		/// <param name="lang">Language Name in the 5 letters format. Example: pt-br, en-us</param>
		/// <returns>Return the CultureInfo created</returns>
		public static CultureInfo GetLocale(string lang)
		{
			CultureInfo locale = new CultureInfo(lang);
			Thread.CurrentThread.CurrentCulture = locale;

			/*
			Assembly a = Assembly.GetExecutingAssembly();
			locale = (LocaleIF)a.CreateInstance("com.xmlnuke.international.Locale_"+lang.Replace("-","_"));
			if (locale == null)
			{
				locale = new Locale_en_us();
			}
			*/
			return locale;
		}
	}
}