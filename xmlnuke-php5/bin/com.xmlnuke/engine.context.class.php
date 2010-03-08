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

/**
* Context class get data from HttpContext class and Config.php file and put all in propeties and methods to make easy access their contents
* @package Context
*/
class Context
{
	/**
	* @access private
	* @var Context
	*/
	private $_context = null;
	/**
	* @access private
	* @var string
	*/
	private $_XmlNukeVersion = "XMLNuke 3.x PHP5 Edition";
	/**
	* Name Value Collection
	* @access private
	* @var array
	*/
	private $_config = array();
	/**
	* @access private
	* @var string
	*/
	private $_xml = "";
	/**
	* @access private
	* @var string
	*/
	private $_xsl = "";
	/**
	* @access private
	* @var CultureInfo
	*/
	private $_lang = null;
	/**
	* @access private
	* @var string
	*/
	private $_site = "";
	/**
	* @access private
	* @var bool
	*/
	private $_reset = false;
	/**
	* @access private
	* @var bool
	*/
	private $_nocache = false;
	/**
	* @access private
	* @var XmlNukeDB
	*/
	private $_xmlnukedb;
	/**
	* @access private
	* @var string
	*/
	private $_appNameInMemory;
	/**
	* @access private
	* @var string
	*/
	private $_xmlnukepath = "";
	/**
	 * Debug erros in XMLNuke Error Module
	 *
	 * @var bool
	 */
	private $_debug = false;
//	/**
//	*It is necessary, because the Random value was returned the same value (because uses the same seed).
//	* @access private
//	* @var int
//	*/
//	private $_rnd;

	private $_contentType = "";


	/**
	* Context construtor. Read data from HttpContext class and assign default values to main arguments (XML, XSL, SITE and LANG) if doesn't exists.
	* Process Config.php and put into NameValueCollection the make easy access it.
	* @access public
	* @return void
	*/
	public function Context()
	{
		$this->AddCollectionToConfig(Config::getValuesConfig());

		$this->_xsl = $this->getParameter("xsl");
		if ($this->_xsl == "")
		{
			$this->_xsl = $this->ContextValue("xmlnuke.DEFAULTPAGE");
		}

		$this->_xml = $this->getParameter("xml");
		if ($this->_xml == "")
		{
			$this->_xml = "home";
		}

		$this->_site = $this->getParameter("site");
		if ($this->_site == "")
		{
			$this->_site = $this->ContextValue("xmlnuke.DEFAULTSITE");
		}

		$this->_xmlnukepath = $this->ContextValue("xmlnuke.ROOTDIR");
		$this->_reset = ($this->getParameter("reset") != "");
		$this->_nocache = (($this->getParameter("nocache") != "") || (!$this->ContextValue("xmlnuke.ALWAYSUSECACHE")));

		$this->AddCollectionToConfig($_REQUEST);
		$this->AddCollectionToConfig($_SERVER);
		$this->AddSessionToConfig($_SESSION);
		$this->AddCookieToConfig($_COOKIE);

		$this->AddPairToConfig("SELFURLREAL", $_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."&");
		$this->AddPairToConfig("SELFURL", $_SERVER["REQUEST_URI"]);
		$this->AddPairToConfig("ROOTDIR", $this->_xmlnukepath."/".$this-> _site);
		$this->AddPairToConfig("SITE", $this->_site);
		$this->AddPairToConfig("XMLNUKE", $this->_XmlNukeVersion);
		//$this->AddPairToConfig("USERNAME", $this->authenticatedUser());
		//$this->AddPairToConfig("USERID", $this->authenticatedUserId());
		$this->AddPairToConfig("ENGINEEXTENSION", "php");

		$this->readCustomConfig();
		$this->_debug = $this->ContextValue("xmlnuke.DEBUG");
		if (gettype($this->_debug) != "boolean")
		{
			$this->_debug = ($this->_debug == "true");
		}

		ModuleFactory::PhpLibDir($this);

		$lang = $this->getParameter("lang");

		$langAvail = $this->LanguagesAvailable();

		if ($lang == "")
		{

			$lang = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
			if ($lang != null) // MONO 0.26 doesnt understand HTTP_ACCEPT_LANGUAGE
			{
				$langOpt = preg_split("/[,;]/", $lang);
				$i=0;
				$lang = null;
				$langOptLength = count($langOpt);
				while (($lang == null) && ($i<$langOptLength))
				{
					$langTmp = $langOpt[$i++];
					$lang = $langAvail[$langTmp];

					if ($lang == null)
					{
						$langAux = explode("-",$langTmp);
						$langMajor = $langAux[0];

						for($j=0, $langAvailLength = count($langAvail); ( $j<$langAvailLength && ($lang==null) ) ;$j++)
						{
							$langAux = array_keys($langAvail);
							$langAux2 = $langAux[$j];
							$langAux = explode("-",$langAux2);
							if ($langMajor == $langAux[0])
							{
								$lang = $langAux2;
							}
						}
					}
					else
					{
						$lang = $langOpt[--$i];
					}
				}
			}

			// If not found, use Default language. Default language is the FIRST Parameter!
			if ($lang == null)
			{
				$langAux = array_keys($langAvail);
				$lang = $langAux[0];
			}

		}
		else
		{
			// if the current language isnt exists, then select the FIRST Parameter.
			if (($langAvail[$lang] == null) || ($langAvail[$lang] == ""))
			{
				$langAux = array_keys($langAvail);
				$lang = $langAux[0];
			}
		}

		$this->_lang = LocaleFactory::GetLocale($lang, $this);
		$this->_lang->setLanguage($langAvail[$this->_lang->getName()]);
		$this->AddPairToConfig("LANGUAGE", $this->_lang->getName());
		$this->AddPairToConfig("LANGUAGENAME", $this->_lang->getLanguage());

		$langStr = "";
		foreach (array_keys($langAvail) as $key)
		{
			$langStr =$langStr."<a href='".$this->bindXmlnukeUrl( $this->getXml(), $this->getXsl(), $this->getSite(), $key )."'>".$langAvail[$key]."</a> | ";
		}
		$this->AddPairToConfig("LANGUAGESELECTOR", str_replace("&", "&amp;", substr($langStr,0,strlen($langStr)-2)));

		// Adjusts to Run with XMLNukeDB
		$this->_appNameInMemory = "db_".$this->getSite()."_".strtolower($this->Language()->getName());

		$this->_xmlnukedb = new XmlNukeDB($this->XmlHashedDir(), $this->XmlPath(), strtolower($this->Language()->getName()));
		//$this->_xmlnukedb->loadIndex();

		if ($this->ContextValue("logout") != "")
		{
		       $this->MakeLogout();
		}
	}

