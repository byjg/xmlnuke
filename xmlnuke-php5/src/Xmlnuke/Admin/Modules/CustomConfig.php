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
namespace Xmlnuke\Admin\Modules;

use Xmlnuke\Core\Admin\NewBaseAdminModule;
use ByJG\AnyDataset\Repository\AnyDataset;
use Xmlnuke\Core\Classes\XmlBlockCollection;
use Xmlnuke\Core\Classes\XmlEasyList;
use Xmlnuke\Core\Classes\XmlFormCollection;
use Xmlnuke\Core\Classes\XmlInputButtons;
use Xmlnuke\Core\Classes\XmlInputHidden;
use Xmlnuke\Core\Classes\XmlInputLabelField;
use Xmlnuke\Core\Classes\XmlInputTextBox;
use Xmlnuke\Core\Classes\XmlnukeText;
use Xmlnuke\Core\Classes\XmlParagraphCollection;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Enum\BlockPosition;
use Xmlnuke\Core\Enum\EasyListType;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;

class CustomConfig extends NewBaseAdminModule
{

	public function CustomConfig()
	{

	}

	public function useCache()
	{
		return false;
	}

	public function getAccessLevel()
	{
		return AccessLevel::OnlyRole;
	}

	public function getRole()
	{
		return "MANAGER";
	}

