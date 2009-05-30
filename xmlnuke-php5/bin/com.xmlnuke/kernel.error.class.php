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
*This is base engine exception
*@package com.xmlnuke
*@subpackage xmlnuke.kernel
*/
class XMLNukeErrorModule
{
	/**
	 * Context object
	 *
	 * @var Context
	 */
	protected $context;
	/**
	 * XMLNuke native exception
	 *
	 * @var XMLNukeException
	 */
	protected $exception;
	/**
	 * Exists module?
	 *
	 * @var bool
	 */
	protected $isModule = true;
	protected $variableContents = "";
	
	/**
	 * Constructor
	 *
	 * @param Context $context
	 * @param XMLNukeException $ex
	 * @return XMLNukeErrorModule
	 */
	function XMLNukeErrorModule($context, $ex, $cleanbuffer = true)
	{
		$this->variableContents = "";
		//cleaning buffer to show module erros
		if ($cleanbuffer) {
			$this->variableContents = ob_get_contents();
			ob_end_flush();
			ob_clean();
		}
		$this->context = $context;
		if ($ex->moduleName == "") {
			$this->isModule = false;
			$ex->moduleName = $this->context->getSite();
		}
		$this->exception = $ex;
	}
	/**
	*@return LanguageCollection
	*@desc WordCollection Imodule interface
	*/
	public static function ErrorWordCollection($context)
	{
		$lang = LanguageFactory::GetLanguageCollection($context, LanguageFileTypes::INTERNAL, "xmlnukeerror");
		if (!$lang->loadedFromFile())
		{
			// English Words
			$lang->addText("en-us", "TITLE", "XMLNuke Kernel Errors Module");
			$lang->addText("en-us", "MESSAGE", "The requested module \"{0}\" caused an error. See below for more information.");
			$lang->addText("en-us", "MESSAGESITE", "The requested site \"{0}\" caused an error. See below for more information.");
			$lang->addText("en-us", "CONTACTADMIN", "If this problem persist contact your XMLNuke Administrator.");
			$lang->addText("en-us", "MSG_ERROR_UNKNOW", "Unknow error!");
			$lang->addText("en-us", "TEXT_MODULE", "Module:");
			$lang->addText("en-us", "TEXT_SITE", "Site:");
			$lang->addText("en-us", "TEXT_ERROR_ORIGINAL", "Original Error Message:");
			$lang->addText("en-us", "TEXT_ERROR_RELATED", "Related Error Message:");
			$lang->addText("en-us", "TEXT_ERROR_CODE", "Error Code:");
			$lang->addText("en-us", "TEXT_ERROR_TYPE", "Error Type:");
			$lang->addText("en-us", "TEXT_ERROR_CLASS", "Error Class:");
			$lang->addText("en-us", "TEXT_ERROR_LINE", "line ");
			$lang->addText("en-us", "TEXT_ERROR_FILE", "Error in file:");
			$lang->addText("en-us", "TEXT_ERROR_STACKTRACE", "Stack Trace:");
			// Portuguese Words
			$lang->addText("pt-br", "TITLE", "XMLNuke Kernel Módulo de erros");
			$lang->addText("pt-br", "MESSAGE", "O módulo solicitado \"{0}\" provocou um erro. Veja abaixo mais detalhes");
			$lang->addText("pt-br", "MESSAGESITE", "O site solicitado \"{0}\" provocou um erro. Veja abaixo mais detalhes");
			$lang->addText("pt-br", "CONTACTADMIN", "Se o problema persistir contate o seu administrador XMLNuke");
			$lang->addText("pt-br", "MSG_ERROR_UNKNOW", "Erro desconhecido!");
			$lang->addText("pt-br", "TEXT_MODULE", "Módulo:");
			$lang->addText("pt-br", "TEXT_SITE", "Site:");
			$lang->addText("pt-br", "TEXT_ERROR_ORIGINAL", "Mensagem de Erro Original:");
			$lang->addText("pt-br", "TEXT_ERROR_RELATED", "Mensagem de Erro Relacionado:");
			$lang->addText("pt-br", "TEXT_ERROR_CODE", "Cógido do Erro:");
			$lang->addText("pt-br", "TEXT_ERROR_TYPE", "Tipo do Erro:");
			$lang->addText("pt-br", "TEXT_ERROR_CLASS", "Classe do Erro:");
			$lang->addText("pt-br", "TEXT_ERROR_LINE", "linha ");
			$lang->addText("pt-br", "TEXT_ERROR_FILE", "Erro no arquivo:");
			$lang->addText("pt-br", "TEXT_ERROR_STACKTRACE", "Pilha de Erros:");
		}
		return $lang;
	}
	/**
	 * Generate the HTML Page Error
	 *
	 */
	public function CreatePage()
	{
		$messageParam = "";
		switch ($this->exception->errorType) {
			case ErrorType::DataBase :
				$messageParam = $this->exception->sql;
				break;
		
			default:
				break;
		}
		$messageModule = $this->exception->moduleName;
		$messageIndex = "MESSAGE";
		$moduleTitle = "TEXT_MODULE";
		$myWords = self::ErrorWordCollection($this->context);
		$errorCode = $this->exception->getCode();
		if ($myWords->loadedFromFile()) 
		{
			$messageRelated = $myWords->Value($errorCode, $messageParam);
			if ($messageRelated == "[$errorCode?]") {
				$messageRelated = $myWords->Value("MSG_ERROR_UNKNOW");
			}
		}
		$messageOriginal = $this->exception->getMessage();
		if ($this->isModule == false) {
			$messageModule = $this->context->getSite();
			$messageIndex = "MESSAGESITE";
			$moduleTitle = "TEXT_SITE";
		}
		
		?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>XMLNuke Kernel Errors</title>
<style type="text/css">
<!--
body {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
}
.text {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
}
h1 {
	font-size: 16px;
}
-->
</style>
</head>

<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr height="100">
    <td width="400"><img src="common/imgs/logo_xmlnuke2.gif" width="300" height="83"></td>
    <td align="right" height="80%" width="50%"> <h1><?php print $myWords->Value("TITLE") ?></h1></td>
  </tr>
  <tr>
    <td colspan="2">
      <p class="text"> <?php print $myWords->Value($messageIndex, $messageModule) ?></p>
      <p class="text"><strong><?php print $myWords->Value($moduleTitle) ?></strong> <?php print ucfirst($this->exception->moduleName) ?></p>
      <p class="text"><strong><?php print $myWords->Value("TEXT_ERROR_RELATED") ?></strong> <?php print nl2br($messageRelated) ?></p>
      <?php
      if ($this->exception->errorType == ErrorType::DataBase) {
      	print '<p class="text"><strong>' . $myWords->Value("TEXT_ERROR_SQL") . '</strong> ' . $this->exception->sql .'</p>';
      }
      ?>
      <p class="text"><strong><?php print $myWords->Value("TEXT_ERROR_ORIGINAL") ?></strong> <?php print nl2br($messageOriginal) ?></p>
      <p class="text"><strong><?php print $myWords->Value("TEXT_ERROR_CODE") ?></strong> <?php print $errorCode?></p>
      <p class="text"><strong><?php print $myWords->Value("TEXT_ERROR_FILE") ?></strong> 
      <?php 
      	print basename($this->exception->getFile());
      	print "<strong> (". $myWords->Value("TEXT_ERROR_LINE") . $this->exception->getLine() . ")</strong>";
      ?>
      </p>
      <p class="text"><strong><?php print $myWords->Value("TEXT_ERROR_TYPE") ?></strong> <?php print $this->exception->errorType?></p>
      <p class="text"><strong><?php print $myWords->Value("TEXT_ERROR_CLASS") ?></strong> <?php print $this->exception->erroClass?></p>
    <p class="text"><strong><?php print $myWords->Value("TEXT_ERROR_STACKTRACE") ?></strong></p>
    <p class="text">
    <?php print nl2br($this->exception->getTraceAsString()) ?>
    </p>
    </td>
  </tr>
  <tr >
    <td colspan="2" height="50" class="text">
    	<p class="text"><strong><?php print $myWords->Value("TEXT_AMBIENTVARIABLES") ?></strong></p>
    	<p class="text"><?php print $this->variableContents; ?></p>
    </td>
  </tr>
  <tr >
    <td colspan="2" height="50" class="text">
    	<p class="text"><strong><?php print $myWords->Value("CONTACTADMIN") ?></strong></p>
    </td>
  </tr>
</table>
</body>
</html>
		<?php	
	}
}
?>