	/**
	 * Get config debug in module
	 *
	 * @return bool
	 */
	public function getDebugInModule()
	{
		$configDebug = $this->_debug;
		if ($this->ContextValue("debug") == "true")
		{
			$requestDebug = true;
		}
		else
		{
			$requestDebug = false;
		}
		return ($configDebug && $requestDebug);
	}

	/**
	 * Set debug in module with true or false
	 *
	 * @param bool $debug
	 */
	public function setDebugInModule($debug)
	{
		$this->_debug = $debug;
	}

	/**
	* Return a randon number
	* @access public
	* @param int $maxValue Number maximo to range values
	* @return int - Any number
	*/
	public function getRandomNumber($maxValue)
	{
		return rand(0, $maxValue);
	}


	/**
	* Look for a param name into the HttpContext Request already processed.
	* @access public
	* @param string $paramName Param to be looked for
	* @return string Return the param value if exists or an empty string if doesnt exists
	*/
	private function getParameter($paramName)
	{
		if (array_key_exists($paramName, $_REQUEST))
		{
			return str_replace(FileUtil::Slash().FileUtil::Slash(), FileUtil::Slash(), $_REQUEST[$paramName]);
		}
		else
		{
			return "";
		}
	}

	/**
	* @access public
	* @return string Return the current XML page argument
	*/
	public function getXml()
	{
		return $this->_xml;
	}

	/**
	* @access public
	* @param string $value Value to XML page argument
	* @return void
	*/
	public function setXml($value)
	{
		$this->_xml = $value;
	}
	/**
	* @access public
	* @return string Return the current XSL page argument
	*/
	public function getXsl()
	{
		return $this->_xsl;
	}
	/**
	* @access public
	* @param string $value Value to XSL page argument
	* @return void
	*/
	public function setXsl($value)
	{
		$this->_xsl = $value;
	}
	/**
	* @access public
	* @return string Return the current Site page argument
	*/
	public function getSite()
	{
		return $this->_site;
	}
	/**
	* @access public
	* @param string $value Value to SITE page argument
	* @return void
	*/
	public function setSite($value)
	{
		$this->_site = $value;
	}

