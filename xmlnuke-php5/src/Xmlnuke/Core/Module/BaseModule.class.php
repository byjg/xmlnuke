<?php

/**
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

namespace Xmlnuke\Core\Module;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Xmlnuke\Core\Admin\IUsersBase;
use Xmlnuke\Core\Cache\ICacheEngine;
use Xmlnuke\Core\Classes\PageXml;
use Xmlnuke\Core\Classes\XmlnukeDocument;
use Xmlnuke\Core\Classes\XmlnukeManageUrl;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Enum\AuthMode;
use Xmlnuke\Core\Enum\SSLAccess;
use Xmlnuke\Core\Enum\URLTYPE;
use Xmlnuke\Core\Enum\UserProperty;
use Xmlnuke\Core\Exception\EngineException;
use Xmlnuke\Core\Exception\InsufficientPrivilegeException;
use Xmlnuke\Core\Exception\NotImplementedException;
use Xmlnuke\Core\Locale\LanguageCollection;
use Xmlnuke\Core\Locale\LanguageFactory;
use Xmlnuke\Core\Processor\XMLCacheFilenameProcessor;
use Xmlnuke\Core\Processor\XMLFilenameProcessor;
use Xmlnuke\Core\Processor\XSLFilenameProcessor;
use Xmlnuke\Util\Debug;

/**
 * BaseModule class is the base for custom module implementation.
 * This class uses cache, save to disk and other functionalities.
 * All custom modules must inherits this class and need to have com.xmlnuke.module namespace.
 * @see com.xmlnuke.module.ModuleFactory
 * @package xmlnuke
 */
abstract class BaseModule implements IModule
{

	/**
	 * @var Context
	 */
	protected $_context;

	/**
	 * Module name
	 * @var XMLFilenameProcessor
	 */
	protected $_xmlModuleName;

	/**
	 * Cache file module
	 * @var XMLCacheFilenameProcessor
	 */
	protected $_cacheFile;

	/**
	 * Action from Request["Action"]
	 * @var string
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
	 * @var XmlnukeDocument
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
	 * BaseModule constructor
	 */
	public function __construct()
	{

	}

	/**
	 * @param XMLFilenameProcessor $xmlModuleName
	 * @param Object $customArgs
	 * @return void
	 * @desc Add custom setup elements
	 */
	public function Setup($xmlModuleName, $customArgs)
	{
		$this->_start = microtime(true);
		$this->_xmlModuleName = $xmlModuleName;
		$this->_context = Context::getInstance();
		$this->_cacheFile = new XMLCacheFilenameProcessor($this->_xmlModuleName->ToString());
		$this->_action = $this->_context->get("action");
		if ($this->_action == "")
		{
			$this->_action = $this->_context->get("acao");
		}

		$this->CustomSetup($customArgs);
		$this->defaultXmlnukeDocument = new XmlnukeDocument();
		$this->_url = new XmlnukeManageUrl(URLTYPE::MODULE, $this->_xmlModuleName->ToString());
		$this->_moduleName = $this->_xmlModuleName->ToString();
	}

	/**
	 * @param Object $customArgs
	 * @return void
	 * @desc CustomSetup Imodule interface
	 */
	public function CustomSetup($customArg)
	{

	}

	/**
	 * @return LanguageCollection
	 * @desc WordCollection Imodule interface
	 */
	public function WordCollection()
	{
		if ($this->_words == null)
		{
			$this->_words = LanguageFactory::GetLanguageCollection(get_called_class());
		}
		return $this->_words;
	}

	/**
	 * @return bool - Default is True
	 * @desc useCache Imodule interface
	 */
	public function useCache()
	{
		return false;
	}

	/**
	 *
	 * @return ICacheEngine
	 */
	public function getCacheEngine()
	{
		return $this->_context->getXSLCacheEngine();
	}

	private $_cacheId = "";

