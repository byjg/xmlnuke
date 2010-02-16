<?php
/**
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

class ModuleAction
{
	const Create = 'new';
	const CreateConfirm = 'action.CREATECONFIRM';
	const Edit = 'edit';
	const EditConfirm = 'action.EDITCONFIRM';
	const Listing = 'action.LIST';
	const View = 'view';
	const Delete = 'delete';
	const DeleteConfirm = 'action.DELETECONFIRM';
}

class AccessLevel
{
	const OnlyAdmin = 0;
	const OnlyCurrentSite = 1;
	const OnlyRole = 2;
	const OnlyAuthenticated= 3;
	const CurrentSiteAndRole = 4;
}

/**
* BaseModule class is the base for custom module implementation. This class uses cache, save to disk and other functionalities.
* All custom modules must inherits this class and need to have com.xmlnuke.module namespace.
*@see com.xmlnuke.module.IModule
*@see com.xmlnuke.module.ModuleFactory
*@package com.xmlnuke
*@subpackage xmlnuke.modules
*/
abstract class BaseModule implements IModule
{
	/**
	*@var Context
	*/
	protected $_context;
	/**
	* Module name
	*@var XMLFilenameProcessor
	*/
	protected $_xmlModuleName;
	/**
	* Cache file module
	*@var XMLCacheFilenameProcessor
	*/
	protected $_cacheFile;
	/**
	* Action from Request["Action"]
	*@var string
	*/
	protected $_action;
	public function setAction($value)
	{
		$this->_action = $value;
	}
	public function getAction()
	{
		return $this->_action;
	}
	/**
	* Optional use XmlnukeDocument to build your page
	*@var XmlnukeDocument
	*/
	public $defaultXmlnukeDocument;
	
	/**
	 * Optional use XmlnukeManageUrl to this module
	 *
	 * @var XmlnukeManageUrl
	 */
	protected $_url;

	/**
	 * Full module name (including namespace)
	 * @var string
	 */
	protected $_moduleName;

	/**
	 *
	 * @var LanguageCollection
	 */
	protected $_words = null;

	/**
	 * Measure the time
	 *
	 * @var int
	 */
	protected $_start;
	
	/**
	 * Measure the time
	 *
	 * @var int
	 */
	protected $_end;

	/**
	 * Object to access USERS DB
	 *
	 * @var IUsersBase
	 */
	private $__userdb = null;

	/**
	 * Internal state. If true, ignore USECACHE inside hasInCache
	 * @var bool
	 */
	private $_ignoreCache = false;
	
	/**
	*BaseModule constructor
	*/
	public function BaseModule()
	{}

	/**
	*@param XMLFilenameProcessor $xmlModuleName
	*@param Context $context
	*@param Object $customArgs
	*@return void
	*@desc Add custom setup elements
	*/
	public function Setup($xmlModuleName, $context, $customArgs)
	{
		$this->_start = microtime(true);
		$this->_xmlModuleName = $xmlModuleName;
		$this->_context = $context;
		$this->_cacheFile = new XMLCacheFilenameProcessor($this->_xmlModuleName->ToString(), $this->_context);
		$this->_action = $this->_context->ContextValue("action");
		if ($this->_action == "") 
		{
			$this->_action = $this->_context->ContextValue("acao");
		}
		
		$this->CustomSetup($customArgs);
		$this->defaultXmlnukeDocument = new XmlnukeDocument();
		$this->_url = new XmlnukeManageUrl(URLTYPE::MODULE , $this->_xmlModuleName->ToString());
		$this->_moduleName = $this->_xmlModuleName->ToString();
	}

	/**
	*@param Object $customArgs
	*@return void
	*@desc CustomSetup Imodule interface
	*/
	public function CustomSetup($customArg)
	{}
	
	/**
	*@return LanguageCollection
	*@desc WordCollection Imodule interface
	*/
	public function WordCollection()
	{
		if ($this->_words == null)
		{
			$this->_words = LanguageFactory::GetLanguageCollection($this->_context, LanguageFileTypes::MODULE, $this->_xmlModuleName->ToString());
		}
		return $this->_words;
	}

	/**
	*@return bool
	*@desc hasInCache Imodule interface
	*/
	public function hasInCache()
	{
		return (!$this->_ignoreCache && FileUtil::Exists($this->_cacheFile->FullQualifiedNameAndPath()) && (!$this->_context->getNoCache() || !$this->_context->getReset()));
	}
	