	/**
	* @access public
	* @desc Return the current Language page argument
	* @return CultureInfo
	*/
	public function Language()
	{
		return $this->_lang;

	}
	/**
	* @access public
	* @return string Return the current Reset page argument
	*/
	public function getReset()
	{
		return $this->_reset;
	}
	/**
	* @access public
	* @param string $value Value to Reset page argument
	* @return void
	*/
	public function setReset($value)
	{
		$this->_reset = $value;
	}

	/**
	* @access public
	* @return string Return the current NoCache page argument
	*/
	public function getNoCache()
	{
		return $this->_nocache;
	}
	/**
	* @access public
	* @param string $value Value to NoCache page argument
	* @return void
	*/
	public function setNoCache($value)
	{
		$this->_nocache = $value;
	}

	/**
	* @access public
	* @return string Return the phisical directory from xmlnuke.ROOTDIR param from Config.php file.
	*/
	private function XmlNukePath()
	{
		if ($this->ContextValue("xmlnuke.USEABSOLUTEPATHSROOTDIR"))
		{
			return FileUtil::AdjustSlashes($this->_xmlnukepath).FileUtil::Slash();
		}
		else
		{
			return realpath($this->_xmlnukepath).FileUtil::Slash();
		}
	}

	/**
	* @desc Return the phisical directory from xmlnuke.ROOTDIR param from Web.Config file.
	* @return string
	*/
	public function SharedRootPath()
	{
		return $this->XmlNukePath() . "shared" . FileUtil::Slash();

	}

	/**
	* @access public
	* @return string Return the root directory where all sites are located.
	*/
	public function SiteRootPath()
	{
		return $this->XmlNukePath()."sites".FileUtil::Slash();
	}

	/**
	* @access public
	* @return string Return the root directory where the current site pages are located.
	*/
	public function CurrentSitePath()
	{
		$externalSiteArray = $this->getExternalSiteDir();
		$externalSite = $externalSiteArray[$this->getSite()];

		if ($externalSite != "")
		{
			return $externalSite.FileUtil::Slash();
		}
		else
		{
			return $this->SiteRootPath().$this->getSite().FileUtil::Slash();
		}
	}

	/**
	* @access public
	* @return string Return the root directory where the current site XML pages are located.
	*/
	public function XmlPath()
	{
		return $this->CurrentSitePath()."xml".FileUtil::Slash();
	}
	/**
	* @access public
	* @return string Return the root directory where the current site XSL pages are located.
	*/
	public function XslPath()
	{
		return $this->CurrentSitePath()."xsl".FileUtil::Slash();
	}

	/**
	* @access public
	* @return string Return the root directory where the current site CACHE pages are located..
	*/
	public function CachePath()
	{

		return $this->CurrentSitePath()."cache".FileUtil::Slash();
	}

	/**
	* @access public
	* @return string Return the root directory where the current site OFFLINE pages are located.
	*/
	public function OfflinePath()
	{
		return $this->CurrentSitePath()."offline".FileUtil::Slash();
	}

	/**
	* @access public
	* @return string Return the virtual path from xmlnuke.URLXMLNUKEENGINE param from Config.php file.
	*/
	public function UrlXmlNukeEngine()
	{
		return $this->joinUrlBase($this->ContextValue("xmlnuke.URLXMLNUKEENGINE"));
	}

	/**
	* @access public
	* @return string Return the virtual path from xmlnuke.URLMODULE param from Config.php file.
	*/
	public function UrlModule()
	{
		return $this->joinUrlBase($this->ContextValue("xmlnuke.URLMODULE"));
	}

	/**
	* @access public
	* @return string Return the virtual path from xmlnuke.URLXMLNUKEADMIN param from Config.php file.
	*/
	public function UrlXmlNukeAdmin()
	{
		return $this->joinUrlBase($this->ContextValue("xmlnuke.URLXMLNUKEADMIN"));
	}

	public function UrlBase()
	{
		return $this->ContextValue("xmlnuke.URLBASE");
	}

