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
		$contentType = $context->getSuggestedContentType();
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
		catch (NotFoundException $ex)
		{
			$firstError = $ex;
			$ex->moduleName = $moduleName;
			$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
		}
		catch (NotAuthenticatedException $ex)
		{
			$s = XmlnukeManageUrl::encodeParam( $_SERVER["REQUEST_URI"] );
			$url = $context->bindModuleUrl($context->ContextValue("xmlnuke.LOGINMODULE"))."&ReturnUrl=".$s;
			// Do not leave empty spaces at begin or end of modules
			// Não deixe espaços em branco no início ou fim dos módulos
			$context->redirectUrl( $url );
		}
		catch (InsufficientPrivilegeException $ex)
		{
			$firstError = $ex;
			$ex->moduleName = $moduleName;
			$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
		}
		catch (KernelException $ex)
		{
			$firstError = $ex;
			$ex->moduleName = $moduleName;
			$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
		}
		catch (Exception $ex)
		{
			$firstError = $ex;
			$ex->moduleName = $moduleName;
			$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
		}
		
		// Try to execute modules
		// Catch errors from execute
		try 
		{
			try
			{
				if ($debug && ($firstError!=null)) {
					throw $ex;
				}
				writePage($engine->TransformDocumentFromModule($module), $context);
			}
			catch (PDOException $ex)
			{
				$ex->errorType = ErrorType::DataBase ;
				$ex->showStackTrace = false;
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				if ($debug) {
					throw $ex;
				}
				$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
				writePage($engine->TransformDocumentFromModule($module), $context);
			}
			catch (XmlUtilException $ex)
			{
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				if ($debug) {
					throw $ex;
				}
				$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
				writePage($engine->TransformDocumentFromModule($module), $context);
			}
			catch (FileUtilException $ex)
			{
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				if ($debug) {
					throw $ex;
				}
				$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
				writePage($engine->TransformDocumentFromModule($module), $context);
			}
			catch (KernelException $ex)
			{
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				if ($debug) {
					throw $ex;
				}
				$module = ModuleFactory::GetModule("LoadError", $context,  $ex );
				writePage($engine->TransformDocumentFromModule($module), $context);
			}
			catch (XMLNukeErrorModule $ex)
			{
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				if ($debug) {
					throw $ex;
				}
				$module = ModuleFactory::GetModule("loaderror", $context,  $ex );
				writePage($engine->TransformDocumentFromModule($module), $context);
			}
			catch (EngineException $ex)
			{
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				if ($debug) {
					throw $ex;
				}
				$module = ModuleFactory::GetModule("loaderror", $context,  $ex );
				writePage($engine->TransformDocumentFromModule($module), $context);
			}
			catch (Exception $ex)
			{
				$ex->moduleName = $moduleName;
				$firstError = $ex;
				if ($debug) {
					throw $ex;
				}
				$module = ModuleFactory::GetModule("loaderror", $context,  $ex );
				writePage($engine->TransformDocumentFromModule($module), $context);
			}
			// Include specific error treatments here.
			// catch (ClassException $ex)
			// {
			// }
		}
		catch (Exception $ex)
		{
			$ex->moduleName = $moduleName;
			if(is_null($firstError))
			{
				$firstError = $ex;
			}
			$kernelError = new XMLNukeErrorModule($context, $firstError, $context->getDebugInModule());
			$kernelError->CreatePage();
		}
		
		
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