	/**
	 * Routine to determine a name for dynamic caches. If expired the time limit the system reset the cache
	 *
	 * @param int $timeInSeconds
	 */
	protected function validateDynamicCache($timeInSeconds)
	{
		// Retrieve Basic XMLNuke paramenters
		$chavesXmlnuke = array();
		$chavesXmlnuke["site"]= $this->_context->getSite();
		if ($this->_context->ContextValue("module") != "")
		{
			$chavesXmlnuke["module"]= $this->_context->ContextValue("module");
		}
		else
		{
			$chavesXmlnuke["xml"]= $this->_context->getXml();
		}
		$chavesXmlnuke["xsl"] = $this->_context->getXsl();
		$chavesXmlnuke["lang"] = $this->_context->Language()->getName();

		// Exclude common and random parameteres from request
		$exclude = array("phpsessid"=>1, "reset"=>1, "debug"=>1, "nocache"=>1, "x"=>1, "y"=>1, "site"=>1, "xml"=>1, "xsl"=>1, "module"=>1, "__clickevent"=>1, "__postback"=>1) + $_COOKIE;
		$arrRequest = array_diff_key($_REQUEST, $exclude);

		// Create array of parameters from request
		$chaves = array();
		foreach ($arrRequest as $key=>$value) 
		{
			$key = strtolower($key);
			$value = strtolower($value);
			if ( (strpos($key, "imagefield_")===false) )
			{
				$chaves[$key] = $value;
			}
		}
		arsort($chaves);
		
		// Create a final set of chaves to determine the cache file name
		$chaves = $chavesXmlnuke + $chaves; 
		
		$str = "";
		foreach ($chaves as $key=>$value)
		{
			$str .= $key . "=" . $value . "/";
		}
		//Debug::PrintValue($str);
		$this->_cacheFile = new XMLCacheFilenameProcessor(UsersAnyDataSet::getSHAPassword(strtolower($str)), $this->_context);
		
		// Test if cache exists
		$fileControl = $this->_cacheFile->FullQualifiedNameAndPath() . ".control";
		if (file_exists($fileControl))
		{
			//Debug::PrintValue("Have Control File");
			$horaMod = filemtime($fileControl);
			$tempo = intval(time()-$horaMod);

			if ($tempo < 30)
			{
				return;
			}
		}

		$file = $this->_cacheFile->FullQualifiedNameAndPath();
		//Debug::PrintValue($file, $this->_cacheFile);
		
		if (file_exists($file))
		{
			//Debug::PrintValue("Exists");
			$horaMod = filemtime($file);
			$tempo = intval((time()-$horaMod));
			//Debug::PrintValue($tempo);
			if (($tempo > $timeInSeconds) || $this->_context->getReset() || $this->_context->getNoCache() )
			{
				FileUtil::QuickFileWrite($fileControl, $horaMod);
				$this->_ignoreCache = true;
				//Debug::PrintValue("Erased.!");
			}
		}
	}

	/**
	*@return bool - Default is True
	*@desc useCache Imodule interface
	*/
	public function useCache()
	{
		return false;
	}

	/**
	*@return string
	*@desc getFromCache Imodule interface. Implement basic read cache file
	*/
	public function getFromCache()
	{
		if ($this->hasInCache())
		{
			return FileUtil::QuickFileRead($this->_cacheFile->FullQualifiedNameAndPath());
		}
		else
		{
			return "";
		}
	}

	/**
	*@param string $content - XHtml string to be cached
	*@return void
	*@desc saveToCache IModule interface. Implements basic save cache file.
	*/
	public function saveToCache($content)
	{
		FileUtil::QuickFileWrite($this->_cacheFile->FullQualifiedNameAndPath(), $content);
		$this->deleteControlCache();
	}

	/**
	*@return void
	*@desc resetCache IModule interface. saveToCache Implements basic reset cache file.
	*/
	public function resetCache()
	{
		$this->deleteControlCache();
		FileUtil::DeleteFile($this->_cacheFile);
	}

	protected function deleteControlCache()
	{
		$fileControl = $this->_cacheFile->FullQualifiedNameAndPath() . ".control";
		if (file_exists($fileControl))
		{
			FileUtilKernel::DeleteFile($fileControl);
		}
	}

	/**
	*@return PageXml
	*@desc Return PageXml IModule interface. Return a Empty PageXmL object
	*/
	public function CreatePage()
	{
		throw new Exception("You must implement the CreatePage() method.");
	}

	/**
	*@return bool
	*@desc requiresAuthentication IModule interface.
	*/
	public function requiresAuthentication()
	{
		return false;
	}
	
	/**
	 * Return a default class to Handle users.
	 *
	 * @return IUsersBase
	 */
	public function getUsersDatabase()
	{
		return $this->_context->getUsersDatabase(); // For Compatibility Reason
	}