	public function joinUrlBase($url)
	{
		$urlBase = $this->UrlBase();
		if ($urlBase != "")
		{
			if ($url[0] == "/")
			{
				$i = strpos($urlBase, "/");
				if (!($i === false))
				{
					$urlBase = substr($urlBase, 0, $i);
				}
			}
			else
			{
				if ($urlBase[strlen($urlBase)-1] != "/")
				{
					$urlBase .= "/";
				}
			}
		}

		return $urlBase . $url;
	}


	/**
	* @access public
	* @param string $relativePath
	* @return string Return the absolute virtual path from relatives virtual paths.
	*/
	public function VirtualPathAbsolute($relativePath)
	{
		if (($relativePath[0] == "/") || (preg_match("/^https?:\/\//", $relativePath)))
		{
			return $relativePath;
		}

		$result = $_SERVER["SCRIPT_NAME"];
		$iPath = strrpos($result,"/");
		if ($iPath !== false)
		{
			$result = substr($result,0,$iPath);
		}
		if ($relativePath{0} == "~")
		{
			return $result.substr($relativePath,1);
		}
		else
		{
			return $result."/".$relativePath;
		}
	}

	/**
	* Access the Context collection and returns the value from a key.
	* @access public
	* @return string
	*/
	public function ContextValue($key)
	{
		$key = strtoupper($key);
		if (array_key_exists($key, $this->_config))
		{
			$value = $this->_config[$key];
			if ($value instanceof IProcessParameter)
			{
				return $value->getParameter();
			}
			else
			{
				return $value;
			}
		}
		else
		{
			return "";
		}
	}

	public function putContextValue($key, $value)
	{
		$this->AddPairToConfig($key, $value);
	}

	/**
	* @access public
	* @return array Return the languages available from xmlnuke.LANGUAGESAVAILABLE from Config.php file.
	*/
	public function LanguagesAvailable()
	{
		$value = $this->ContextValue("xmlnuke.LANGUAGESAVAILABLE");
		$pairs = explode("|",$value);

		$result = array();

		foreach ($pairs as $pair)
		{
			$values = explode('=',$pair);
			$result[$values[0]] = $values[1];
		}
		return $result;
	}

	/**
	* @access public
	* @return string Return XmlNuke version.
	*/
	public function XmlNukeVersion()
	{
		return $this->_XmlNukeVersion;
	}

	/**
	* @access public
	* @return array Return all exists sites and your full paths.
	*/
	public function ExistingSites()
	{
		$sites = FileUtil::RetrieveSubFolders($this->XmlNukePath()."sites");
		$ret = array();
		foreach ($sites as $key=>$value)
		{
			$basename = basename($value);
			if ($basename[0] != "." )
			{
				$ret[] = $value;
			}

		}

		$externalSite = $this->getExternalSiteDir();
		foreach ($externalSite as $key=>$value)
		{
			$ret[] = $key;
		}


		return $ret;
	}

	protected $_externalSiteArray = null;

	/**
	 * @return array()
	 */
	protected function getExternalSiteDir()
	{
		if ($this->_externalSiteArray == null)
		{
			$this->_externalSiteArray = array();
			$externalSiteDir = $this->ContextValue("xmlnuke.EXTERNALSITEDIR");
			if ($externalSiteDir != "")
			{
				$externalSiteDirArray = explode("|", $externalSiteDir);
				foreach ($externalSiteDirArray as $siteItem)
				{
					$siteArray = explode("=", $siteItem);
					$this->_externalSiteArray[$siteArray[0]] = $siteArray[1];
				}
			}
		}
		return $this->_externalSiteArray;
	}

	/**
	* Get information about current context is authenticated.
	* @access public
	* @return bool Return true if authenticated; false otherwise.
	*/
	public function IsAuthenticated()
	{
		return
		   (
			($this->getSession(SESSION_XMLNUKE_AUTHUSER) != "") &&
			($this->getSession(SESSION_XMLNUKE_USERCONTEXT) == $this->ContextValue("xmlnuke.USERSDATABASE"))
		   );
	}

	/**
	* Get the authenticated user name
	* @access public
	* @return string The authenticated username if exists.
	*/
	public function authenticatedUser()
	{
		if ($this->IsAuthenticated())
		{
			return $this->getSession(SESSION_XMLNUKE_AUTHUSER);
		}
		else
		{
			return "";
		}
	}