	/**
	 * @return string
	 */
	public function getCacheId()
	{
		if ($this->_cacheId == "")
		{
			// Starting NAME
			$id = strtolower(get_class($this)) . "#" . $this->_context->getSite() . "#" .
				$this->_context->getXsl() . "#" . $this->_context->Language()->getName();

			// Exclude common and random parameteres from request
			$exclude = array("phpsessid" => 1, "reset" => 1, "debug" => 1, "nocache" => 1, "x" => 1, "y" => 1, "site" => 1, "xml" => 1, "xsl" => 1, "module" => 1, "__clickevent" => 1, "__postback" => 1) + $_COOKIE;
			$arrRequest = array_diff_key($_REQUEST, $exclude);

			// Create array of parameters from request
			$keys = array();
			foreach ($arrRequest as $key => $value)
			{
				$key = strtolower($key);
				$value = strtolower($value);
				if ((strpos($key, "imagefield_") === false))
				{
					$keys[] = $key . "=" . $value;
				}
			}
			asort($keys);

			$idParam = implode("/", $keys);

			$this->_cacheId = $id . ":" . md5($idParam);
		}

		return $this->_cacheId;
	}

	/**
	 * @return PageXml
	 * @desc Return PageXml IModule interface. Return a Empty PageXmL object
	 */
	public function CreatePage()
	{
		throw new NotImplementedException("You must implement the CreatePage() method.");
	}

	/**
	 * @return bool
	 * @desc requiresAuthentication IModule interface.
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
	 * @return bool
	 * @desc Base module have some basic tests, like check if user is admin or if user is from current site
	 * and have specific role. This method can be overrided to implement another validations.
	 */
	public function accessGranted()
	{
		$users = $this->getUsersDatabase();
		$currentUser = $users->getUserId($this->_context->authenticatedUserId());
		if (!$currentUser)
		{
			throw new EngineException("Authenticated user id in session does not exists in Users table.", 753);
		}
		if ($users->userIsAdmin($this->_context->authenticatedUserId()))
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
					if (!is_null($roles) && ($roles != ""))
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
	 * @return string
	 * @desc Return AccessLevel for this module
	 * For security reasons each module need set the proper access level.
	 */
	public function getAccessLevel()
	{
		return AccessLevel::OnlyAdmin;
	}

	/**
	 * @throws InsufficientPrivilegeException
	 * @return void
	 * @desc Process Insufficient Privilege for this module
	 */
	public function processInsufficientPrivilege()
	{
		throw new InsufficientPrivilegeException("You do not have rights to access this feature");
	}

	/**
	 * @return string
	 * @desc Get rule for this module
	 */
	public function getRole()
	{
		return null;
	}

	/**
	 * @return bool
	 * @desc This module is admin?
	 */
	public function isAdmin()
	{
		return false;
	}

	public function __destruct()
	{
		$this->_end = microtime(true);
		$result = $this->_end - $this->_start;
		if ($this->_context->getDebugStatus())
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
	 * @desc add a item to menu
	 * @param string $title
	 * @param string $desc
	 * @param string $desc
	 * @return void
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
		if ($this->isPostBack() && ($this->_context->get("__clickevent") != ""))
		{
			$events = explode("|", $this->_context->get("__clickevent"));
			foreach ($events as $eventName)
			{
				if ($this->_context->get($eventName) != "")
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

		$properties = $class->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC);

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
				if ($this->_context->get($propName) != "")
				{
					if ($prop->isPublic())
						$prop->setValue($obj, $this->_context->get($propName));
					else
					{
						$method = new ReflectionMethod(get_class($obj), "set" . ucfirst($propName));
						$method->invokeArgs($obj, array($this->_context->get($propName)));
					}
				}
			}
		}
	}

	public function isPostBack()
	{
		return ( $this->_context->get("__postback") != "" );
	}

	public function requiresSSL()
	{
		return SSLAccess::Wherever;
	}

	protected $_rawRequest = null;

	public function getRawRequest()
	{
		if (is_null($this->_rawRequest))
		{
			$this->_rawRequest = file_get_contents("php://input");
		}

		return $this->_rawRequest;
	}

	public function getAuthMode()
	{
		return AuthMode::Form;
	}

	public function getXsl()
	{
		if (strpos($this->_context->getXsl(), "admin_page"))
		{
			$this->_context->setXsl($this->_context->get("xmlnuke.DEFAULTPAGE"));
		}

		// Default XSL (get from parameter or config)
		$xslFile = new XSLFilenameProcessor($this->_context->getXsl());

		// Forced XSL (used only one time)
		//$xslFile = new XSLFilenameProcessor("myxsl");

		// Forced XSL (use it from now and then)
		//$xsl = "myxsl";
		//$this->_context->setXsl($xsl);
		//$xslFile = new XSLFilenameProcessor($xsl);
		
		return $xslFile;
	}

}

?>
