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

class Sample extends BaseModule
{
	/**
	 * XmlnukeDocument
	 *
	 * @var XmlnukeDocument
	 */
	protected $_document;

	/**
	 * LanguageCollection
	 *
	 * @var LanguageCollection
	 */
	protected $_myWords;

	/**
	 * Default constructor
	 *
	 * @return Sample
	 */
	public function Sample()
	{}

	/**
	 *  Return the LanguageCollection used in this module
	 *
	 * @return LanguageCollection
	 */
	public function WordCollection()
	{
		$myWords = parent::WordCollection();
		if (!$myWords->loadedFromFile())
		{
			// English Words
			$myWords->addText("en-us", "TITLE", "Module Sample");
			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Exemplo");
		}
		return $myWords;
	}

	/**
	 * Returns if use cache
	 *
	 * @return False
	 */
	public function useCache()
	{
		return false;
	}

	/**
	 * Output error message
	 *
	 * @return PageXml object
	 */
	public function CreatePage()
	{
		$this->_myWords = $this->WordCollection();

		$this->_document = new XmlnukeDocument($this->_myWords->Value("TITLE"), $this->_myWords->Value("ABSTRACT"));

		$linkModule = "url://module:sample";

		$this->_document->setMenuTitle($this->_myWords->Value("OPTIONMODULE"));

		$this->_document->addMenuItem($linkModule."?op=1", $this->_myWords->Value("OBJECT"), $this->_myWords->Value("DESCOBJECT"));
		$this->_document->addMenuItem($linkModule."?op=2", $this->_myWords->Value("FORM"), $this->_myWords->Value("DESCFORM"));
		$this->_document->addMenuItem($linkModule."?op=3", $this->_myWords->Value("EDITLIST"), $this->_myWords->Value("DESCEDITLIST"));
		$this->_document->addMenuItem($linkModule."?op=4", $this->_myWords->Value("ANYDATASET"), $this->_myWords->Value("DESCANYDATASET"));
		$this->_document->addMenuItem($linkModule."?op=5", $this->_myWords->Value("DATABASE"), $this->_myWords->Value("DESCDATABASE"));
		$this->_document->addMenuItem($linkModule."?op=6", $this->_myWords->Value("UPLOAD"), $this->_myWords->Value("DESCUPLOAD"));
		$this->_document->addMenuItem($linkModule."?op=7", $this->_myWords->Value("XMLDATASET"), $this->_myWords->Value("DESCXMLDATASET"));
		$this->_document->addMenuItem($linkModule."?op=8", $this->_myWords->Value("TEXTFILEDATASET"), $this->_myWords->Value("DESTEXTFILEDATASET"));
		$this->_document->addMenuItem($linkModule."?op=9", $this->_myWords->Value("XMLCHART"), $this->_myWords->Value("DESCXMLCHART"));
		$this->_document->addMenuItem($linkModule."?op=10",$this->_myWords->Value("TABVIEW"), $this->_myWords->Value("DESCTABVIEW"));
		$this->_document->addMenuItem($linkModule."?op=11",$this->_myWords->Value("DUALLIST"), $this->_myWords->Value("DESCDUALLIST"));
		$this->_document->addMenuItem($linkModule."?op=12",$this->_myWords->Value("FAQ"), $this->_myWords->Value("DESCFAQ"));
		$this->_document->addMenuItem($linkModule."?op=13",$this->_myWords->Value("AJAXPOST"), $this->_myWords->Value("DESCAJAXPOST"));
		$this->_document->addMenuItem($linkModule."?op=14",$this->_myWords->Value("AUTOSUGGEST"), $this->_myWords->Value("DESCAUTOSUGGEST"));
		$this->_document->addMenuItem($linkModule."?op=15",$this->_myWords->Value("TREEVIEW"), $this->_myWords->Value("DESCTREEVIEW"));
		$this->_document->addMenuItem($linkModule."?op=16",$this->_myWords->Value("SORTABLE"), $this->_myWords->Value("DESCSORTABLE"));
		$this->_document->addMenuItem($linkModule."?op=17",$this->_myWords->Value("CALENDAR"), $this->_myWords->Value("DESCCALENDAR"));
		$this->_document->addMenuItem($linkModule."?op=18",$this->_myWords->Value("UIALERT"), $this->_myWords->Value("DESCUIALERT"));
		$this->_document->addMenuItem($linkModule."?op=19",$this->_myWords->Value("MEDIAGALLERY"), $this->_myWords->Value("DESCMEDIAGALLERY"));

		$block = new XmlBlockCollection($this->_myWords->Value("MODULE"), BlockPosition::Center);

		$paragraph = new XmlParagraphCollection();

		$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("DESCMODULE")));
		$paragraph->addXmlnukeObject(new XmlnukeBreakLine());
		$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("SELECTOPTION")));
		$block->addXmlnukeObject($paragraph);
		$this->_document->addXmlnukeObject($block);

		$option = $this->_context->ContextValue("op");

		switch ($option)
		{
			case 1:
				{
					$this->actionCreateObject();
					break;
				}
			case 2:
				{
					$this->actionForm();
					break;
				}
			case 3:
				{
					$this->actionEditList();
					break;
				}
			case 4:
				{
					$this->actionEditAnydataSet();
					break;
				}
			case 5:
				{
					$this->actionEditDB();
					break;
				}
			case 6:
				{
					$this->actionUpload();
					break;
				}
			case 7:
				{
					$this->Opcao7();
					break;
				}
			case 8:
				{
					$this->Opcao8();
					break;
				}
			case 9:
				{
					$this->Opcao9();
					break;
				}
			case 10:
				{
					$this->Opcao10();
					break;
				}
			case 11:
				{
					$this->Opcao11();
					break;
				}
			case 12:
				{
					$this->Opcao12();
					break;
				}
			case 13:
				{
					$this->Opcao13();
					break;
				}
			case 14:
				{
					$this->Opcao14();
					break;
				}
			case 15:
				{
					$this->Opcao15();
					break;
				}
			case 16:
				{
					$this->Opcao16();
					break;
				}
			case 17:
				{
					$this->Opcao17();
					break;
				}
			case 18:
				{
					$this->Opcao18();
					break;
				}
			case 19:
				{
					$this->Opcao19();
					break;
				}
		}
		return $this->_document->generatePage();
	}

	/**
	 * Show the Create Object Exemple
	 *
	 */
	protected function actionCreateObject()
	{
		$blockCenter = new XmlBlockCollection($this->_myWords->Value("OBJECT"), BlockPosition::Center);

		$breakLine = new XmlnukeBreakLine();

		$firstParagraph = new XmlParagraphCollection();
		$firstParagraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("OBJECTTEXT1")));
		$firstParagraph->addXmlnukeObject($breakLine);
		$firstParagraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("OBJECTTEXT2"), true, true, true, true));
		$firstParagraph->addXmlnukeObject($breakLine);
		$firstParagraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("OBJECTTEXT3")));

		$secondParagraph = new XmlParagraphCollection();
		$secondParagraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("OBJECTTEXT4")));
		$secondParagraph->addXmlnukeObject($breakLine);
		$secondParagraph->addXmlnukeObject($breakLine);

		$xmlnukeImage = new XmlnukeImage("common/imgs/logo_xmlnuke.gif");
		$link = new XmlAnchorCollection("engine:xmlnuke", "");
		$link->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("CLICK")." -->"));
		$link->addXmlnukeObject($breakLine);
		$link->addXmlnukeObject($xmlnukeImage);
		$link->addXmlnukeObject($breakLine);
		$link->addXmlnukeObject(new XmlnukeText(" -- ".$this->_myWords->Value("CLICK")));
		$secondParagraph->addXmlnukeObject($link);
		$secondParagraph->addXmlnukeObject($breakLine);

		$thirdParagraph = new XmlParagraphCollection();
		$arrayOptions = array();
		$arrayOptions["OPTION1"] =  $this->_myWords->Value("OPTIONTEST1");
		$arrayOptions["OPTION2"] =  $this->_myWords->Value("OPTIONTEST2");
		$arrayOptions["OPTION3"] =  $this->_myWords->Value("OPTIONTEST3");
		$arrayOptions["OPTION4"] =  $this->_myWords->Value("OPTIONTEST4");
		$thirdParagraph->addXmlnukeObject(new XmlEasyList(EasyListType::UNORDEREDLIST, "name", "caption", $arrayOptions, "OP3"));

		$blockCenter->addXmlnukeObject($firstParagraph);
		$blockCenter->addXmlnukeObject($secondParagraph);
		$blockCenter->addXmlnukeObject($thirdParagraph);

		$blockLeft = new XmlBlockCollection($this->_myWords->Value("BLOCKLEFT"), BlockPosition::Left);
		$blockLeft->addXmlnukeObject($firstParagraph);

		$blockRight = new XmlBlockCollection($this->_myWords->Value("BLOCKRIGHT"), BlockPosition::Right);
		$blockRight->addXmlnukeObject($firstParagraph);

		$this->_document->addXmlnukeObject($blockCenter);
		$this->_document->addXmlnukeObject($blockLeft);
		$this->_document->addXmlnukeObject($blockRight);
	}

	/**
	 * Show The Form Exemple
	 *
	 */
	protected function actionForm()
	{
		$blockCenter = new XmlBlockCollection($this->_myWords->Value("FORM"), BlockPosition::Center);

		$breakLine = new XmlnukeBreakLine();

		$paragraph = new XmlParagraphCollection();
		$blockCenter->addXmlnukeObject($paragraph);

		$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("FORMTEXT")));

		$form = new XmlFormCollection($this->_context, "module:sample", $this->_myWords->Value("FORMEDIT"));
		$paragraph->addXmlnukeObject($form);

		$form->setJSValidate(true);
		$form->addXmlnukeObject(new XmlInputHidden("op", "2"));
		$form->addXmlnukeObject(new XmlInputLabelField("Caption", $this->_myWords->Value("VALUE")));

		$text = new XmlInputTextBox($this->_myWords->Value("FIELDREQUIRED"), "field1", "");
		$text->setRequired(true);
		$form->addXmlnukeObject($text);

		$text = new XmlInputDateTime("Date Picker", "date1", DATEFORMAT::DMY, false);
		$form->addXmlnukeObject($text);

		$text = new XmlInputDateTime("Date Picker", "date2", DATEFORMAT::YMD, true);
		$form->addXmlnukeObject($text);

		$text = new XmlInputTextBox($this->_myWords->Value("FIELDEMAIL"), "field2", "");
		$text->setRequired(true);
		$text->setDataType(INPUTTYPE::EMAIL);
		$form->addXmlnukeObject($text);

		$form->addXmlnukeObject(new XmlInputMemo($this->_myWords->Value("MEMO"), "field3", $this->_myWords->Value("VALUE")));
		$form->addXmlnukeObject(new XmlInputCheck($this->_myWords->Value("CHECKBOX"), "check1", $this->_myWords->Value("VALUE")));

		$inputCheck = new XmlInputCheck($this->_myWords->Value("CAPTIONREADONLY"), "check2", $this->_myWords->Value("VALUE"));
		$inputCheck->setChecked(true);
		$inputCheck->setReadOnly(true);
		$form->addXmlnukeObject($inputCheck);

		$inputTextBox = new XmlInputTextBox($this->_myWords->Value("INPUTREADONLY"), "field4", $this->_myWords->Value("VALUE"));
		$inputTextBox->setReadOnly(true);
		$form->addXmlnukeObject($inputTextBox);

		$buttons = new XmlInputButtons();
		$buttons->addSubmit($this->_myWords->Value("SUBMIT"), "bs");
		$buttons->addReset($this->_myWords->Value("RESET"), "br");
		$buttons->addButton($this->_myWords->Value("BUTTON"), "bt", "javascript:alert('ok')");
		$form->addXmlnukeObject($buttons);

		$this->_document->addXmlnukeObject($blockCenter);
	}

	/**
	 * EditList
	 *
	 */
	protected function actionEditList()
	{
		$blockCenter = new XmlBlockCollection($this->_myWords->Value("EDITLIST"), BlockPosition::Center);

		$breakLine = new XmlnukeBreakLine();

		$firstParagraph = new XmlParagraphCollection();
		$firstParagraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("DESCEDITLISTTEXT")));
		$firstParagraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("DESCEDITLISTTEXT2"), true, false, false));
		$firstParagraph->addXmlnukeObject($breakLine);

		$guestbookFile = new AnydatasetFilenameProcessor("guestbook", $this->_context);
		$guestbook = new AnyDataSet($guestbookFile);
		$iterator = $guestbook->getIterator();

		$thirdParagraph = new XmlParagraphCollection();
		$editList = new XmlEditList($this->_context, $this->_myWords->Value("CONTENTBOOK"), "module:sample");
		$editList->setDataSource($iterator);
		$editList->addParameter("op", "3");

		$field = new EditListField();
		$field->fieldData = "frommail";
		$editList->addEditListField($field);

		$field = new EditListField();
		$field->fieldData = "fromname";
		$field->editlistName = $this->_myWords->Value("TXT_NAME");
		$editList->addEditListField($field);

		$field = new EditListField();
		$field->fieldData = "frommail";
		$field->editlistName = $this->_myWords->Value("TXT_EMAIL");
		$editList->addEditListField($field);

		$field = new EditListField();
		$field->fieldData = "message";
		$field->editlistName = $this->_myWords->Value("MESSAGE");
		$editList->addEditListField($field);

		$customButton = new CustomButtons();
		$customButton->action = "acaocustomizada";
		$customButton->enabled = true;
		$customButton->alternateText = "Texto alternativo da ação";
		$url = new XmlnukeManageUrl(URLTYPE::MODULE , "sample");
		$url->addParam("op", 3);
		$customButton->url = htmlentities($url->getUrlFull($this->_context));
		$customButton->icon = "common/editlist/ic_custom.gif";
		$editList->setCustomButton($customButton);
		$editList->setPageSize(3, 0);
		$editList->setEnablePage(true);
		$thirdParagraph->addXmlnukeObject($editList);

		$secondParagraph = new XmlParagraphCollection();
		$secondParagraph->addXmlnukeObject(new XmlnukeText("Ação selecionada: ", true, false, false));
		$secondParagraph->addXmlnukeObject(new XmlnukeText($this->_action));
		$secondParagraph->addXmlnukeObject($breakLine);
		$secondParagraph->addXmlnukeObject(new XmlnukeText("Valor selecionado: ", true, false, false));
		$secondParagraph->addXmlnukeObject(new XmlnukeText($this->_context->ContextValue("valueid")));

		$blockCenter->addXmlnukeObject($firstParagraph);
		$blockCenter->addXmlnukeObject($thirdParagraph);

		if ($this->_action != "")
		{
			$blockCenter->addXmlnukeObject($secondParagraph);
		}

		$this->_document->addXmlnukeObject($blockCenter);
	}

	/**
	 * Edit AnydataSet
	 *
	 */
	protected function actionEditAnydataSet()
	{
		$blockCenter = new XmlBlockCollection($this->_myWords->Value("ANYDATASET"), BlockPosition::Center);

		$breakline = new XmlnukeBreakLine();

		$paragraph = new XmlParagraphCollection();
		$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("DESCANYDATASETTEXT")));
		$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("DESCANYDATASETTEXT2"), true, false, false));
		$paragraph->addXmlnukeObject($breakline);

		$pageField = new ProcessPageFields();
		$fieldPage = new ProcessPageField();
		$fieldPage->fieldName = "code";
		$fieldPage->key = true;
		$fieldPage->dataType = INPUTTYPE::NUMBER;
		$fieldPage->fieldCaption = $this->_myWords->Value("CODE");
		$fieldPage->fieldXmlInput = XmlInputObjectType::TEXTBOX;
		$fieldPage->visibleInList = true;
		$fieldPage->editable = true;
		$fieldPage->required = true;
		$fieldPage->rangeMin = "100";
		$fieldPage->rangeMax = "10000";
		$pageField->addProcessPageField($fieldPage);

		$fieldPage = new ProcessPageField();
		$fieldPage->fieldName = "name";
		$fieldPage->key = false;
		$fieldPage->dataType = INPUTTYPE::TEXT;
		$fieldPage->fieldCaption = $this->_myWords->Value("TXT_NAME");
		$fieldPage->fieldXmlInput = XmlInputObjectType::TEXTBOX;
		$fieldPage->visibleInList = true;
		$fieldPage->editable = true;
		$fieldPage->required = true;
		$pageField->addProcessPageField($fieldPage);

		$fieldPage = new ProcessPageField();
		$fieldPage->fieldName = "email";
		$fieldPage->key = false;
		$fieldPage->dataType = INPUTTYPE::EMAIL;
		$fieldPage->fieldCaption = $this->_myWords->Value("TXT_EMAIL");
		$fieldPage->fieldXmlInput = XmlInputObjectType::TEXTBOX;
		$fieldPage->visibleInList = true;
		$fieldPage->editable = true;
		$fieldPage->required = true;
		$pageField->addProcessPageField($fieldPage);

		$fieldPage = new ProcessPageField();
		$fieldPage->fieldName = "data";
		$fieldPage->key = false;
		$fieldPage->dataType = INPUTTYPE::DATE;
		$fieldPage->fieldCaption = $this->_myWords->Value("DATE");
		$fieldPage->fieldXmlInput = XmlInputObjectType::TEXTBOX;
		$fieldPage->visibleInList = false;
		$fieldPage->editable = true;
		$fieldPage->required = true;
		$fieldPage->size = 10;
		$pageField->addProcessPageField($fieldPage);

		$fieldPage = new ProcessPageField();
		$fieldPage->fieldName = "Memo";
		$fieldPage->key = false;
		$fieldPage->dataType = INPUTTYPE::TEXT;
		$fieldPage->fieldCaption = $this->_myWords->Value("MEMO");
		$fieldPage->fieldXmlInput = XmlInputObjectType::MEMO;
		$fieldPage->visibleInList = false;
		$fieldPage->editable = true;
		$fieldPage->required = false;
		$pageField->addProcessPageField($fieldPage);

		$processPage = new ProcessPageStateAnydata(
			        $this->_context,
			        $pageField,
			        $this->_myWords->Value("EDITDB"),
			        "module:sample?op=4",
			        null,
			        new AnydatasetFilenameProcessor("sample", $this->_context));
		$processPage->setPageSize(3, 0);
		$paragraph->addXmlnukeObject($processPage);

		$blockCenter->addXmlnukeObject($paragraph);

		$this->_document->addXmlnukeObject($blockCenter);
	}

	/**
	 * Edit DataBase
	 *
	 */
	protected function actionEditDB()
	{
		$blockCenter = new XmlBlockCollection($this->_myWords->Value("DATABASE"), BlockPosition::Center);

		$breakline = new XmlnukeBreakLine();

		$paragraph = new XmlParagraphCollection();
		$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("DATABASETEXT")));
		$paragraph->addXmlnukeObject($breakline);

		$secop = $this->_context->ContextValue("secop");

		// Menu
		$form = new XmlFormCollection($this->_context, "module:sample?op=5", "Menu");
		$optionlist = array();
		$optionlist[""] = "-- Selecione --";
		$optionlist["setup"] = "Configurar a Conexão";
		$optionlist["test"] = "Testar a conexão";
		$optionlist["create"] = "Create Sample Table";
		$optionlist["edit"] = "Edit Sample Table";
		$list = new XmlEasyList(EasyListType::SELECTLIST, "secop", "Selecione a Ação", $optionlist, $secop);
		$form->addXmlnukeObject($list);

		$btnmenu = new XmlInputButtons();
		$btnmenu->addSubmit("Selecionar");
		$form->addXmlnukeObject($btnmenu);
		$blockCenter->addXmlnukeObject($form);

		// Opções:
		switch ($secop)
		{
			case "setup":
				{
					$formsetup = new XmlFormCollection($this->_context, "module:sample?op=5", "Editar Conexão");
					$formsetup->addXmlnukeObject(new XmlInputHidden("secop", "setupconf"));

					$text = new XmlInputTextBox("Connection String", "connection", "adodriver://username:password@server/datasource");
					$text->setRequired(true);
					$formsetup->addXmlnukeObject($text);

					$btn = new XmlInputButtons();
					$btn->addSubmit("Salvar");
					$formsetup->addXmlnukeObject($btn);
					$blockCenter->addXmlnukeObject($formsetup);
					break;
				}
			case "setupconf":
				{
					$filename = new AnydatasetFilenameProcessor("_db", $this->_context);
					$anydata = new AnyDataSet($filename);
					$itf = new IteratorFilter();
					$itf->addRelation("dbname", Relation::Equal, "sampledb");
					$it = $anydata->getIterator($itf);
					if ($it->hasNext())
					{
						$sr = $it->moveNext();
						$sr->setField("dbtype", "dsn");
						$sr->setField("dbconnectionstring", $this->_context->ContextValue("connection"));
					}
					else
					{
						$anydata->appendRow();
						$anydata->addField("dbname", "sampledb");
						$anydata->addField("dbtype", "dsn");
						$anydata->addField("dbconnectionstring", $this->_context->ContextValue("connection"));
					}
					$anydata->Save();
					$paragraph->addXmlnukeObject(new XmlnukeText("Updated!", true));
					break;
				}
			case "test":
				{
					$db = new DBDataSet("sampledb", $this->_context);
					$db->TestConnection();
					$paragraph->addXmlnukeObject(new XmlnukeText("I suppose it is fine the connection string!", true));
					break;
				}
			case "create":
				{
					$db = new DBDataSet("sampledb", $this->_context);
					$sql = "create table sample (fieldkey integer, fieldname varchar(20))";
					$db->execSQL($sql);
					$db->TestConnection();
					$paragraph->addXmlnukeObject(new XmlnukeText("Table Created!", true));
					break;
				}
			case "edit":
				{
					// Cria um acesso a $processPage
					$pageFields = new ProcessPageFields();

					$fieldPage = new ProcessPageField();
					$fieldPage->fieldName = "fieldkey";
					$fieldPage->key = true;
					$fieldPage->dataType = INPUTTYPE::NUMBER;
					$fieldPage->fieldCaption = "Código";
					$fieldPage->fieldXmlInput = XmlInputObjectType::TEXTBOX;
					$fieldPage->visibleInList = true;
					$fieldPage->editable = true;
					$fieldPage->required = true;
					$fieldPage->rangeMin = "100";
					$fieldPage->rangeMax = "999";
					$pageFields->addProcessPageField($fieldPage);

					$fieldPage = new ProcessPageField();
					$fieldPage->fieldName = "fieldname";
					$fieldPage->key = false;
					$fieldPage->dataType = INPUTTYPE::TEXT;
					$fieldPage->fieldCaption = "Name";
					$fieldPage->fieldXmlInput = XmlInputObjectType::TEXTBOX;
					$fieldPage->visibleInList = true;
					$fieldPage->editable = true;
					$fieldPage->required = true;
					$fieldPage->maxLength = 20;
					$pageFields->addProcessPageField($fieldPage);

					$processPage =
						new ProcessPageStateDB(
								$this->_context,
								$pageFields,
								"Edição teste usando Banco de Dados",
								"module:sample?op=5", null,
								"sample",
								"sampledb");
					$processPage->setPageSize(3, 0);
					$processPage->addParameter("secop", "edit");
					$paragraph->addXmlnukeObject($processPage);
					break;
				}
		}

		$blockCenter->addXmlnukeObject($paragraph);

		$this->_document->addXmlnukeObject($blockCenter);
	}

	/**
	 * Action Upload
	 *
	 */
	protected function actionUpload()
	{
		$blockCenter = new XmlBlockCollection($this->_myWords->Value("UPLOAD"), BlockPosition::Center);
		$this->_document->addXmlnukeObject($blockCenter);
		$paragraph = new XmlParagraphCollection();
		$blockCenter->addXmlnukeObject($paragraph);

		switch ($this->_action)
		{
			case 'add':
				$fileProcessor = new UploadFilenameProcessor('*.*', $this->_context);
				$fileProcessor->setFilenameLocation(ForceFilenameLocation::DefinePath, "diretorio/subdir/");
				$result = $this->_context->processUpload($fileProcessor, false, 'form_file');
				$paragraph->addXmlnukeObject(new XmlnukeText("Arquivo " . $result[0] . " enviado com sucesso ao servidor!"));
				break;

			default:
				$paragraph->addXmlnukeObject(new XmlnukeText($this->_myWords->Value("DESCUPLOADTEXT2")));
				$url = new XmlnukeManageUrl(URLTYPE::MODULE, 'sample');
				$url->addParam('op', '6');
				$url->addParam('action', 'add');

				$form = new XmlFormCollection($this->_context, $url->getUrl(), $this->_myWords->Value("MAKEUPLOAD"));
				$paragraph = new XmlParagraphCollection();
				$blockCenter->addXmlnukeObject($paragraph);
				$paragraph->addXmlnukeObject($form);
				$fileField = new XmlInputFile($this->_myWords->Value("FILE"), 'form_file');
				$form->addXmlnukeObject($fileField);
				$button = new XmlInputButtons();
				$button->addSubmit($this->_myWords->Value("ADD"), "");
				$form->addXmlnukeObject($button);
				break;
		}
	}

	protected function Opcao7()
	{
		$block = new XmlBlockCollection("Exemplo 7: XmlDataSet", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();

		$para1 = new XmlParagraphCollection();

		$xmlstr = $this->_context->ContextValue("xmlstr");
		$rowNode = $this->_context->ContextValue("rownode");
		$colNodeStr = preg_split("/\n/", $this->_context->ContextValue("cols"));
		if ($xmlstr != "")
		{
			$colNode = array();
			foreach ($colNodeStr as $key=>$value)
			{
				$tmp = explode("=", $value);
				$colNode[$tmp[0]] = str_replace("\r", "", $tmp[1]);
			}

			$dataset = new XmlDataSet($this->_context, $xmlstr, $rowNode, $colNode);
			//$para1->addXmlnukeObject(new XmlnukeText(""));
			$editlist = new XmlEditList($this->_context, "XML Flat", "module:sample?op=7");
			$editlist->setReadOnly(true);
			$editlist->setDataSource($dataset->getIterator());
			$para1->addXmlnukeObject($editlist);
		}
		else
		{
			$processor = new AnydatasetFilenameProcessor("sample", $this->_context);
			$xmlstr = FileUtil::QuickFileRead($processor->PathSuggested() . "sample.xml");
			$rowNode = "book";
			$colNodeStr = array();
			$colNodeStr[] = "category=@category";
			$colNodeStr[] = "title=title";
			$colNodeStr[] = "titlelang=title/@lang";
			$colNodeStr[] = "year=year";
			$colNodeStr[] = "price=price";
			$colNodeStr[] = "author=author";
		}

		// Cria um Formulário
		$form = new XmlFormCollection($this->_context, "module:sample", "Formulário de Edição");

		$form->addXmlnukeObject(new XmlInputHidden("op", "7"));
		$memo = new XmlInputMemo("XML", "xmlstr", $xmlstr);
		$form->addXmlnukeObject($memo);

		$text = new XmlInputTextBox("Row XPath", "rownode", $rowNode);
		$text->setRequired(true);
		$form->addXmlnukeObject($text);

		$colMemo = new XmlInputMemo("Col XPath", "cols", join("\n", $colNodeStr));
		$form->addXmlnukeObject($colMemo);

		$buttons = new XmlInputButtons();
		$buttons->addSubmit("Submit", "bs");
		$form->addXmlnukeObject($buttons);


		$block->addXmlnukeObject($para1);
		$block->addXmlnukeObject($form);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao8()
	{
		$block = new XmlBlockCollection("Exemplo 8: TextFileDataSet", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para1 = new XmlParagraphCollection();

		$txtstr = $this->_context->ContextValue("txtstr");
		$regexp = $this->_context->ContextValue("regexp");
		$colNodeStr = preg_split("/\n/", $this->_context->ContextValue("cols"));
		if ($txtstr != "")
		{
			$processor = new AnydatasetFilenameProcessor("sample", $this->_context);
			FileUtil::QuickFileWrite($processor->PathSuggested() . "sample.csv", $txtstr);
			$dataset = new TextFileDataSet($this->_context, $processor->PathSuggested() . "sample.csv", $colNodeStr, $regexp);
			//$para1->addXmlnukeObject(new XmlnukeText(""));
			$editlist = new XmlEditList($this->_context, "Text Flat", "module:sample?op=8");
			$editlist->setReadOnly(true);
			$editlist->setDataSource($dataset->getIterator());
			$para1->addXmlnukeObject($editlist);
		}
		else
		{
			$processor = new AnydatasetFilenameProcessor("sample", $this->_context);
			$txtstr = FileUtil::QuickFileRead($processor->PathSuggested() . "sample.csv");
			$regexp = CSVFILE;
			$colNodeStr = array();
			$colNodeStr[] = "category";
			$colNodeStr[] = "title";
			$colNodeStr[] = "titlelang";
			$colNodeStr[] = "year";
			$colNodeStr[] = "price";
			$colNodeStr[] = "buyprice";
			$colNodeStr[] = "author";
		}

		// Cria um Formulário
		$form = new XmlFormCollection($this->_context, "module:sample", "Formulário de Edição");

		$form->addXmlnukeObject(new XmlInputHidden("op", "8"));
		$memo = new XmlInputMemo("Text", "txtstr", $txtstr);
		$form->addXmlnukeObject($memo);

		$text = new XmlInputTextBox("Regular Expression", "regexp", $regexp);
		$text->setRequired(true);
		$form->addXmlnukeObject($text);

		$colMemo = new XmlInputMemo("Col Names", "cols", join("\n", $colNodeStr));
		$form->addXmlnukeObject($colMemo);

		$buttons = new XmlInputButtons();
		$buttons->addSubmit("Submit", "bs");
		$form->addXmlnukeObject($buttons);


		$block->addXmlnukeObject($para1);
		$block->addXmlnukeObject($form);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao9()
	{
		$block = new XmlBlockCollection("Exemplo 9: XmlChart", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para1 = new XmlParagraphCollection();

		$colNodeStr = array();
		$colNodeStr[] = "category";
		$colNodeStr[] = "title";
		$colNodeStr[] = "titlelang";
		$colNodeStr[] = "year";
		$colNodeStr[] = "price";
		$colNodeStr[] = "buyprice";
		$colNodeStr[] = "author";

		$processor = new AnydatasetFilenameProcessor("sample", $this->_context);
		$dataset = new TextFileDataSet($this->_context, $processor->PathSuggested() . "sample.csv", $colNodeStr);

		//$para1->addXmlnukeObject(new XmlnukeText(""));
		$editlist = new XmlEditList($this->_context, "Text Flat", "module:sample?op=9");
		$editlist->setReadOnly(true);
		$editlist->setDataSource($dataset->getIterator());
		$para1->addXmlnukeObject($editlist);

		$chart = new XmlChart($this->_context, "Book Store", $dataset->getIterator(), ChartOutput::Flash, ChartSeriesFormat::Column);
		$chart->setLegend("category", "#000000", "#C0C0C0");
		$chart->addSeries("price", "Sell Price", "#000000");
		$chart->addSeries("buyprice", "Buy Price", "#000000");
		$para1->addXmlnukeObject($chart);

		$code = new XmlnukeCode("Code Sample");
		$code->AddTextLine("\$chart = new XmlChart(");
		$code->AddTextLine("		\$this->_context,             // Xmlnuke Context");
		$code->AddTextLine("		\"Book Store\",            // Graph Title");
		$code->AddTextLine("		\$dataset->getIterator(),     // IIterator Object");
		$code->AddTextLine("		ChartOutput::Flash,         // Graph output format ");
		$code->AddTextLine("		ChartSeriesFormat::Column   // Default column type");
		$code->AddTextLine(");");
		$code->AddTextLine("\$chart->setLegend(\"category\", \"#000000\", \"#C0C0C0\");");
		$code->AddTextLine("\$chart->addSeries(\"price\", \"Sell Price\", \"#000000\");");
		$para1->addXmlnukeObject($code);

		$block->addXmlnukeObject($para1);

		$this->_document->addXmlnukeObject($block);
	}


	protected function Opcao10()
	{
		$block = new XmlBlockCollection("Exemplo 10: TabView", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para1 = new XmlParagraphCollection();
		$para1->addXmlnukeObject(new XmlnukeText("Parágrafo 1"));

		$para2 = new XmlParagraphCollection();
		$para2->addXmlnukeObject(new XmlnukeText("Parágrafo 2"));

		$para3 = new XmlParagraphCollection();
		$para3->addXmlnukeObject(new XmlnukeText("Parágrafo 3"));

		$tabview = new XmlnukeTabView();
		$tabview->addTabItem("Aba 1", $para1);
		$tabview->addTabItem("Aba 2", $para2);
		$tabview->addTabItem("Aba 3", $para3);

		$block->addXmlnukeObject($tabview);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao11()
	{
		$block = new XmlBlockCollection("Exemplo 11: DualList", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();

		if ($this->isPostBack())
		{
			Debug::PrintValue(XmlDualList::Parse($this->_context, "frmdual"));
		}


		$form = new XmlFormCollection($this->_context, "module:sample?op=11", "Formulário com Um Dual List");

		// Create DualList Object
		$duallist = new XmlDualList($this->_context, "frmdual", "Não Selecionado", "Selecionado");
		$duallist->setDualListSize(10,10);
		$duallist->createDefaultButtons();

		// Define DataSet Source
		$arrayLeft = array("A" => "Letra A", "B" => "Letra B", "C" => "Letra C", "D" => "Letra D", "E" => "Letra E", "F" => "Letra F");
		$arrayRight = array("B" => "Letra B", "D" => "Letra D");
		$arrayDBLeft = new ArrayDataSet($arrayLeft);
		$itLeft = $arrayDBLeft->getIterator();
		$arrayDBRight = new ArrayDataSet($arrayRight);
		$itRight = $arrayDBRight->getIterator();
		$duallist->setDataSourceFieldName("key", "value");
		$duallist->setDataSource($itLeft, $itRight);

		$form->addXmlnukeObject($duallist);

		$button = new XmlInputButtons();
		$button->addSubmit("Enviar Dados");
		$form->addXmlnukeObject($button);

		$block->addXmlnukeObject($form);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao12()
	{
		$block = new XmlBlockCollection("Exemplo 12: Faq", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para1 = new XmlnukeSpanCollection();
		$para1->addXmlnukeObject(new XmlnukeText("Parágrafo 1", true));

		$para2 = new XmlnukeSpanCollection();
		$para2->addXmlnukeObject(new XmlnukeText("Parágrafo 2"));

		$para3 = new XmlnukeSpanCollection();
		$para3->addXmlnukeObject(new XmlnukeText("Parágrafo 3"));

		$faq = new XmlnukeFaq("Lista de Perguntas");
		$faq->addFaqItem("Pergunta 1", $para1);
		$faq->addFaqItem("Pergunta 2", $para2);
		$faq->addFaqItem("Pergunta 3", $para3);

		$block->addXmlnukeObject($faq);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao13()
	{
		$block = new XmlBlockCollection("Exemplo 13: Ajax Post", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("Com o XMLNuke é possível fazer um POST sem dar um refresh na página. É possível também definir uma área para exibir a resposta."));
		$para->addXmlnukeObject(new XmlnukeText("Útil para fazer Uploads ou processamentos em background."));
		$block->addXmlnukeObject($para);

		// First Create the FORM
		$form = new XmlFormCollection($this->_context, "xmlnuke.php?site=sample&amp;xsl=preview", "Ajax Post");

		$txt = new XmlInputTextBox("Algum Texto", "name", "");
		$txt->setRequired(true);
		$form->addXmlnukeObject($txt);

		// And Add a Submit Button
		$button = new XmlInputButtons();
		$button->addSubmit("Teste");
		$form->addXmlnukeObject($button);

		$block->addXmlnukeObject($form);

		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeBreakLine());
		$para->addXmlnukeObject(new XmlnukeBreakLine());
		$para->addXmlnukeObject(new XmlnukeBreakLine());
		$para->addXmlnukeObject(new XmlnukeBreakLine());

		// Second, Create a AjaxCallBack, associate it our form
		$ajax = new XmlnukeAjaxCallback($this->_context);
		$ajax->setCustomStyle(400, true);
		$form->setAjaxCallback($ajax);
		$para->addXmlnukeObject($ajax);
		$block->addXmlnukeObject($para);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao14()
	{
		$block = new XmlBlockCollection("Exemplo 14: Auto Suggest", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("É possível associar um TextBox e uma consulta com auto sugestão de valores. Nesse exemplo, estamos consultando TODOS os links <A> existentes na página que é exibido como XML."));
		$block->addXmlnukeObject($para);

		$form = new XmlFormCollection($this->_context, "", "Teste");

		$form->addXmlnukeObject(new XmlInputCaption("Auto Suggest"));

		// First Create the the TextBox
		$txt = new XmlInputTextBox("Teste", "Nome", "");
		$txt->setRequired(true);
		// and then associate the AutoSuggest
		$txt->setAutosuggest($this->_context, "module:sample?site=sample&rawxml=true&xpath=//a&","input");
		$form->addXmlnukeObject($txt);

		$form->addXmlnukeObject(new XmlInputCaption("Auto Suggest com CallBack"));

		// First Create the the TextBox
		$txt = new XmlInputTextBox("Teste", "Nome2", "");
		$txt->setRequired(true);
		$txt->setAutosuggest($this->_context, "module:sample?site=sample&rawxml=true&xpath=//a&","input","nodeinfo", "nodeid", "alert(obj.id + ' - ' + obj.info + ' - ' + obj.value)");
		$form->addXmlnukeObject($txt);


		$block->addXmlnukeObject($form);

		$this->_document->addXmlnukeObject($block);
	}


	protected function Opcao15()
	{
		$block = new XmlBlockCollection("Exemplo 15: Tree View", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("Esse exemplo mostra como criar um componente Treeview através das classes de abstração XmlnukeTreeview, XmlnukeTreeviewFolder e XmlnukeTreeviewLeaf"));
		$block->addXmlnukeObject($para);

		$treeview = new XmlnukeTreeview($this->_context, "Título");

		$folder1 = new XmlnukeTreeViewFolder($this->_context, "Folder 1", "mydocuments.gif");
		$folder1->setAction(TreeViewActionType::ExecuteJS, "document.getElementById('here').style.display='none';");

		$leaf = new XmlnukeTreeViewLeaf($this->_context, "Leaf 1", "empty_doc.gif");
		$leaf->setAction(TreeViewActionType::OpenUrl, "module:sample?op=1");
		$folder1->addChild( $leaf );
		$leaf = new XmlnukeTreeViewLeaf($this->_context, "Leaf 2", "empty_doc.gif");
		$leaf->setAction(TreeViewActionType::OpenInNewWindow, "module:sample?op=2");
		$folder1->addChild( $leaf );
		$leaf = new XmlnukeTreeViewLeaf($this->_context, "Leaf 3", "document.gif");
		$leaf->setAction(TreeViewActionType::OpenUrlInsideContainer, "module:sample?op=3&xsl=blank", "here");
		$folder1->addChild( $leaf );
		$treeview->addChild($folder1);

		$folder2 = new XmlnukeTreeViewFolder($this->_context, "Folder 1", "myimages.gif");
		$folder2->addChild( new XmlnukeTreeViewLeaf($this->_context, "Leaf 1", "document.gif") );
		$treeview->addChild($folder2);

		$block->addXmlnukeObject($treeview);

		$container = new XmlContainerCollection("here");
		$container->setStyle("display: none; width: 100%; height: 200px");
		$block->addXmlnukeObject($container);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao16()
	{
		$block = new XmlBlockCollection("Exemplo 16: Sortable", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("Esse exemplo mostra como criar um componente Treeview através das classes de abstração XmlnukeTreeview, XmlnukeTreeviewFolder e XmlnukeTreeviewLeaf"));
		$block->addXmlnukeObject($para);

		$form = new XmlFormCollection($this->_context, "", "Sortable Example");
		$sortable = new XmlInputSortableList("Teste", "meunome");
		$sortable->addSortableItem("1", new XmlnukeText("Teste 1"));
		$sortable->addSortableItem("2", new XmlnukeText("Teste 2"));
		$sortable->addSortableItem("3", new XmlnukeText("Teste 3"), SortableListItemState::Highligth);
		$sortable->addSortableItem("4", new XmlnukeText("Teste 4"), SortableListItemState::Disabled);
		$sortable->addSortableItem("5", new XmlnukeText("Teste 5"));
		$sortable->addSortableItem("6", new XmlnukeText("Teste 6"));

		$form->addXmlnukeObject($sortable);

		$block->addXmlnukeObject($form);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao17()
	{
		$block = new XmlBlockCollection("Exemplo 17: Calendar", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("Esse exemplo mostra como criar um componente Treeview através das classes de abstração XmlnukeTreeview, XmlnukeTreeviewFolder e XmlnukeTreeviewLeaf"));
		$block->addXmlnukeObject($para);

		$calendar = new XmlnukeCalendar(1, 1974);
		$calendar->addCalendarEvent(new XmlnukeCalendarEvent(15, 1, "Teste"));
		$calendar->addCalendarEvent(new XmlnukeCalendarEvent(26, 2, "Teste"));

		$block->addXmlnukeObject($calendar);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao18()
	{
		$block = new XmlBlockCollection("Exemplo 18: UI Alert", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();


		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("Esse exemplo mostra como mostrar uma mensagem de alert no cliente"));
		$block->addXmlnukeObject($para);

		if ($this->_context->ContextValue("type") != "")
		{
			switch ($this->_context->ContextValue("type"))
			{
				case 1:
					$uialert = new XmlnukeUIAlert($this->_context, UIAlert::Dialog, "Isso é um teste");
					break;
				case 2:
					$uialert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog, "Isso é um teste");
					$uialert->setAutoHide(10000);
					break;
				case 3:
					$uialert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog, "Isso é um teste");
					$uialert->addRedirectButton("Ok", "module:sample");
					$uialert->addCloseButton("Cancel");
					break;
				case 4:
					$uialert = new XmlnukeUIAlert($this->_context, UIAlert::ModalDialog, "Isso é um teste");
					$uialert->addRedirectButton("Ok, proceed!", "module:sample");
					$uialert->addCloseButton("Cancel");
					$uialert->setOpenAction(UIAlertOpenAction::Button, "Clique me");
					break;
				case 5:
					$uialert = new XmlnukeUIAlert($this->_context, UIAlert::BoxInfo, "Isso é um teste");
					$uialert->setAutoHide(2000);
					break;
				case 6:
					$uialert = new XmlnukeUIAlert($this->_context, UIAlert::BoxAlert, "Isso é um teste");
					break;
			}
			$uialert->addXmlnukeObject(new XmlnukeText("Isso é um novo teste, novo teste"));
			$block->addXmlnukeObject($uialert);
		}

		$list = array();
		$list["module:sample?op=18&type=1"] = "Caixa de Diálogo";
		$list["module:sample?op=18&type=2"] = "Caixa de Diálogo Modal";
		$list["module:sample?op=18&type=3"] = "Caixa de Diálogo Modal com botão de fechar";
		$list["module:sample?op=18&type=4"] = "Caixa de Diálogo Modal com botões de confirmação e abrir personalizado";
		$list["module:sample?op=18&type=5"] = "Box de Informação com auto hide";
		$list["module:sample?op=18&type=6"] = "Box de Alerta";

		$listElement = new XmlListCollection(XmlListType::UnorderedList, "Opções");
		foreach ($list as $key=>$value)
		{
			$href = new XmlAnchorCollection($key);
			$href->addXmlnukeObject(new XmlnukeText($value));
			$listElement->addXmlnukeObject($href);
		}
		$block->addXmlnukeObject($listElement);

		$this->_document->addXmlnukeObject($block);
	}

	protected function Opcao19()
	{
		$block = new XmlBlockCollection("Exemplo 19: Media Gallery", BlockPosition::Center);

		//XmlnukeBreakLine br = new XmlnukeBreakLine();

		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("Galeria de Imagens (album de Fotos)"));

		$gallery = new XmlnukeMediaGallery($this->_context, "Galeria1");
		$gallery->addImage("common/imgs/albumsample/1.jpg", "common/imgs/albumsample/t_1.jpg", "Titulo Imagem 1", "Você pode colocar um caption aqui", 60, 60);
		$gallery->addImage("common/imgs/albumsample/2.jpg", "common/imgs/albumsample/t_2.jpg", "Titulo Imagem 2", "Você pode colocar um caption aqui", 60, 60);
		$gallery->addImage("common/imgs/albumsample/3.jpg", "common/imgs/albumsample/t_3.jpg", "Titulo Imagem 3", "Você pode colocar um caption aqui", 60, 60);
		$para->addXmlnukeObject($gallery);
		$block->addXmlnukeObject($para);

		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("Flash, Youtube e Quicktime"));

		$gallery = new XmlnukeMediaGallery($this->_context);
		$gallery->addEmbed("http://www.adobe.com/products/flashplayer/include/marquee/design.swf", 792, 294, "http://images.apple.com/trailers/wb/images/terminatorsalvation_200903131052.jpg", "Titulo Flash", "Aqui vc está vendo um Flash");
		$gallery->addEmbed("http://movies.apple.com/movies/wb/terminatorsalvation/terminatorsalvation-tlr3_h.480.mov", 480, 204, "http://images.apple.com/trailers/wb/images/terminatorsalvation_200903131052.jpg", "Titulo Quicktime", "Aqui vc está vendo um Quicktime Movie");
		$gallery->addEmbed("http://www.youtube.com/watch?v=4m48GqaOz90", "", "", "http://i1.ytimg.com/vi/4m48GqaOz90/default.jpg", "Titulo Youtube", "Aqui vc está vendo um Vídeo do Youtube");
		$para->addXmlnukeObject($gallery);
		$block->addXmlnukeObject($para);

		$para = new XmlParagraphCollection();
		$para->addXmlnukeObject(new XmlnukeText("IFrame"));

		$gallery = new XmlnukeMediaGallery($this->_context);
		$gallery->addIFrame("module:sample", 480, 204, "", "IFrame");
		$para->addXmlnukeObject($gallery);
		$block->addXmlnukeObject($para);

		$gallery = new XmlnukeMediaGallery($this->_context, "Galeria2");
		$gallery->setApi(true);
		$gallery->setVisible(false);
		$gallery->addImage("common/imgs/albumsample/4.jpg", "", "Titulo Imagem 1", "Você pode colocar um caption aqui");
		$gallery->addImage("common/imgs/albumsample/5.jpg", "", "Titulo Imagem 2", "Você pode colocar um caption aqui");
		$gallery->addImage("common/imgs/albumsample/1.jpg", "", "Titulo Imagem 3", "Você pode colocar um caption aqui");
		$block->addXmlnukeObject($gallery);

		$form = new XmlFormCollection($this->_context, "", "Abrir por JavaScript");
		$button = new XmlInputButtons();
		$button->addButton("Clique para abrir a Galeria", "kk", "open_Galeria2()");
		$form->addXmlnukeObject($button);
		$block->addXmlnukeObject($form);

		$this->_document->addXmlnukeObject($block);
	}

}

?>