	public function authenticatedUserId()
	{
		if ($this->IsAuthenticated())
		{
			return $this->getSession(SESSION_XMLNUKE_AUTHUSERID);
		}
		else
		{
			return "";
		}
	}

	/**
	* Make login in XMLNuke Engine
	* @access public
	* @param strgin $user
	* @return void
	*/
	public function MakeLogin($user, $id)
	{
		$this->setSession(SESSION_XMLNUKE_AUTHUSER, $user);
		$this->setSession(SESSION_XMLNUKE_AUTHUSERID, $id);
		$this->setSession(SESSION_XMLNUKE_USERCONTEXT, $this->ContextValue("xmlnuke.USERSDATABASE"));
	}

	/**
	* Make logout from XMLNuke Engine
	* @access public
	* @return void
	*/
	public function MakeLogout()
	{
		session_unset();
	}

	/**
	* Collection to be added
	* @access public
	* @param array $collection Collection to be added
	* @return void
	*/
	private function AddCollectionToConfig($collection)
	{
		foreach(array_keys($collection) as $key)
		{
			$this->AddPairToConfig($key, $collection[$key]);
		}
	}

	/**
	* Collection Session to be added
	* @access public
	* @param array $collection Session Collection to be added
	* @return void
	*/
	private function AddSessionToConfig($collection)
	{
		if (!is_null($collection))
		{
			foreach($collection as $key => $value)
			{
				$this->AddPairToConfig('session.' . $key, $value);
			}
		}
	}

	/**
	* Cookie Collection to be added
	* @access public
	* @param array $collection Cookie Collection to be added
	* @return void
	*/
	private function AddCookieToConfig($collection)
	{
		foreach($collection as $key => $value)
		{
			$this->AddPairToConfig('cookie.' . $key, $value);
		}
	}

	/**
	* @access public
	* Add a single element to _config collection
	* @param string $key
	* @param array $value
	* @return void
	*/
	private function AddPairToConfig($key, $value)
	{
		$this->_config[strtoupper($key)] = $value;
	}
	/**
	* @access public
	* @return XMLNukeDB
	*/
	public function getXMLDataBase()
	{
		return $this->_xmlnukedb;
	}
	/**
	* Nothing today.
	* @access public
	* @return void
	*/
	public function persistXMLDataBaseInMemory()
	{
		/*
		_context.Application.Lock();
		try
		{
		_context.Application.Set(_appNameInMemory, _xmlnukedb);
		}
		finally
		{
		_context.Application.UnLock();
		}
		*/
	}
	/**
	* Redirect to Url.
	* @access public
	* @param string $url
	* @return void
	*/
	public function redirectUrl($url)
	{
		$processor = new ParamProcessor($this);
		$url = $processor->GetFullLink($url);
		$url = str_replace("&amp;", "&", $url);

		// IIS running CGI mode has a bug related to POST and header(LOCATION) to the SAME script.
		// In this environment the behavior expected causes a loop to the same page
		// To reproduce this behavior comment the this and try use any processpage state class
		$isBugVersion = stristr(PHP_OS, "win") && stristr($this->ContextValue("GATEWAY_INTERFACE"), "cgi") && stristr($this->ContextValue("SERVER_SOFTWARE"), "iis");

		ob_clean();
		if (!$isBugVersion)
		{
			header("Location: " . $url);
		}

		echo "<html>";
		echo "<head>";
		echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"1;URL=$url\">";
		echo "<style type='text/css'> ";
		echo "	#logo{";
		echo "		width:32px;";
		echo "		height:32px;";
		echo "		top: 50%;";
		echo "		left: 50%;";
		echo "		margin-top: -16px;";
		echo "		margin-left: -16px;";
		echo "		position:absolute;";
		echo "} </style>";
		echo "</head>";
		echo "<h1></h1>";
		echo "<div id='logo'><a href='$url'><img src='common/imgs/ajax-loader.gif' border='0' title='If this page does not refresh, Click here' alt='If this page does not refresh, Click here' /></a></div>";
		echo "</html>";
		exit;
	}

