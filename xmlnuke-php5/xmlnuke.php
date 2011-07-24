<?php
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
	

	$selectNodes = $context->ContextValue("xpath");
	$alternateFilename = str_replace(".", "_", ($context->getModule() != "" ? $context->getModule() : $context->getXml()));
	if ($context->ContextValue("rawxml")!="")
	{
		$output = "xml";
		header("Content-Type: text/xml; charset=utf-8");
		header("Content-Disposition: inline; filename=\"{$alternateFilename}.xml\";");
	}
	elseif ($context->ContextValue("rawjson")!="")
	{
		$output = "json";
		header("Content-Type: application/json; charset=utf-8");
		header("Content-Disposition: inline; filename=\"{$alternateFilename}.json\";");
	}
	else
	{
		$output = "";
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
	
	$engine = new XmlNukeEngine($context, $output, $selectNodes);
	if ($context->ContextValue("remote")!="")
	{
		echo $engine->TransformDocumentRemote($context->ContextValue("remote"));
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
		}
		catch (NotAuthenticatedException $ex)
		{
			$s = XmlnukeManageUrl::encodeParam( $_SERVER["REQUEST_URI"] );
			$url = $context->bindModuleUrl($context->ContextValue("xmlnuke.LOGINMODULE"))."&ReturnUrl=".$s;
			// Do not leave empty spaces at begin or end of modules
			// Não deixe espaços em branco no início ou fim dos módulos
			$context->redirectUrl( $url );
		}
		catch (Exception $ex)
		{
			if ($debug) 
			{
				Debug::LogError($moduleName, $ex);
				$kernelError = new XMLNukeErrorModule($firstError, $context->getDebugInModule());
				$kernelError->CreatePage();
				exit();
			}
			else
			{
				$ex->moduleName = $moduleName;
				$module = ModuleFactory::GetModule("LoadError", $ex );
			}
		}

			
		// Try to execute modules
		// Catch errors from execute
		try
		{
			writePage($engine->TransformDocumentFromModule($module));
		}
		catch (Exception $ex)
		{
			Debug::LogError($moduleName, $ex);
			if ($debug) 
			{
				$kernelError = new XMLNukeErrorModule($ex, $context->getDebugInModule());
				$kernelError->CreatePage();
			}
			else
			{
				if ($ex instanceof PDOException)
				{
					$ex->errorType = ErrorType::DataBase ;
					$ex->showStackTrace = true;
				}
				
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				
				try
				{
					$module = ModuleFactory::GetModule("LoadError", $ex );
					writePage($engine->TransformDocumentFromModule($module));
				}
				catch (Exception $ex)
				{
					echo "Fatal Error: [" . get_class($ex) . "] " . $ex->getMessage() . "<br/>File: " . basename($ex->getFile()) . " at " . $ex->getLine();
				}
				
			}
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

		if (!$context->ContextValue("xmlnuke.DETECTMOBILE"))
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

		if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
			$mobile_browser++;
		}

		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
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
			
			echo $context->ContextValue($var);
			
			$posi = $if + $tamparam + 9;
			$i = strpos($buffer, "<param-", $posi);
		}
		
		echo substr($buffer, $posi);
	}

?>
