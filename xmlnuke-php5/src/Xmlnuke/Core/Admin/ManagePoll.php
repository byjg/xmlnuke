<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
 *  PHP5 Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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
 * @package xmlnuke
 */
namespace Xmlnuke\Core\Admin;

use Exception;
use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\DBDataset;
use Xmlnuke\Core\Classes\CrudField;
use Xmlnuke\Core\Classes\CrudFieldCollection;
use Xmlnuke\Core\Classes\XmlBlockCollection;
use Xmlnuke\Core\Classes\XmlEasyList;
use Xmlnuke\Core\Classes\XmlFormCollection;
use Xmlnuke\Core\Classes\XmlInputButtons;
use Xmlnuke\Core\Classes\XmlInputGroup;
use Xmlnuke\Core\Classes\XmlInputHidden;
use Xmlnuke\Core\Classes\XmlInputTextBox;
use Xmlnuke\Core\Classes\XmlnukeBreakLine;
use Xmlnuke\Core\Classes\XmlnukeCrudAnydata;
use Xmlnuke\Core\Classes\XmlnukeCrudBase;
use Xmlnuke\Core\Classes\XmlnukeCrudDB;
use Xmlnuke\Core\Classes\XmlnukeText;
use Xmlnuke\Core\Classes\XmlParagraphCollection;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Enum\BlockPosition;
use Xmlnuke\Core\Enum\CustomButtons;
use Xmlnuke\Core\Enum\EasyListType;
use Xmlnuke\Core\Enum\INPUTTYPE;
use Xmlnuke\Core\Enum\ModuleAction;
use Xmlnuke\Core\Enum\MultipleSelectType;
use Xmlnuke\Core\Enum\XmlInputObjectType;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;
use Xmlnuke\Util\FileUtil;

class ManagePoll extends NewBaseAdminModule
{	
	protected $_tblpoll = "";
	protected $_tblanswer = "";
	protected $_tbllastip = "";
	protected $_isdb = false;
	protected $_connection = "";

	protected $_moduleUrl = "module:Xmlnuke.Admin.ManagePoll";
	