	/**
	* @access public
	* @param string $name
	* @param string $value
	* @param int $expire (seconds from now)
	* @param int $path (directory into domain in which the cookie will be available on )
	* @return void
	* @desc Add a value in cookie
	*/
	public function addCookie($name, $value, $expire = null, $path = null, $domain = null)
	{
		if (!is_null($expire))
		{
			$expire = time() + $expire;
		}
		setcookie($name, $value, $expire, $path, $domain);
		$this->AddPairToConfig("cookie." . $name, $value);
	}

	/**
	* @access public
	* @param string $name
	* @return void
	* @desc Remove a cookie
	*/
	public function removeCookie($name)
	{
		setcookie ($name, "", time() - 3600);
		unset($_COOKIE[$name]);
		unset($this->_config["cookie." . $name]);
	}

	/**
	* @access public
	* @param string $name
	* @return String
	* @desc Return the value of a cookie
	*/
	public function getCookie($name)
	{
		return $this->ContextValue("cookie." . $name);
	}

	/**
	* @access public
	* @param string $name
	* @param string $value
	* @return String
	* @desc Add a value in session
	*/
	public function setSession($name, $value)
	{
		$_SESSION[strtoupper($name)] = $value;
		$this->AddPairToConfig("session." . $name, $value);
	}

	/**
	* @access public
	* @param string $name
	* @return void
	* @desc Remove a value in this session
	*/
	public function removeSession($name)
	{
		unset($_SESSION[strtoupper($name)]);
		session_unregister(strtoupper($name));
		unset($this->_config["session." . strtoupper($name)]);
	}

	/**
	* @access public
	* @param string $name
	* @return String
	* @desc Return the a value in this session
	*/
	public function getSession($name)
	{
		return $this->ContextValue("session." . strtoupper($name));
	}


	/**
	* This method was created in intention to substitute the outhers three similars methods.
	* @access public
	* @param string $modulename
	* @param string $xsl
	* @param string $site
	* @param string $lang
	* @return string Return the bind Url
	*/
	public function bindModuleUrl($modulename, $xsl="", $site="",  $lang="")
	{
		$queryStart = strpos($modulename, "?");
		if (!($queryStart===false))
		{
			$queryString = "&" . substr($modulename, $queryStart+1);
			$modulename = substr($modulename, 0, $queryStart);
		}

		if (strpos($modulename, "module:") !== false)
		{
			$modulename = substr($modulename, 7);
		}
		elseif (strpos($modulename, "admin:") !== false)
		{
			$modulename = "admin." . substr($modulename, 6);
		}

		if(empty($xsl))
		{
			if ($this->getXsl()=="index")
			{
				$xsl = 	$this->ContextValue("xmlnuke.DEFAULTPAGE");

			}
			else
			{
				$xsl = $this->getXsl();
			}
		}

		if(empty($site))
			$site = $this->getSite();

		if(empty($lang))
			$lang = strtolower($this->Language()->getName());


		$fullLink = $this->ContextValue("xmlnuke.USEFULLPARAMETER");
		if (!$fullLink)
		{
			if ($site == $this->ContextValue("xmlnuke.DEFAULTSITE"))
			{
				$site = "";
			}
			if ($xsl == $this->ContextValue("xmlnuke.DEFAULTPAGE"))
			{
				$xsl = "";
			}
			$array = array_keys($this->LanguagesAvailable());
			if ($lang == $array[0])
			{
				$lang = "";
			}
		}

		$url = $this->UrlModule()."?module=".$modulename;
		$url .= $queryString;
		if ($site != "")
		{
			$url .= "&site=".$site;
		}
		if ($xsl != "")
		{
			$url .= "&xsl=".$xsl;
		}
		if ($lang != "")
		{
			$url .= "&lang=".$lang;
		}

		return $url;
	}


	/**
	* This method was created in intention to substitute the outhers three similars methods.
	* @access public
	* @param string $xml
	* @param string $xsl
	* @param string $site
	* @param string $lang
	* @return string Return the bind Url
	*/
	public function bindXmlnukeUrl($xml, $xsl="", $site="",  $lang="")
	{
		if(empty($xsl))
			$xsl = $this->getXsl();

		if(empty($site))
			$site = $this->getSite();

		if(empty($lang))
			$lang = strtolower($this->Language()->getName());

		return $this->UrlXmlNukeEngine()."?site=".$site."&xml=".$xml."&xsl=".$xsl."&lang=".$lang;
	}

