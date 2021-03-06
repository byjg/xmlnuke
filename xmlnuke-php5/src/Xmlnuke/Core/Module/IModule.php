<?php
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

namespace Xmlnuke\Core\Module;

use ByJG\Cache\ICacheEngine;
use Xmlnuke\Core\Classes\IXmlnukeDocument;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Locale\LanguageCollection;
use Xmlnuke\Core\Processor\XMLFilenameProcessor;

/**
 * IModule is a generic interface used to create custom user modules. 
 * 
 * All modules need implement this interface.
 * To create a user module you can implement this interface or inherit from BaseModule class it implements all generic functions.
 * 
 * @package xmlnuke
 */
interface IModule
{
	/**
	 * Create the Language Collection to be use in the Module
	 * Return The Current LanguageCollection for this module
	 * 
	 * @return LanguageCollection
	 */
	function WordCollection();

	/**
	 * Create the main module page
	 * 
	 * @return IXmlnukeDocument
	 */
	function CreatePage();

	/**
	 * ModuleFactory call this method to Setup default values to module.
	 *
	 * @param XMLFilenameProcessor $xmlModuleName
	 * @param object $customArgs
	 */
	function Setup($xmlModuleName, $customArgs);

	/**
	 * Check if module result can be write to cache
	 * Return a INT to use a cache and false if never use cache
	 * 
	 * @return bool
	 */
	function useCache();

	/**
	 * Return the Cache engine to set and get modules from cache
	 *
	 * @return ICacheEngine
	 */
	function getCacheEngine();

	/**
	 * Return the cache name unique for this module;
	 *
	 * @return string
	 */
	function getCacheId();

	/**
	 * Check if current module requires authentication.
	 * Return True if module requires authentication. False, otherwise.
	 *
	 * @return bool
	 */
	function requiresAuthentication();

	/**
	 * Check if current module have access granted.
	 * Return True is access granted; false otherwise.
	 * 
	 * @return bool
	 */
	function accessGranted();

	/**
	 * Returns Default Access Level for this module (only if module requiresAuthentication)
	 *
	 * @return AccessLevel
	 */
	function getAccessLevel();

	/**
	 * If access is granted (user is knows) but it is Insufficient Privilege to access this option 
	 * (see accessGranted and getAccessLevel), this routine can be adjust module!
	 *
	 */
	function processInsufficientPrivilege();

	/**
	 * Get module role to guarantee current user access site
	 *
	 * @return string[]
	 */
	function getRole();

	/**
	 * Verify if this module is admin page
	 *
	 */
	function isAdmin();

	/**
	 * This page requires SSL Layer.
	 *
	 */
	function requiresSSL();
	/**
	 * Returns the php://input stream
	 */
	function getRawRequest();
	/**
	 * Returns if the requires authentication will be directed to the Form login OR will use the HTTP DIGEST
	 */
	function getAuthMode();

	/**
	 * Return the must be used in this module
	 * @return \Xmlnuke\Core\Processor\XSLFilenameProcessor File name processor
	 */
	function getXsl();

	/**
	 * Force a specific output if the return is not null and ignores the parameter "raw"
	 * @return \Xmlnuke\Core\Enum\OutputData
	 */
	function getOutputFormat();

}
?>