	public function CreatePage()
	{
		parent::CreatePage();

		$myWords = $this->WordCollection();

		$this->setHelp($myWords->Value("DESCRIPTION"));

		//this.addMenuOption("OK", "module:Xmlnuke.Admin.ManageGroup?action=aqui");
		$this->setTitlePage($myWords->Value("TITLE"));
		//this.addMenuOption("Click here to ERASE ALL cache.", "module:Xmlnuke.Admin.CustomConfig?action=erase");
		//this.addMenuOption("Click here to LIST cache.", "module:Xmlnuke.Admin.CustomConfig?action=list");

		$action = strtolower($this->_action);

		$block = new XmlBlockCollection($myWords->Value("WORKINGAREA"), BlockPosition::Center);
		/*
		  XmlNode paragraph;
		  XmlNode form;
		  XmlNode boxButton;
		 */
		if ($action == "update")
		{
			$nv = array();
			$nv["xmlnuke.SMTPSERVER"] = $this->_context->get("smtpserver");
			$nv["xmlnuke.LANGUAGESAVAILABLE"] = $this->createLanguageString();
			$nv["xmlnuke.SHOWCOMPLETEERRORMESSAGES"] = $this->_context->get("showcompleterrormessages");
			$nv["xmlnuke.LOGINMODULE"] = $this->_context->get("loginmodule");
			$nv["xmlnuke.USERSDATABASE"] = $this->_context->get("usersdatabase");
			$nv["xmlnuke.USERSCLASS"] = $this->_context->get("usersclass");
			$nv["xmlnuke.DEBUG"] = $this->_context->get("txtdebug");
			$nv["xmlnuke.DETECTMOBILE"] = $this->_context->get("txtdetectmobile");
			$nv["xmlnuke.CAPTCHACHALLENGE"] = $this->_context->get("captchachallenge");
			$nv["xmlnuke.CAPTCHALETTERS"] = $this->_context->get("captchaletters");
			$nv["xmlnuke.ENABLEPARAMPROCESSOR"] = $this->_context->get("enableparamprocessor");
			$nv["xmlnuke.USEFULLPARAMETER"] = $this->_context->get("usefullparameter");
			$nv["xmlnuke.PHPLIBDIR"] = $this->_context->get("phplibdir"); # PHP SPECIFIC

			$this->_context->updateCustomConfig($nv);

			$paragraph = new XmlParagraphCollection();
			$paragraph->addXmlnukeObject(new XmlnukeText($myWords->Value("UPDATED"), true));
			$block->addXmlnukeObject($paragraph);
		}

		$form = new XmlFormCollection($this->_context, "module:Xmlnuke.Admin.CustomConfig", $myWords->Value("FORMTITLE"));
		$form->setJSValidate(true);
		$form->setFormName("form");

		$truefalse = array("" => "Use Default", "true" => "True", "false" => "False");

		$form->addXmlnukeObject(new XmlInputHidden("action", "update"));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.ROOTDIR", $this->_context->get("xmlnuke.ROOTDIR")));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.USEABSOLUTEPATHSROOTDIR", $this->_context->get("xmlnuke.USEABSOLUTEPATHSROOTDIR")));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.URLMODULE", $this->_context->get("xmlnuke.URLMODULE")));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.URLXMLNUKEADMIN", $this->_context->get("xmlnuke.URLXMLNUKEADMIN")));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.URLXMLNUKEENGINE", $this->_context->get("xmlnuke.URLXMLNUKEENGINE")));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.DEFAULTPAGE", $this->_context->get("xmlnuke.DEFAULTPAGE")));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.XSLCACHE", $this->_context->get("xmlnuke.XSLCACHE")));
		$form->addXmlnukeObject(new XmlInputTextBox("xmlnuke.SMTPSERVER", "smtpserver", $this->_context->get("xmlnuke.SMTPSERVER"), 30));
		$this->generateLanguageInput($form);
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "showcompleterrormessages", "xmlnuke.SHOWCOMPLETEERRORMESSAGES", $truefalse, $this->getStringBool($this->_context->get("xmlnuke.SHOWCOMPLETEERRORMESSAGES"))));
		$form->addXmlnukeObject(new XmlInputTextBox("xmlnuke.LOGINMODULE", "loginmodule", $this->_context->get("xmlnuke.LOGINMODULE"), 30));
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "usersdatabase", "xmlnuke.USERSDATABASE", $this->getStringConnectionsArray(), $this->_context->get("xmlnuke.USERSDATABASE")));
		$form->addXmlnukeObject(new XmlInputTextBox("xmlnuke.USERSCLASS", "usersclass", $this->_context->get("xmlnuke.USERSCLASS"), 30));
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "txtdetectmobile", "xmlnuke.DETECTMOBILE", $truefalse, $this->getStringBool($this->_context->get("xmlnuke.DETECTMOBILE"))));
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "txtdebug", "xmlnuke.DEBUG", $truefalse, $this->getStringBool($this->_context->get("xmlnuke.DEBUG"))));
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "captchachallenge", "xmlnuke.CAPTCHACHALLENGE", array("easy" => "Easy", "hard" => "Hard"), $this->_context->get("xmlnuke.CAPTCHACHALLENGE")));
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "captchaletters", "xmlnuke.CAPTCHALETTERS", array("5" => "5", "6" => "6", "7" => "7", "8" => "8", "9" => "9", "10" => "10"), $this->_context->get("xmlnuke.CAPTCHALETTERS")));
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "enableparamprocessor", "xmlnuke.ENABLEPARAMPROCESSOR", $truefalse, $this->getStringBool($this->_context->get("xmlnuke.ENABLEPARAMPROCESSOR"))));
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "usefullparameter", "xmlnuke.USEFULLPARAMETER", $truefalse, $this->getStringBool($this->_context->get("xmlnuke.USEFULLPARAMETER"))));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.CACHESTORAGEMETHOD", $this->_context->get("xmlnuke.CACHESTORAGEMETHOD")));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.XMLSTORAGEMETHOD", $this->_context->get("xmlnuke.XMLSTORAGEMETHOD")));
		$form->addXmlnukeObject(new XmlInputLabelField("xmlnuke.XMLNUKEDATA", $this->_context->get("xmlnuke.XMLNUKEDATA")));
		$form->addXmlnukeObject(new XmlInputTextBox("xmlnuke.PHPLIBDIR", "phplibdir", $this->_context->get("xmlnuke.PHPLIBDIR"), 30));

		$boxButton = new XmlInputButtons();
		$boxButton->addSubmit($myWords->Value("TXT_SAVE"));
		$form->addXmlnukeObject($boxButton);

		$block->addXmlnukeObject($form);

		$this->defaultXmlnukeDocument->addXmlnukeObject($block);

		return $this->defaultXmlnukeDocument;
	}

	protected function getLangArray()
	{
		return array(
			'' => '',
			'pt-br=Português (Brasil)' => 'pt-br=Português (Brasil)',
			'en-us=English (United States)' => 'en-us=English (United States)',
			'fr-fr=Français' => 'fr-fr=Français',
			'it-it=Italiano' => 'it-it=Italiano',
			'' => '',
			'ar-dz=جزائري عربي' => 'ar-dz=جزائري عربي',
			'bg-bg=Български' => 'bg-bg=Български',
			'ca-es=Català' => 'ca-es=Català',
			'cs-cz=Čeština' => 'cs-cz=Čeština',
			'da-dk=Dansk' => 'da-dk=Dansk',
			'de-de=Deutsch' => 'de-de=Deutsch',
			'el-gr=Ελληνικά' => 'el-gr=Ελληνικά',
			'en-gb=English (Great Britain)' => 'en-gb=English (Great Britain)',
			'es-es=Español' => 'es-es=Español',
			'et-ee=Eesti' => 'et-ee=Eesti',
			'fi-fi=Suomi' => 'fi-fi=Suomi',
			'gl-gz=Galego' => 'gl-gz=Galego',
			'he-il=עברית' => 'he-il=עברית',
			'hu-hu=Magyar' => 'hu-hu=Magyar',
			'id-id=Bahasa Indonesia' => 'id-id=Bahasa Indonesia',
			'is-is=Íslenska' => 'is-is=Íslenska',
			'ja-jp=Japanese' => 'ja-jp=Japanese',
			'lv-lv=Latviešu' => 'lv-lv=Latviešu',
			'nl-nl=Nederlands' => 'nl-nl=Nederlands',
			'no-no=Norsk' => 'no-no=Norsk',
			'pl-pl=Polski' => 'pl-pl=Polski',
			'pt-pt=Português (Portugal)' => 'pt-pt=Português (Portugal)',
			'ro-ro=Română' => 'ro-ro=Română',
			'ru-ru=Русский' => 'ru-ru=Русский',
			'sk-sk=Slovenčina' => 'sk-sk=Slovenčina',
			'sv-se=Svenska (Sverige)' => 'sv-se=Svenska (Sverige)',
			'th-th=Thai' => 'th-th=Thai',
			'uk-ua=Українська' => 'uk-ua=Українська',
			'zh-cn=Chinese (Simplified)' => 'zh-cn=Chinese (Simplified)',
			'zh-tw=Chinese (Traditional)' => 'zh-tw=Chinese (Traditional)',
		);
	}

	/**
	 * Write Languages Available function
	 *
	 * @param XmlFormCollection $form
	 */
	protected function generateLanguageInput($form)
	{
		$curValueArray = $this->_context->get("xmlnuke.LANGUAGESAVAILABLE");

		foreach ($curValueArray as $key => $value)
		{
			$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "languagesavailable$key", "xmlnuke.LANGUAGESAVAILABLE", $this->getLangArray(), $value));
		}
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "languagesavailable" . ++$key, "xmlnuke.LANGUAGESAVAILABLE", $this->getLangArray()));
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "languagesavailable" . ++$key, "xmlnuke.LANGUAGESAVAILABLE", $this->getLangArray()));
		$form->addXmlnukeObject(new XmlInputHidden("languagesavailable", $key));
	}

	protected function createLanguageString()
	{
		$key = "languagesavailable";
		$qty = intval($_POST[$key]);
		$value = "";
		for ($i = 0; $i <= $qty; $i++)
		{
			if ($_POST[$key . $i] != "")
				$value .= ($value != "" ? "|" : "") . $_POST[$key . $i];
		}
		return $value;
	}

	/**
	 * Enter description here...
	 *
	 */
	protected function getStringConnectionsArray()
	{
		$processor = new AnydatasetFilenameProcessor("_db");
		$processor->UseFileFromAnyLanguage();
		$anydata = new AnyDataset($processor);
		$it = $anydata->getIterator();
		$ret = array();
		$ret[''] = '-- Default UsersAnydataSet --';
		while ($it->hasNext())
		{
			$sr = $it->moveNext();
			$ret[$sr->getField("dbname")] = $sr->getField("dbname");
		}

		return $ret;
	}

	protected function getStringBool($var)
	{
		if (gettype($var) == "boolean")
		{
			return ($var ? "true" : "false");
		}
		else
		{
			return $var;
		}
	}

}

?>