	/**
	* @access public
	* @param array $options
	* @return void
	*/
	public function updateCustomConfig($options)
	{
		//processor.AnydatasetFilenameProcessor
		$configFile = new AnydatasetFilenameProcessor("customconfig", $this);
		$phyFile = $this->CurrentSitePath().$configFile->FullQualifiedName();
		//anydataset.AnyDataSet
		$config = new AnyDataSet($phyFile);
		//anydataset.AnyIterator
		$it = $config->getIterator();
		if ($it->hasNext())
		{
			$config->removeRow(0);
		}

		$config->appendRow();
		foreach( array_keys($options) as $key )
		{
			if (trim($options[$key]) != "")
			{
				$this->AddPairToConfig($key, $options[$key]);
				$config->addField($key, $options[$key]);
			}
		}
		$config->Save($phyFile);
	}
	/**
	* @access public
	* @return void
	*/
	private function readCustomConfig()
	{
		//  |
		//  |  Attention: FilenameProcessor not used because readCustomConfig is fired before
		//  |  setting current language...
		//  v
		//processor.AnydatasetFilenameProcessor configFile = new processor.AnydatasetFilenameProcessor("customconfig", this);

		$phyFile = $this->CurrentSitePath()."customconfig.anydata.xml"; // <--- argh!!
		if (FileUtil::Exists($phyFile))
		{
			$config = new AnyDataSet($phyFile);
			$it = $config->getIterator(null);
			if ($it->hasNext())
			{
				//SingleRow
				$sr = $it->moveNext();
				$fieldNames = $sr->getFieldNames();
				foreach( $fieldNames as $field )
				{
					if ($sr->getField($field) != "")
					{
						$this->AddPairToConfig($field, $sr->getField($field));
					}
				}
			}
		}
	}

	/**
	* @desc Return path to XMLNuke root directory
	* @return string
	*/
	public function SystemRootPath()
	{
		return realpath(".") . FileUtil::Slash();
	}

	public function getXmlnukeURL()
	{
		$protocol = ($this->ContextValue("SERVER_PORT") == 443) ? "https://" : "http://";
		$url = $protocol . $this->ContextValue("HTTP_HOST") . dirname($this->ContextValue("PHP_SELF"));
		if ($url[strlen($url)-1] != '/')
		{
			$url .= "/";
		}
		return $url;
	}

	/**
	 * Enable XMLNuke get parameters like:
	 *
	 * module.php/VIRTUALCOMMAND?a=1
	 *
	 * Virtual Command follow the SLASH and QUERY STRING follow the "?" char
	 */
	public function getVirtualCommand()
	{
		$script = $this->ContextValue("PHP_SELF");
		$name = $this->ContextValue("SCRIPT_NAME");

		$command = substr($script, strlen($name) + 1);
		return $command;
	}

	/**
	 * Return the Field name and the FILENAME for Saving files
	 *
	 * @param bool $systemArray
	 * @return array
	 */
	public function getUploadFileNames($systemArray=false)
	{
		if ($systemArray)
		{
			return $_FILES;
		}
		else
		{
			$ret = array();
			foreach($_FILES as $file => $property)
			{
				$ret[$file] = $property["name"];
			}
		}
		return $ret;
	}

	/**
	 * Process a document Upload
	 *
	 * @param UploadFilenameProcessor $filenameProcessor
	 * @param bool $useProcessorForName
	 * @param string/array $field Contain the filename properties (if Array, or $filename if string)
	 * @param array Valid Extensions
	 * @return Array Filename saved.
	 */
    public function processUpload($filenameProcessor, $useProcessorForName, $field = null)
    {
    	if (!($filenameProcessor instanceof UploadFilenameProcessor))
    	{
    		throw new Exception("processUpload must receive a UploadFilenameProcessor class");
    	}
    	else if (is_null($field))
    	{
    		$ret = array();
			foreach($_FILES as $file => $property)
			{
				$ret[] = $this->processUpload($filenameProcessor, $useProcessorForName, $property);
			}
			return $ret;
		}
		else if (is_string($field))
		{
			$ret = array();
			$ret[] = $this->processUpload($filenameProcessor, $useProcessorForName, $_FILES[$field]);
			return $ret;
		}
		else if (is_array($field))
		{
			if ($useProcessorForName)
			{
				$uploadfile = $filenameProcessor->FullQualifiedNameAndPath();
			}
			else
			{
				$uploadfile = $filenameProcessor->PathSuggested() . FileUtil::Slash() . $field["name"];
			}
			if (move_uploaded_file($field['tmp_name'], $uploadfile))
			{
				return $uploadfile;
			}
			else
			{
			    throw new Exception("Cannot upload file. Raeson: '" . $field['error'] . "'");
			}
		}
		else
		{
			throw new Exception("Something is wrong with Upload file.");
		}
    }

