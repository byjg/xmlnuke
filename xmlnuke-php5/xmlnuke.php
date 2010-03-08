<?php
	#############################################
	# To create a XMLNuke capable PHP5 page
	#
	require_once("xmlnuke.inc.php");
	#############################################
	

	$applyXslTemplate = ($context->ContextValue("rawxml")=="");
	$selectNodes = $context->ContextValue("xpath");
	
	if (!$applyXslTemplate)
	{
		header("Content-Type: text/xml; charset=utf-8");
	}
	else
	{
		if (detectMobile())
		{
			// WML
			//$contentType = "text/vnd.wap.wml";
			//$context->setXsl("wml");

			// XHTML + MP
			$contentType = $context->getBestSupportedMimeType(array("application/vnd.wap.xhtml+xml", "application/xhtml+xml", "text/html"));
			$context->setXsl("mobile");
		}
		else
		{
			$contentType = $context->getSuggestedContentType();
		}
		header("Content-Type: $contentType; charset=utf-8");
	}
	
	$engine = new XmlNukeEngine($context, $applyXslTemplate, $selectNodes);
	if ($context->ContextValue("remote")!="")
	{
		echo $engine->TransformDocumentRemote($context->ContextValue("remote"));
	}
	elseif ($context->ContextValue("module")=="")
	{
		echo $engine->TransformDocumentNoArgs();
	}
	else 
	{
		processModule($context, $engine);
	}	
	
	//echo "<div align='right'><font face='verdana' size='1'><b>";
	//echo $context->XmlNukeVersion()."<br>Platform: ".PHP_OS;
	//echo "</b></font></div>";
	
	
	function processModule($context, $engine)
	{
		//IModule
		$module = null;
		$moduleName = $context->ContextValue("module");
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
			$module = ModuleFactory::GetModule($moduleName, $context, null);
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
				$kernelError = new XMLNukeErrorModule($context, $firstError, $context->getDebugInModule());
				$kernelError->CreatePage();
				exit();
			}
			else
			{
				$ex->moduleName = $moduleName;
				$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
			}
		}

			
		// Try to execute modules
		// Catch errors from execute
		try
		{
			writePage($engine->TransformDocumentFromModule($module), $context);
		}
		catch (Exception $ex)
		{
			Debug::LogError($moduleName, $ex);
			if ($debug) 
			{
				$kernelError = new XMLNukeErrorModule($context, $ex, $context->getDebugInModule());
				$kernelError->CreatePage();
			}
			else
			{
				if ($ex instanceof PDOException)
				{
					$ex->errorType = ErrorType::DataBase ;
					$ex->showStackTrace = false;
				}
				
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
				writePage($engine->TransformDocumentFromModule($module), $context);
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

	
	function writePage($buffer, $context)
	{
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
