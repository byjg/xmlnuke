/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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

using com.xmlnuke.classes;
using com.xmlnuke.engine;
using com.xmlnuke.util;
using com.xmlnuke.processor;

namespace com.xmlnuke.international
{
	public enum LanguageFileTypes
	{
		ADMINMODULE,
		ADMININTERNAL,
		MODULE,
		OBJECT,
		INTERNAL
	}

	public class LanguageFactory
	{
		/**
		 * Enter description here...
		 *
		 * @param Context context
		 * @param LanguageFileTypes type
		 * @param string name
		 * @return LanguageCollection
		 */
		public static LanguageCollection GetLanguageCollection(Context context, LanguageFileTypes type, string name)
		{
			AnydatasetLangFilenameProcessor langFile;
			LanguageCollection lang;

			switch (type)
			{
				case LanguageFileTypes.ADMINMODULE:
					name = name.ToLower().Replace(".", "-");
					langFile = new AnydatasetLangFilenameProcessor(name, context);
					break;

				case LanguageFileTypes.ADMININTERNAL:
					langFile = new AdminModulesLangFilenameProcessor(context);
					break;

				case LanguageFileTypes.MODULE:
					name = name.ToLower().Replace(".", "-");
					langFile = new AnydatasetLangFilenameProcessor(name, context);
					break;

				case LanguageFileTypes.OBJECT:
					name = name.ToLower().Replace(".", "-");
					langFile = new AnydatasetLangFilenameProcessor(name, context);
					break;

				default:
					langFile = new AnydatasetLangFilenameProcessor(name, context);
					break;
			}
			lang = new LanguageCollection(context);
			lang.LoadLanguages(langFile);
			return lang;
		}
	}
}