	public function getSuggestedContentType()
	{
		if ($this->_contentType == "")
		{
			$contentType = "text/html";
			if ($this->ContextValue("xmlnuke.CHECKCONTENTTYPE"))
			{
				$filename = new AnydatasetFilenameProcessor("contenttype", $this);
				$anydataset = new AnyDataSet($filename);
				$itf = new IteratorFilter();
				$itf->addRelation("xsl", Relation::Equal, $this->getXsl());
				$it = $anydataset->getIterator($itf);
				if ($it->hasNext())
				{
					$sr = $it->moveNext();
					$contentType = $sr->getField("content-type");
				}
				else
				{
					$filename = new AnydatasetSetupFilenameProcessor("contenttype", $this);
					$anydataset = new AnyDataSet($filename);
					$itf = new IteratorFilter();
					$itf->addRelation("xsl", Relation::Equal, $this->getXsl());
					$it = $anydataset->getIterator($itf);
					if ($it->hasNext())
					{
						$sr = $it->moveNext();
						$contentType = $sr->getField("content-type");
					}
				}
			}
			$this->_contentType = ($contentType == "")?"text/html":$contentType;
		}
		return ($this->_contentType);
	}

	/**
	 * Original code from: Maciej Łebkowski
	 * @param array $mimeTypes
	 * @return <type>
	 */
	function getBestSupportedMimeType($mimeTypes = null)
	{
		// Values will be stored in this array
		$AcceptTypes = Array ();

		// Accept header is case insensitive, and whitespace isn’t important
		$accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
		// divide it into parts in the place of a ","
		$accept = explode(',', $accept);
		foreach ($accept as $a)
		{
			// the default quality is 1.
			$q = 1;
			// check if there is a different quality
			if (strpos($a, ';q='))
			{
				// divide "mime/type;q=X" into two parts: "mime/type" i "X"
				list($a, $q) = explode(';q=', $a);
			}
			// mime-type $a is accepted with the quality $q
			// WARNING: $q == 0 means, that mime-type isn’t supported!
			$AcceptTypes[$a] = $q;
		}
		arsort($AcceptTypes);

		// if no parameter was passed, just return parsed data
		if (!$mimeTypes) return $AcceptTypes;

		$mimeTypes = array_map('strtolower', (array)$mimeTypes);

		// let’s check our supported types:
		foreach ($AcceptTypes as $mime => $q)
		{
		   if ($q && in_array($mime, $mimeTypes)) return $mime;
		}
		// no mime-type found
		return null;
	}


	public function Debug()
	{
		Debug::PrintValue($this->_config);
	}

    public function CacheHashedDir()
    {
        return (strtoupper($this->ContextValue("xmlnuke.CACHESTORAGEMETHOD")) == "HASHED");
    }

    public function XmlHashedDir()
    {
        return (strtoupper($this->ContextValue("xmlnuke.XMLSTORAGEMETHOD")) == "HASHED");
    }

    public function getPostVariables()
    {
    	return $_POST;
    }


    protected $__userdb;

    /**
     *
     * @return IUsersBase
     */
    public function getUsersDatabase()
    {
		if ($this->__userdb == null)
		{
			$class = $this->ContextValue("xmlnuke.USERSCLASS");
			$conn = $this->ContextValue("xmlnuke.USERSDATABASE");
			if ($class != "")
			{
				$this->__userdb = PluginFactory::LoadPlugin($class, "", $this, $conn);
				if (!($this->__userdb instanceof IUsersBase))
				{
					throw new XmlnukeException(0, "Authentication class '$class' must implement IUsersBase interface");
				}
			}
			elseif ($conn == "")
			{
				$this->__userdb = new UsersAnyDataSet($this);
			}
			else
			{
				$this->__userdb = new UsersDBDataSet($this, $conn);
			}
		}

		return $this->__userdb;
    }

}
?>