	public function ManagePoll()
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
		return array("MANAGER", "EDITOR");
	}

	//Returns: classes.PageXml
	public function CreatePage() 
	{
		parent::CreatePage();
		
		$this->myWords = $this->WordCollection();
		$this->setTitlePage($this->myWords->Value("TITLE"));
		$this->setHelp($this->myWords->Value("DESCRIPTION"));

		$block = new XmlBlockCollection($this->myWords->Value("TITLE"), BlockPosition::Center);
		
		$this->addMenuItem($this->_moduleUrl, $this->myWords->Value("MENULISTPOLLS"), "");
		$this->addMenuItem("module:Xmlnuke.Admin.ManageDBConn", $this->myWords->Value("MENUMANAGEDBCONN"), "");
			
		// Create a NEW config file and SETUP Database
		$configfile = new AnydatasetFilenameProcessor("_poll");
		if (!FileUtil::Exists($configfile))
		{
			$this->CreateSetup($block);
		}
		else 
		{
			$anyconfig = new AnyDataset($configfile->FullQualifiedNameAndPath());
			$it = $anyconfig->getIterator();
			if ($it->hasNext())
			{
				$sr = $it->moveNext();
				$this->_isdb = $sr->getField("dbname") != "-anydata-";
				$this->_connection = $sr->getField("dbname");
				$this->_tblanswer = $sr->getField("tbl_answer");
				$this->_tblpoll = $sr->getField("tbl_poll");
				$this->_tbllastip = $sr->getField("tbl_lastip");
			}
			else 
			{
				$this->CreateSetup($block);
				$this->defaultXmlnukeDocument->addXmlnukeObject($block);
				return $this->defaultXmlnukeDocument->generatePage();
			}
			
			if ($this->_context->get("op") == "")
			{
				$this->ListPoll($block);
			}
			elseif ($this->_context->get("op") == "answer")
			{
				$polldata = explode("|", $this->_context->get("valueid"));
				$this->ListAnswers($block, $polldata[0], $polldata[1]);
			}
			elseif ($this->_context->get("op") == "answernav")
			{
				$this->ListAnswers($block, $this->_context->get("curpoll"), $this->_context->get("curlang"));
			}
		}

		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
		return $this->defaultXmlnukeDocument->generatePage();
	}

	
	/**
	 * Enter description here...
	 *
	 * @param XmlBlockCollection $block
	 */
	protected function CreateSetup($block)
	{
		if ($this->_action == ModuleAction::CreateConfirm)
		{
			$p = new XmlParagraphCollection();
			if ($this->_context->get("type")!="-anydata-")
			{
				try 
				{
					$tblpoll = $this->_context->get("tbl_poll");
					$tblanswer = $this->_context->get("tbl_answer");
					$tbllastip = $this->_context->get("tbl_lastip");
					$suffix = $this->_context->get("tablesuffix");
					
					$dbdata = new DBDataset($this->_context->get("type"));
					$results = array();
					$results[] = $this->CreateTable($dbdata, "create table $tblpoll", "create table $tblpoll (name varchar(15), lang char(5), question varchar(150), multiple char(1), showresults char(1), active char(1)) $suffix");
					$results[] = $this->CreateTable($dbdata, "create table $tblanswer", "create table $tblanswer (name varchar(15), lang char(5), code int, short varchar(10), answer varchar(50), votes int) $suffix");
					//$results[] = $this->CreateTable($dbdata, "create table $tbllastip", "create table $tbllastip (name varchar(15), ip varchar(15)) $suffix");
					$results[] = $this->CreateTable($dbdata, "add primary key poll", "alter table $tblpoll add constraint pk_poll primary key (name, lang);");
					$results[] = $this->CreateTable($dbdata, "add primary key answer", "alter table $tblanswer add constraint pk_answer primary key (name, lang, code)");
					//$results[] = $this->CreateTable($dbdata, "add primary key lastip", "alter table $tbllastip add constraint pk_lastip primary key (name, ip)");
					$results[] = $this->CreateTable($dbdata, "add check poll 1", "alter table $tblpoll add constraint ck_poll_multiple check (multiple in ('Y', 'N'))");
					$results[] = $this->CreateTable($dbdata, "add check poll 2", "alter table $tblpoll add constraint ck_poll_showresults check (showresults in ('Y', 'N'))");
					$results[] = $this->CreateTable($dbdata, "add check poll 3", "alter table $tblpoll add constraint ck_poll_active check (active in ('Y', 'N'))");
					$results[] = $this->CreateTable($dbdata, "add foreign key answer", "alter table $tblanswer add constraint pk_answer_poll foreign key (name) references $tblpoll(name)");
					//$results[] = $this->CreateTable($dbdata, "add foreign key lastip", "alter table $tbllastip add constraint pk_lastip_poll foreign key (name) references $tblpoll(name)");
					
					$block->addXmlnukeObject(new XmlEasyList(EasyListType::UNORDEREDLIST, "", $this->myWords->Value("RESULTSQL"), $results));

					$poll = new AnydatasetFilenameProcessor("_poll");
					$anypoll = new AnyDataset($poll);
					$anypoll->appendRow();
					$anypoll->addField("dbname", $this->_context->get("type"));
					$anypoll->addField("tbl_poll", $tblpoll);
					$anypoll->addField("tbl_answer", $tblanswer);
					$anypoll->addField("tbl_lastip", $tbllastip);
					$anypoll->Save();
				}
				catch (Exception $ex)
				{
					$p->addXmlnukeObject(new XmlnukeText($this->myWords->Value("GOTERROR", $ex->getMessage())));
				}
			}
			else 
			{
				$poll = new AnydatasetFilenameProcessor("_poll");
				$anypoll = new AnyDataset($poll);
				$anypoll->appendRow();
				$anypoll->addField("dbname", "-anydata-");
				$anypoll->Save();
			}
			$p->addXmlnukeObject(new XmlnukeBreakLine());
			$p->addXmlnukeObject(new XmlnukeText($this->myWords->Value("CONFIGCREATED"), true));
			$block->addXmlnukeObject($p);
		}
		else 
		{
			$p = new XmlParagraphCollection();
			$p->addXmlnukeObject(new XmlnukeText($this->myWords->Value("FIRSTTIMEMESSAGE")));
			$block->addXmlnukeObject($p);
			
			$form = new XmlFormCollection($this->_context, $this->_moduleUrl, $this->myWords->Value("CREATESETUP"));
			$form->addXmlnukeObject(new XmlInputHidden("action", ModuleAction::CreateConfirm));
			$db = array("-anydata-"=>$this->myWords->Value("NOTUSEDB"));
			$anydatafile = new AnydatasetFilenameProcessor("_db");
			$anydata = new AnyDataset($anydatafile->FullQualifiedNameAndPath());
			$it = $anydata->getIterator();
			while ($it->hasNext())
			{
				$sr = $it->moveNext();
				$db[$sr->getField("dbname")] = $sr->getField("dbname");
			}
			$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "type", $this->myWords->Value("FORMCONN"), $db));
			
			$inputGroup = new XmlInputGroup($this->_context, "tabledetail", true);
			$inputGroup->setVisible(false);
			
			$text = new XmlInputTextBox($this->myWords->Value("TABLENAME_POLL"), "tbl_poll", "xmlnuke_poll", 20);
			$text->setRequired(true);
			$inputGroup->addXmlnukeObject($text);
			
			$text = new XmlInputTextBox($this->myWords->Value("TABLENAME_ANSWER"), "tbl_answer", "xmlnuke_answer", 20);
			$text->setRequired(true);
			$inputGroup->addXmlnukeObject($text);
			
			$text = new XmlInputTextBox($this->myWords->Value("TABLENAME_LASTIP"), "tbl_lastip", "xmlnuke_lastip", 20);
			$text->setRequired(true);
			$inputGroup->addXmlnukeObject($text);
			
			$text = new XmlInputTextBox($this->myWords->Value("TABLE_SUFFIX"), "tablesuffix", "TYPE INNODB", 30);
			$text->setRequired(true);
			$inputGroup->addXmlnukeObject($text);
			
			$form->addXmlnukeObject($inputGroup);
			
			$buttons = new XmlInputButtons();
			$buttons->addSubmit($this->myWords->Value("CREATESETUPBTN"));
			$form->addXmlnukeObject($buttons);
			
			$block->addXmlnukeObject($form);
			
			$javascript = 
				"
				// ByJG 
				fn_addEvent('type', 'change', enableFields);
				function enableFields(e) {
		    		obj = document.getElementById('type');
		    		showHide_tabledetail(obj.selectedIndex != 0);
				}
				";
			$this->defaultXmlnukeDocument->addJavaScriptSource($javascript, true);
			
		}
			
	}
	
	/**
	 * Enter description here...
	 *
	 * @param DbDataSet $dbdata
	 * @param string $desc
	 * @param string $sql
	 * @return unknown
	 */
	protected function CreateTable($dbdata, $desc, $sql)
	{
		$result = $desc. ": ";
		try 
		{
			$dbdata->execSQL($sql);
			$result .= "OK";
		}
		catch (Exception $ex)
		{
			$result .= $this->myWords->Value("GOTERROR", $ex->getMessage());
		}
		return $result;
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param XmlBlockCollection $block
	 */
	protected function ListPoll($block)
	{
		$yesno = array("Y"=>$this->myWords->Value("YES"), "N"=>$this->myWords->Value("NO"));
		
		$processfields = new CrudFieldCollection();
		
		$field = CrudField::FactoryMinimal("name", $this->myWords->Value("POLLNAME"), 15, true, true);
		$field->key = true;
		$field->dataType = INPUTTYPE::UPPERASCII;
		$processfields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("lang", $this->myWords->Value("POLLLANG"), 5, true, true);
		$field->key = true;
		$field->fieldXmlInput = XmlInputObjectType::SELECTLIST;
		$field->arraySelectList = $this->_context->LanguagesAvailable();
		$processfields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("question", $this->myWords->Value("POLLQUESTION"), 150, true, true);
		$field->maxLength = 150;
		$field->size = 40;
		$processfields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("multiple", $this->myWords->Value("POLLMULTIPLE"), 1, true, true);
		$field->fieldXmlInput = XmlInputObjectType::SELECTLIST;
		$field->arraySelectList = $yesno;
		$processfields->addCrudField($field);
					
		$field = CrudField::FactoryMinimal("showresults", $this->myWords->Value("POLLSHOWRESULTS"), 1, true, true);
		$field->fieldXmlInput = XmlInputObjectType::SELECTLIST;
		$field->arraySelectList = $yesno;
		$processfields->addCrudField($field);
					
		$field = CrudField::FactoryMinimal("active", $this->myWords->Value("POLLACTIVE"), 1, true, true);
		$field->fieldXmlInput = XmlInputObjectType::SELECTLIST;
		$field->arraySelectList = $yesno;
		$processfields->addCrudField($field);
					
		$buttons = new CustomButtons();
		$buttons->alternateText = $this->myWords->Value("SHOWEDITANSWERS");
		$buttons->enabled = true;
		$buttons->icon = "common/editlist/ic_subcategorias.gif";
		$buttons->message = $this->myWords->Value("SHOWEDITANSWERS");
		$buttons->multiple = MultipleSelectType::ONLYONE;
		$buttons->url = $this->_moduleUrl . "?op=answer&curpage=0";
		
		if ($this->_isdb)
		{
			$crud = 
				new XmlnukeCrudDB(
					$this->_context, 
					$processfields, 
					$this->myWords->Value("AVAILABLEPOLLS"), 
					$this->_moduleUrl, 
					array($buttons), 
					$this->_tblpoll, 
					$this->_connection
				);
		}
		else 
		{
			$anydatafile = new AnydatasetFilenameProcessor("poll_list");
			$crud = 
				new XmlnukeCrudAnydata(
					$this->_context, 
					$processfields, 
					$this->myWords->Value("AVAILABLEPOLLS"), 
					$this->_moduleUrl, 
					array($buttons), 
					$anydatafile
				);
		}
			
		$block->addXmlnukeObject($crud);			
	}

	/**
	 * Enter description here...
	 *
	 * @param XmlBlockCollection $block
	 * @param string $pollname
	 */
	protected function ListAnswers($block, $pollname, $lang)
	{
		$yesno = array("Y"=>$this->myWords->Value("YES"), "N"=>$this->myWords->Value("NO"));
		
		$processfields = new CrudFieldCollection();
		
		$field = CrudField::FactoryMinimal("name", $this->myWords->Value("POLLNAME"), 15, true, true);
		$field->key = true;
		$field->editable = $this->_context->get("acao") == XmlnukeCrudBase::ACTION_NEW_CONFIRM;
		$field->defaultValue = $pollname;
		$processfields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("lang", $this->myWords->Value("POLLLANG"), 5, true, true);
		$field->key = true;
		$field->editable = $this->_context->get("acao") == XmlnukeCrudBase::ACTION_NEW_CONFIRM;
		$field->fieldXmlInput = XmlInputObjectType::SELECTLIST;
		$field->arraySelectList = $this->_context->LanguagesAvailable();
		$field->defaultValue = $lang;
		$processfields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("code", $this->myWords->Value("ANSWERCODE"), 3, true, true);
		$field->key = true;
		$field->dataType = INPUTTYPE::NUMBER;
		$processfields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("short", $this->myWords->Value("SHORTTEXT"), 10, true, true);
		$processfields->addCrudField($field);
		
		$field = CrudField::FactoryMinimal("answer", $this->myWords->Value("ANSWERTEXT"), 50, true, true);
		$processfields->addCrudField($field);
					
		$field = CrudField::FactoryMinimal("votes", $this->myWords->Value("ANSWERVOTES"), 3, true, true);
		$field->editable = false;
		$field->defaultValue = 0;
		$field->dataType = INPUTTYPE::NUMBER;
		$processfields->addCrudField($field);

		if ($this->_isdb)
		{
			$crud = 
				new XmlnukeCrudDB(
					$this->_context, 
					$processfields, 
					$this->myWords->Value("AVAILABLEANSWER", $pollname), 
					$this->_moduleUrl, 
					null,
					$this->_tblanswer, 
					$this->_connection
				);
			$crud->setFilter("name = '" . $pollname . "' and lang='" . $lang . "'");
		}
		else 
		{
			$anydatafile = new AnydatasetFilenameProcessor("poll_" . $pollname . "_" . $lang);
			$crud = 
				new XmlnukeCrudAnydata(
					$this->_context, 
					$processfields, 
					$this->myWords->Value("AVAILABLEANSWER", $pollname), 
					$this->_moduleUrl, 
					null,
					$anydatafile
				);
		}
		$crud->addParameter("op", "answernav");
		$crud->addParameter("curpoll", $pollname);
		$crud->addParameter("curlang", $lang);
		
		$block->addXmlnukeObject($crud);	
	}
}
?>