	/**
	*@return bool
	*@desc Base module have some basic tests, like check if user is admin or if user is from current site 
	*and have specific role. This method can be overrided to implement another validations.
	*/
	public function accessGranted()
	{
		$users = $this->getUsersDatabase();
		$currentUser = $users->getUserId($this->_context->authenticatedUserId());
		if(!$currentUser)
		{
			throw new EngineException(753, "I can't find the user");
		}
		if ($currentUser->getField($users->_UserTable->Admin) == "yes")
		{
			return true;
		}
		else
		{
			if ($this->getAccessLevel() != AccessLevel::OnlyAdmin)
			{
				$grantToSite = false;
				$grantToRole = false;
				if ($this->getAccessLevel() == AccessLevel::OnlyAuthenticated) 
                { 
                	return true; 
                }
				
                if (($this->getAccessLevel() == AccessLevel::OnlyCurrentSite) || ($this->getAccessLevel() == AccessLevel::CurrentSiteAndRole))
				{
					$grantToSite = $users->checkUserProperty($this->_context->authenticatedUserId(), $this->_context->getSite(), UserProperty::Site);
				}
				if (($this->getAccessLevel() == AccessLevel::OnlyRole) || ($this->getAccessLevel() == AccessLevel::CurrentSiteAndRole))
				{
					$roles = $this->getRole();
					if (!is_null($roles) && ($roles!=""))
					{
						if (is_array($roles)) 
						{
							foreach ($roles as $oneRule) 
							{
								$grantToRole = $users->checkUserProperty($this->_context->authenticatedUserId(), $oneRule, UserProperty::Role);
								if ($grantToRole) 
								{
									break;
								}
							}
						}
						else 
						{
							$grantToRole = $users->checkUserProperty($this->_context->authenticatedUserId(), $roles, UserProperty::Role);						
						}
					}
				}

				if ($this->getAccessLevel() == AccessLevel::CurrentSiteAndRole)
				{
					return ($grantToSite && $grantToRole);
				}
				else
				{
					return ($grantToSite || $grantToRole);
				}
			}
			else
			{
				return false;
			}
		}
	}

	/**
	*@return string
	*@desc Return AccessLevel for this module
	*For security reasons each module need set the proper access level.
	*/
	public function getAccessLevel()
	{
		return AccessLevel::OnlyAdmin;
	}
	
	/**
	*@throws InsufficientPrivilegeException
	*@return void
	*@desc Process Insufficient Privilege for this module
	*/
	public function processInsufficientPrivilege()
	{
		throw new InsufficientPrivilegeException("You do not have rights to access this feature");
	}

	/**
	*@return string
	*@desc Get rule for this module
	*/
	public function getRole()
	{
		return null;
	}

	/**
	*@return bool
	*@desc This module is admin?
	*/
	public function isAdmin()
	{
		return false;
	}

	public function __destruct()
	{
		$this->_end = microtime(true);
		$result = $this->_end - $this->_start;
		if ($this->_context->getDebugInModule())
		{
			Debug::PrintValue("Total Execution Time: " . $result . " seconds ");
		}
	}
	
	protected $_checkedPermission = array();
	
	public function CurrentUserHasPermission($permission = null)
	{
		if (is_null($permission))
		{
			$permission = $this->getRole();	
		}
		
		if (is_array($permission))
			$checkPerm = join(",", $permission);
		else
			$checkPerm = $permission;
		
		$ok = false;
		if (!array_key_exists($checkPerm, $this->_checkedPermission))
		{
			$users = $this->getUsersDatabase();

			$permArr = explode(",", $checkPerm);
			foreach ($permArr as $value) 
			{
				$ok = $ok || $users->checkUserProperty($this->_context->authenticatedUserId(), $value, UserProperty::Role);
			}
			$this->_checkedPermission[$checkPerm] = $ok;
		}
		else 
		{
			$ok = $this->_checkedPermission[$checkPerm];
		}
		
		return $ok;
	}
	
	/**
	*@desc add a item to menu
	*@param string $title
	*@param string $desc
	*@param string $desc
	*@return void
	*/
	public function addMenuItem($id, $title, $summary, $group = "__DEFAULT__", $permission = null)
	{ 
		// Check Array Of Permission to put MENU
		$ok = (is_null($permission) ? true : $this->CurrentUserHasPermission($permission));
		
		// If is OK, add the menu, otherwise, nothing to do. 
		if ($ok)
		{
			$this->defaultXmlnukeDocument->addMenuItem($id, $title, $summary, $group);
		}
	} 
	
	/**
	@desc Method for process button click and events associated.
	*/
	public function processEvent()
	{
		if ($this->isPostBack() && ($this->_context->ContextValue("__clickevent") != ""))
		{
			$events = explode("|", $this->_context->ContextValue("__clickevent"));
			foreach ($events as $eventName)
			{
				if ($this->_context->ContextValue($eventName) != "")
				{
					$method = new ReflectionMethod(get_class($this), $eventName . "_Event");

					if (!is_null($method))
					{
						// An Error will be throwed if method doesnt exists.
						$method->invoke($this);
					}
				}
			}
		}
	}

	/**
	@desc Bind public string class parameters based on Request Get e Form
	*/
	protected function bindParameteres($instance = null)
	{
		$obj = (is_null($instance) ? $this : $instance);
		$class = new ReflectionClass(get_class($obj));
		
		$properties = $class->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );

		if (!is_null($properties))
		{
			foreach ($properties as $prop)
			{
				$propName = $prop->getName();
				
				// Remove Prefix "_" from Property Name to find a value
				if ($propName[0] == "_")
				{
					$propName = substr($propName, 1);
				}
				
				// If exists value, set it;
				if ($this->_context->ContextValue($propName) != "")
				{
					$method = new ReflectionMethod(get_class($obj), "set" . ucfirst($propName));
					$method->invokeArgs($obj, array($this->_context->ContextValue($propName)));
				}
			}
		}
	}

	
	public function isPostBack()
	{
		return ( $this->_context->ContextValue("__postback") != "" );
	}
	

}
?>
