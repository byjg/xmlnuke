<?php

use Xmlnuke\Core\Classes\XmlnukeManageUrl;
use Xmlnuke\Core\Engine\Context;
use Xmlnuke\Core\Engine\ModuleFactory;
use Xmlnuke\Core\Engine\XmlnukeEngine;
use Xmlnuke\Core\Exception\NotAuthenticatedException;
use Xmlnuke\Core\Exception\NotFoundException;

/**
 * This file contains the minimum requirements to run a website based on XMLNuke.
 * 
 * You do need make any changes in this file. This file is the XMLNuke Front Controller and process all requests to XMLNUke
 * 
 * If you want to use the XMLNuke as a library you must include the file "xmlnuke.inc.php".
 * 
 * You can create also a file called index.php to define the default values to be passed to xmlnuke.php. See the example below:
 *
 * <code>
 * <?php
 * $_REQUEST["site"] = "byjg";
 * $_REQUEST["module"] = "mylib.home";
 * include("xmlnuke.php");
 * ?>
 * </code>
 * @see xmlnuke.inc.php
 * @package xmlnuke
 */

/**
 * It is necessary include the file xmlnuke.inc.php do process the request. 
 */
	#############################################
	# To create a XMLNuke capable PHP5 page
	#
	require_once("xmlnuke.inc.php");
	#############################################

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
		if (detectMobile())
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
	if ($context->get("remote")!="")
	{
		echo $engine->TransformDocumentRemote($context->get("remote"));
	}
	elseif ($context->getModule()=="")
	{
		echo $engine->TransformDocumentNoArgs();
	}
	else 
	{
		processModule($engine);
	}	
	
	//echo "<div align='right'><font face='verdana' size='1'><b>";
	//echo $context->XmlNukeVersion()."<br>Platform: ".PHP_OS;
	//echo "</b></font></div>";
	
	
	function processModule($engine)
	{
		$context = Context::getInstance();
		
		//IModule
		$module = null;
		$moduleName = $context->getModule();
		if ($moduleName=="")
		{
			// Experimental... Not finished...
			$moduleName = $context->getVirtualCommand();
		}
		$firstError = null;
		$debug = $context->getDebugInModule();
		
		// Try load modules
		// Catch errors from permissions and so on.
		try
		{
			$module = ModuleFactory::GetModule($moduleName);			
			writePage($engine->TransformDocumentFromModule($module));
		}
		catch (NotFoundException $ex)
		{
			$module = ModuleFactory::GetModule('Xmlnuke.HandleException', 
				array(
					'TYPE' => 'NOTFOUND',
					'MESSAGE' => 'The module "' . $moduleName . '" was not found.',
					'OBJECT' => $moduleName
				)
			);
			writePage($engine->TransformDocumentFromModule($module));
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
	 * @author http://mobiforge.com/developing/story/lightweight-device-detection-php
	 * @return bool
	 */
	function detectMobile()
	{
		global $context;

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

	
	function writePage($buffer)
	{
		$context = Context::getInstance();
		
		@include("writepage.inc.php");

		$posi = 0;
		$i = strpos($buffer, "<param-", $posi);
		while ($i !== false)
		{
			echo substr($buffer, $posi, $i-$posi);
			$if = strpos($buffer, "</param-", $i);
			
			$tamparam = $if-$i-8;
			$var = substr($buffer, $i+7, $tamparam);
			
			echo $context->get($var);
			
			$posi = $if + $tamparam + 9;
			$i = strpos($buffer, "<param-", $posi);
		}
		
		echo substr($buffer, $posi);
	}
?>
