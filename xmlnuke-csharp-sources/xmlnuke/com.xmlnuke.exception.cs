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

namespace com.xmlnuke.exceptions
{

	/// <summary>
	/// This exception occurs when the requested module not found
	/// </summary>
	public class NotFoundException : System.Exception
	{
		public NotFoundException()
		{ }

		public NotFoundException(string message)
			: base(message)
		{ }

		public NotFoundException(string message, System.Exception innerException)
			: base(message, innerException)
		{ }
	}


	/// <summary>
	/// This exception occurs when the method requiresAuthentication returns true and user is not authenticated
	/// </summary>
	public class NotAuthenticatedException : System.Exception
	{
		public NotAuthenticatedException()
		{ }

		public NotAuthenticatedException(string message)
			: base(message)
		{ }

		public NotAuthenticatedException(string message, System.Exception innerException)
			: base(message, innerException)
		{ }
	}

	/// <summary>
	/// This exception occurs when the method accessGranted returns false
	/// </summary>
	public class InsufficientPrivilegeException : System.Exception
	{
		public InsufficientPrivilegeException()
		{ }

		public InsufficientPrivilegeException(string message)
			: base(message)
		{ }

		public InsufficientPrivilegeException(string message, System.Exception innerException)
			: base(message, innerException)
		{ }
	}


}