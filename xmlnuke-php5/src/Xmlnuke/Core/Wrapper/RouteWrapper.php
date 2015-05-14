<?php

/*
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

/**
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Wrapper;

use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use InvalidArgumentException;
use Xmlnuke\Core\Classes\BaseSingleton;

class RouteWrapper extends BaseSingleton implements IOutputWrapper
{
	const OK = "OK";
	const METHOD_NOT_ALLOWED = "NOT_ALLOWED";
	const NOT_FOUND = "NOT FOUND";
	
	protected $_defaultMethods = [
			[ "method" => ['GET', 'POST'], "pattern" => '/module/{module}/{id:[0-9]+}/{xsl}', "handler" => 'module' ],
			[ "method" => ['GET', 'POST'], "pattern" => '/module/{module}/{id:[0-9]+}', "handler" => 'module' ],
			[ "method" => ['GET', 'POST'], "pattern" => '/module/{module}/{xsl}', "handler" => 'module' ],
			[ "method" => ['GET', 'POST'], "pattern" => '/module/{module}', "handler" => 'module' ],
			[ "method" => ['GET', 'POST'], "pattern" => '/xml/{xml}', "handler" => 'xml' ],
			[ "method" => ['GET', 'POST'], "pattern" => '/xml/{xml}/{xsl}', "handler" => 'xml' ],

			// Service
			[ "method" => ['GET', 'POST', 'PUT', 'DELETE'], "pattern" => '/{version}/{module}/{action}/{id:[0-9]+}/{secondid}.{output}', "handler" => 'service' ],
			[ "method" => ['GET', 'POST', 'PUT', 'DELETE'], "pattern" => '/{version}/{module}/{action}/{id:[0-9]+}.{output}', "handler" => 'service' ],
			[ "method" => ['GET', 'POST', 'PUT', 'DELETE'], "pattern" => '/{version}/{module}/{id:[0-9]+}/{action}.{output}', "handler" => 'service' ],
			[ "method" => ['GET', 'POST', 'PUT', 'DELETE'], "pattern" => '/{version}/{module}/{id:[0-9]+}.{output}', "handler" => 'service' ],
			[ "method" => ['GET', 'POST', 'PUT', 'DELETE'], "pattern" => '/{version}/{module}/{action}.{output}', "handler" => 'service' ],
			[ "method" => ['GET', 'POST', 'PUT', 'DELETE'], "pattern" => '/{version}/{module}.{output}', "handler" => 'service' ]
		];

	protected $_moduleAlias = [];

	protected $_defaultVersion = '1.0';

	public function getDefaultMethods()
	{
		return $this->_defaultMethods;
	}

	public function setDefaultMethods($methods)
	{
		if (!is_array($methods))
		{
			throw new InvalidArgumentException('You need pass an array');
		}

		foreach ($methods as $key=>$value)
		{
			if (!isset($value['method']) || !isset($value['pattern']))
			{
				throw new InvalidArgumentException('Array has not the valid format');
			}
		}
	}

	public function getDefaultRestVersion()
	{
		return $this->_defaultVersion;
	}

	public function setDefaultRestVersion($version)
	{
		$this->_defaultVersion = $version;
	}

	public function getModuleAlias()
	{
		return $this->_moduleAlias;
	}

	public function addModuleAlias($alias, $module)
	{
		$this->_moduleAlias[$alias] = $module;
	}

	public function Process()
	{
		// Get the URL parameters
		$httpMethod = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];

		// Generic Dispatcher for XMLNuke
		$dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r) {

			foreach ($this->getDefaultMethods() as $route)
			{
			    $r->addRoute(
					$route['method'],
					str_replace('{version}', $this->getDefaultRestVersion(), $route['pattern']),
					isset($route['handler']) ? $route['handler'] : 'default'
				);
			}
		});

		$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

		switch ($routeInfo[0])
		{
			case Dispatcher::NOT_FOUND:

				// ... 404 Not Found
				return self::NOT_FOUND;

			case Dispatcher::METHOD_NOT_ALLOWED:

				// ... 405 Method Not Allowed
				return self::METHOD_NOT_ALLOWED;

			case Dispatcher::FOUND:

				// ... 200 Process:
				$handler = $routeInfo[1];
				$vars = $routeInfo[2];

				// Check Alias
				$moduleAlias = $this->getModuleAlias();
				if (isset($moduleAlias[$vars['module']]))
				{
					$vars['module'] = $moduleAlias[$vars['module']];
				}

				// Define output
				if (!isset($vars['output']))
				{
					$vars['output'] = 'json';
				}

				// Check if output is set
				if ($vars['output'] != 'json' && $vars['output'] != 'xml')
				{
					throw new Exception('Invalid output format. Valid are XML and JSON');
				}

				// Set all default values
				foreach($vars as $key => $value)
				{
					$_REQUEST[$key] = $_GET[$key] = $vars[$key];
				}
				$_REQUEST['raw'] = $_GET['raw'] = isset($vars['output']) ? $vars['output'] : 'json';

				return self::OK;
		}
	}

}
