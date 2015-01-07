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

use Detection\MobileDetect;
use Xmlnuke\Core\Classes\BaseSingleton;
use Xmlnuke\Core\Classes\XmlnukeManageUrl;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Engine\ModuleFactory;
use Xmlnuke\Core\Engine\XmlnukeEngine;
use Xmlnuke\Core\Enum\OutputData;
use Xmlnuke\Core\Exception\ModuleNotFoundException;
use Xmlnuke\Core\Exception\NotAuthenticatedException;
use Xmlnuke\Core\Processor\BaseProcessResult;

class HtmlWrapper extends BaseSingleton implements IOutputWrapper
{
	public function Process()
	{

		/**
		 * @var Context
		 */
		$context = Context::getInstance();

		if ($context->get("remote")!="")
		{
			$engine = $this->createXmlnukeEngine();
			echo $engine->TransformDocumentRemote($context->get("remote"));
		}
		elseif ($context->getModule()=="" && $context->getVirtualCommand() == "")
		{
			$engine = $this->createXmlnukeEngine();
			echo $engine->TransformDocumentNoArgs();
		}
		else
		{
			$this->processModule();
		}
	}

	/**
	 *
	 */
	function processModule()
	{
		$context = Context::getInstance();

		//IModule
		$module = null;
		$moduleName = $context->getModule();
		if ($moduleName=="")
		{
			// Experimental... Not finished...
			$moduleName = $context->getVirtualCommand();
			if ($moduleName == 'admin')
				$moduleName = 'Xmlnuke.Admin.ControlPanel';
		}
		$firstError = null;
		$debug = $context->getDebugStatus();

		// Try load modules
		// Catch errors from permissions and so on.
		$writeResult = $this->getProcessResult();
		try
		{
			$module = ModuleFactory::GetModule($moduleName);
			$engine = $this->createXmlnukeEngine();
			$writeResult->SearchAndReplace($engine->TransformDocumentFromModule($module));
		}
		catch (ModuleNotFoundException $ex)
		{
			$module = ModuleFactory::GetModule('Xmlnuke.HandleException',
				array(
					'TYPE' => 'NOTFOUND',
					'MESSAGE' => 'The module "' . $moduleName . '" was not found.',
					'OBJECT' => $moduleName
				)
			);
			$engine = $this->createXmlnukeEngine();
			$writeResult->SearchAndReplace($engine->TransformDocumentFromModule($module));
		}
		catch (NotAuthenticatedException $ex)
		{
			$s = XmlnukeManageUrl::encodeParam( $_SERVER["REQUEST_URI"] );
			$url = $context->bindModuleUrl($context->get("xmlnuke.LOGINMODULE"))."&ReturnUrl=".$s;
			// Do not leave empty spaces at begin or end of modules
			// Não deixe espaços em branco no início ou fim dos módulos
			$context->redirectUrl( $url );
		}
	}

	/**
	 *
	 * @return XmlnukeEngine
	 */
	public function createXmlnukeEngine()
	{
		$context = Context::getInstance();

		$selectNodes = $context->get("xpath");
		$alternateFilename = str_replace(".", "_", ($context->get("fn") != "" ? $context->get("fn") : ($context->getModule() != "" ? $context->getModule() : $context->getXml())));
		$extraParam = array();
		$output = $context->getOutputFormat();

		if ($output == OutputData::Xml)
		{
			header("Content-Type: text/xml; charset=utf-8");
			header("Content-Disposition: inline; filename=\"{$alternateFilename}.xml\";");
		}
		elseif ($output == OutputData::Json)
		{
			$extraParam["json_function"] = $context->get("jsonfn");
			header("Content-Type: application/json; charset=utf-8");
			header("Content-Disposition: inline; filename=\"{$alternateFilename}.json\";");
		}
		else
		{
			$contentType = array("xsl"=>"", "content-type"=>"", "content-disposition"=>"", "extension"=>"");
			
			// Check if is Mobile
			if ($this->detectMobile())
			{
				$context->setXsl("mobile");
			}
			$contentType = $context->getSuggestedContentType();

			// Get the best content-type for it
			if (!is_array($contentType["content-type"]))
			{
				$bestContentType = $contentType["content-type"];
			}
			else
			{
				$negContentType = new \Negotiation\FormatNegotiator();
				$bestContent = $negContentType->getBest($_SERVER['HTTP_ACCEPT'], $contentType["content-type"]);
				if (!is_null($bestContent))
				{
					$bestContentType = $bestContent->getValue();
				}
				else
				{
					$bestContentType = "text/html";
				}
			}

			// Write Headers
			header("Content-Type: {$bestContentType}; charset=utf-8");
			if (isset($contentType["content-disposition"]))
			{
				header("Content-Disposition: {$contentType["content-disposition"]}; filename=\"{$alternateFilename}.{$contentType["extension"]}\";");
			}
		}

		$engine = new XmlnukeEngine($context, $output, $selectNodes, $extraParam);

		return $engine;
	}

	/**
	 *
	 * @author http://mobiforge.com/developing/story/lightweight-device-detection-php
	 * @return bool
	 */
	protected function detectMobile()
	{
		$context = Context::getInstance();

		if (!$context->get("xmlnuke.DETECTMOBILE"))
		{
			return false;
		}

		if ($context->get('xmlmobile') != '')
		{
			$context->setSession('xmlnuke.USEMOBILE', $context->get('xmlmobile') == "true");
		}

		if ($context->getSession('xmlnuke.USEMOBILE') === false  || $context->getSession('xmlnuke.USEMOBILE') === true)
		{
			return $context->getSession('xmlnuke.USEMOBILE');
		}

		$detect = new MobileDetect();
		// Any mobile device (phones or tablets).
		return $detect->isMobile();
	}

	/**
	 *
	 * @return BaseProcessResult
	 */
	protected function getProcessResult()
	{
		$context = Context::getInstance();

		$className = $context->get('xmlnuke.POST_PROCESS_RESULT');
		if (empty($className))
			$className = "\Xmlnuke\Core\Processor\BaseProcessResult";

		$class = new $className();

		return $class;
	}

}
