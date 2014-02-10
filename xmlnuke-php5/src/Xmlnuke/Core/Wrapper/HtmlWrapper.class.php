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

use Xmlnuke\Core\Classes\BaseSingleton;
use Xmlnuke\Core\Classes\XmlnukeManageUrl;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Engine\ModuleFactory;
use Xmlnuke\Core\Engine\XmlnukeEngine;
use Xmlnuke\Core\Exception\NotAuthenticatedException;
use Xmlnuke\Core\Exception\ModuleNotFoundException;
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

		if ($output == XmlnukeEngine::OUTPUT_XML)
		{
			header("Content-Type: text/xml; charset=utf-8");
			header("Content-Disposition: inline; filename=\"{$alternateFilename}.xml\";");
		}
		elseif ($output == XmlnukeEngine::OUTPUT_JSON)
		{
			$extraParam["json_function"] = $context->get("jsonfn");
			header("Content-Type: application/json; charset=utf-8");
			header("Content-Disposition: inline; filename=\"{$alternateFilename}.json\";");
		}
		else
		{
			$contentType = array("xsl"=>"", "content-type"=>"", "content-disposition"=>"", "extension"=>"");
			if ($this->detectMobile())
			{
				// WML
				//$contentType = "text/vnd.wap.wml";
				//$context->setXsl("wml");

				// XHTML + MP
				$contentType["content-type"] = $context->getBestSupportedMimeType(array("application/vnd.wap.xhtml+xml", "application/xhtml+xml", "text/html"));
				$context->setXsl("mobile");
			}
			else
			{
				$contentType = $context->getSuggestedContentType();
			}
			header("Content-Type: {$contentType["content-type"]}; charset=utf-8");
			if ($contentType["content-disposition"] != "")
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

		$mobile_browser = '0';

		if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$mobile_browser++;
		}

		if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			$mobile_browser++;
		}

		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
		$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda','xda-');

		if(in_array($mobile_ua,$mobile_agents)) {
			$mobile_browser++;
		}

		if (array_key_exists('ALL_HTTP', $_SERVER) && strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
			$mobile_browser++;
		}

		if (array_key_exists('HTTP_USER_AGENT', $_SERVER) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
			$mobile_browser=0;
		}

		return ($mobile_browser>0);
	}

	/**
	 *
	 * @return BaseProcessResult
	 */
	protected function getProcessResult()
	{
		$context = Context::getInstance();

		$className = $context->get('xmlnuke.POST_PROCESS_RESULT');
		if (empty($class))
			$className = "\Xmlnuke\Core\Processor\BaseProcessResult";

		$class = new $className();

		return $class;
	}

}
