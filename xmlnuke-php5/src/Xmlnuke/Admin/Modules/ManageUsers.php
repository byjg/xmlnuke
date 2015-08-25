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
 * @package xmlnuke
 */
namespace Xmlnuke\Admin\Modules;

use Xmlnuke\Core\Admin\NewBaseAdminModule;
use Xmlnuke\Core\Admin\UsersBase;
use ByJG\AnyDataset\Repository\IteratorFilter;
use Xmlnuke\Core\Classes\EditListField;
use Xmlnuke\Core\Classes\XmlAnchorCollection;
use Xmlnuke\Core\Classes\XmlBlockCollection;
use Xmlnuke\Core\Classes\XmlEasyList;
use Xmlnuke\Core\Classes\XmlEditList;
use Xmlnuke\Core\Classes\XmlFormCollection;
use Xmlnuke\Core\Classes\XmlInputButtons;
use Xmlnuke\Core\Classes\XmlInputCheck;
use Xmlnuke\Core\Classes\XmlInputHidden;
use Xmlnuke\Core\Classes\XmlInputLabelField;
use Xmlnuke\Core\Classes\XmlInputTextBox;
use Xmlnuke\Core\Classes\XmlnukeSpanCollection;
use Xmlnuke\Core\Classes\XmlnukeTabView;
use Xmlnuke\Core\Classes\XmlnukeText;
use Xmlnuke\Core\Classes\XmlParagraphCollection;
use Xmlnuke\Core\Classes\XmlTableCollection;
use Xmlnuke\Core\Classes\XmlTableColumnCollection;
use Xmlnuke\Core\Classes\XmlTableRowCollection;
use Xmlnuke\Core\Enum\AccessLevel;
use Xmlnuke\Core\Enum\BlockPosition;
use Xmlnuke\Core\Enum\EasyListType;
use Xmlnuke\Core\Enum\InputTextBoxType;
use Xmlnuke\Core\Enum\INPUTTYPE;
use ByJG\AnyDataset\Enum\Relation;
use Xmlnuke\Core\Enum\UserProperty;
use Xmlnuke\Util\FileUtil;

class ManageUsers extends NewBaseAdminModule
{
	private $url = "module:Xmlnuke.Admin.ManageUsers";

	public function ManageUsers()
	{
	}

	public function useCache()
	{
		return false;
	}
	public function  getAccessLevel()
    {
          return AccessLevel::OnlyRole;
    }

    public function getRole()
    {
           return "MANAGER";
    }

    protected $myWords;

	public function CreatePage()
	{
		parent::CreatePage(); // Doesnt necessary get PX, because PX is protected!

		$users = $this->getUsersDatabase();

		//anydataset.SingleRow user;

		$action = strtolower($this->_action);
		$uid = $this->_context->get("valueid");

		$this->myWords = $this->WordCollection();
		$this->setTitlePage($this->myWords->Value("TITLE"));
		$this->setHelp($this->myWords->Value("DESCRIPTION"));

		$this->addMenuOption($this->myWords->Value("NEWUSER"), $this->url, null);
		$this->addMenuOption($this->myWords->Value("ADDROLE"), "module:Xmlnuke.Admin.manageusersgroups", null);

		// --------------------------------------
		// CHECK ACTION
		// --------------------------------------
		$exec = false;
		if ( ($action != "") && ($action != "move"))
		{
			$message = new XmlParagraphCollection();

			if ( $action == "newuser" )
			{
				if (!$users->addUser( $this->_context->get("name"), $this->_context->get("login"), $this->_context->get("email"), $this->_context->get("password") ) )
				{
					$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("USEREXIST"), true ));
				}
				else
				{
					if ($this->isUserAdmin())
					{
						$user = $users->getUserName( $this->_context->get("login") );
						$user->setField( $users->getUserTable()->admin, $this->_context->get("admin") );
						$users->Save();
					}
					$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("CREATED"), true ));
				}
				$exec = true;
			}

			if ( $action == "update" )
			{
				$user = $users->getById( $uid );
				$user->setField($users->getUserTable()->name, $this->_context->get("name") );
				$user->setField($users->getUserTable()->email, $this->_context->get("email") );
				if ($this->isUserAdmin())
				{
					$user->setField($users->getUserTable()->admin, $this->_context->get("admin") );
				}
				$users->Save();
				$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("UPDATE"), true ));
				$exec = true;
			}

			if ( $action == "changepassword" )
			{
				$user = $users->getById( $uid );
				$user->setField($users->getUserTable()->password, $users->getSHAPassword($this->_context->get("password")) );
				$users->Save();
				$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("CHANGEPASSWORD"), true ));
				$exec = true;
			}

			if ( $action == "delete" )
			{
				if ( $users->removeUserName( $uid ) )
				{
					$users->Save();
					$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("DELETE"), true ));
					$uid = "";
				}
				else
				{
					$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("ERRO"), true ));
				}
				$exec = true;
			}

			if ( $action == "addrole" )
			{
				$users->addPropertyValueToUser( $uid, $this->_context->get("role"), UserProperty::Role );
				$users->Save();
				$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("ROLEADDED"), true ));
				$exec = true;
			}

			if ( $action == "removerole" )
			{
				$users->removePropertyValueFromUser( $uid, $this->_context->get("role"), UserProperty::Role );
				$users->Save();
				$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("ROLEREMOVED"), true ));
				$exec = true;
			}
            if ( $action == "addcustomvalue" )
            {
				$users->addPropertyValueToUser( $uid, $this->_context->get("customvalue"), $this->_context->get("customfield") );
				$users->Save();
				$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("CUSTOMFIELDUPDATED"), true ));
				$exec = true;
            }
            if ( $action == "removecustomvalue" )
        	{
				$users->removePropertyValueFromUser( $uid, $this->_context->get("customvalue"),  $this->_context->get("customfield"));
				$users->Save();
				$message->addXmlnukeObject(new XmlnukeText($this->myWords->Value("CUSTOMFIELDREMOVED"), true ));
				$exec = true;
            }

            if ($exec)
            {
				$block = new XmlBlockCollection($this->myWords->Value("WORKINGAREA"), BlockPosition::Center);
                $block->addXmlnukeObject($message);
				$this->defaultXmlnukeDocument->addXmlnukeObject($block);
            }
		}


		// --------------------------------------
		// LIST USERS
		// --------------------------------------
		$block = new XmlBlockCollection($this->myWords->Value("USERS"), BlockPosition::Center);

		$itf = new IteratorFilter();
		if (!$this->isUserAdmin())
		{
			$itf->addRelation("admin",  Relation::NOT_EQUAL, "yes");
		}
		if ($this->_context->get("pesquser") != "")
		{
			$itf->startGroup();
			$itf->addRelationOr($users->getUserTable()->username,  Relation::CONTAINS, $this->_context->get("pesquser"));
                        $itf->addRelationOr($users->getUserTable()->name,  Relation::CONTAINS, $this->_context->get("pesquser"));
                        $itf->addRelationOr($users->getUserTable()->email,  Relation::CONTAINS, $this->_context->get("pesquser"));
			$itf->endGroup();
		}
		$it = $users->getIterator($itf);

		$formpesq = new XmlFormCollection($this->_context, $this->url, $this->myWords->Value("TITLEPESQUSER"));
		$formpesq->setFormName("form-label-top");
                $textbox = new XmlInputTextBox($this->myWords->Value("PESQUSER"), "pesquser", $this->_context->get("pesquser"));
                $textbox->setDataType(INPUTTYPE::TEXT);
                $formpesq->addXmlnukeObject($textbox);
                $textbox->setRequired(true);

                $boxButton = new XmlInputButtons();
                $boxButton->addSubmit($this->myWords->Value("GOSEARCH"), "");
                $formpesq->addXmlnukeObject($boxButton);
		$block->addXmlnukeObject($formpesq);

		$editlist = new XmlEditList($this->_context, $this->myWords->Value("USERS"), $this->url, true, false, true, true);
		$editlist->setDataSource($it);
		$editlist->setPageSize(20, 0);
		$editlist->setEnablePage(true);

		$field = new EditListField();
		$field->fieldData = $users->getUserTable()->id;
		$editlist->addEditListField($field);

		$field = new EditListField();
		$field->fieldData = $users->getUserTable()->username;
		$field->editlistName = $this->myWords->Value("TXT_LOGIN");
		$editlist->addEditListField($field);

		$field = new EditListField();
		$field->fieldData = $users->getUserTable()->name;
		$field->editlistName = $this->myWords->Value("TXT_NAME");
		$editlist->addEditListField($field);

		$field = new EditListField();
		$field->fieldData = $users->getUserTable()->email;
		$field->editlistName = $this->myWords->Value("TXT_EMAIL");
		$editlist->addEditListField($field);

		$field = new EditListField();
		$field->fieldData = $users->getUserTable()->admin;
		$field->editlistName = $this->myWords->Value("TITADM");
		$editlist->addEditListField($field);

		$block->addXmlnukeObject($editlist);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);


		// --------------------------------------
		// EDIT AREA
		// --------------------------------------

		if ( ($action == "new") || ($action == "newuser") || ($action == "") || ($action == "move") || ($action == "delete"))
		{
			$this->NewUser();
		}
		else
		{
			$this->EditUser($users, $uid);
		}

		return $this->defaultXmlnukeDocument->generatePage();
	}

	public function NewUser()
	{
		$block = new XmlBlockCollection($this->myWords->Value("NEWUSER"), BlockPosition::Center );
		$form =  new XmlFormCollection($this->_context, $this->url, "");
		$form->setJSValidate(true);
		$form->addXmlnukeObject(new XmlInputHidden("action", "newuser"));
		$form->addXmlnukeObject(new XmlInputHidden("curpage", $this->_context->get("curpage")));

		$textbox = new XmlInputTextBox($this->myWords->Value("LABEL_LOGIN"), "login", "");
		$textbox->setDataType(INPUTTYPE::TEXT);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$textbox = new XmlInputTextBox($this->myWords->Value("LABEL_NAME"), "name", "");
		$textbox->setDataType(INPUTTYPE::TEXT);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$textbox = new XmlInputTextBox($this->myWords->Value("LABEL_EMAIL"), "email", "");
		$textbox->setDataType(INPUTTYPE::EMAIL);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$textbox = new XmlInputTextBox($this->myWords->Value("LABEL_PASSWORD"), "password", "");
		$textbox->setInputTextBoxType(InputTextBoxType::PASSWORD);
		$textbox->setDataType(INPUTTYPE::TEXT);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);

		$check = new XmlInputCheck($this->myWords->Value("FORMADMINISTRADOR"), "admin", "yes");
		$check->setReadOnly(!$this->isUserAdmin());
		$form->addXmlnukeObject($check);

		$boxButton = new XmlInputButtons();
		$boxButton->addSubmit($this->myWords->Value("TXT_CREATE"), "");
		$form->addXmlnukeObject($boxButton);

		$block->addXmlnukeObject($form);
		$this->defaultXmlnukeDocument->addXmlnukeObject($block);
	}

	public function EditUser($users, $uid)
	{
		$user = $users->getById($uid);
		$block = new XmlBlockCollection($this->myWords->Value("EDITUSER") . $user->getField($users->getUserTable()->username), BlockPosition::Center );

		if  (!$this->isUserAdmin() && ($user->getField($users->getUserTable()->admin)=="yes") )
		{
			$p = new XmlParagraphCollection();
			$p->addXmlnukeObject(new XmlnukeText($this->myWords->Value("CANNOTEDITADMIN")));
			$block->addXmlnukeObject($p);
	        $this->defaultXmlnukeDocument->addXmlnukeObject($block);
	        return;
		}

		$tabview = new XmlnukeTabView();
		$block->addXmlnukeObject($tabview);

		// -------------------------------------------------------------------
		// EDITAR USUÁRIO
		$form =  new XmlFormCollection($this->_context, $this->url, "");
		$form->setJSValidate(true);
		$form->addXmlnukeObject(new XmlInputHidden("action", "update"));
		$form->addXmlnukeObject(new XmlInputHidden("curpage", $this->_context->get("curpage")));
		$form->addXmlnukeObject(new XmlInputHidden("valueid", $uid));
		$textbox = new XmlInputTextBox($this->myWords->Value("LABEL_LOGIN"), "login", $user->getField($users->getUserTable()->username));
		$textbox->setDataType(INPUTTYPE::TEXT);
		$textbox->setRequired(true);
		$textbox->setReadOnly(true);
		$form->addXmlnukeObject($textbox);
		$textbox = new XmlInputTextBox($this->myWords->Value("LABEL_NAME"), "name", $user->getField($users->getUserTable()->name));
		$textbox->setDataType(INPUTTYPE::TEXT);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		$textbox = new XmlInputTextBox($this->myWords->Value("LABEL_EMAIL"), "email", $user->getField($users->getUserTable()->email));
		$textbox->setDataType(INPUTTYPE::EMAIL);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		$textbox = new XmlInputLabelField($this->myWords->Value("LABEL_PASSWORD"), $this->myWords->Value("FORMPASSWORDNOTVIEW"));
		$form->addXmlnukeObject($textbox);
		$check = new XmlInputCheck($this->myWords->Value("FORMADMINISTRADOR"), "admin", "yes");
		$check->setChecked($user->getField($users->getUserTable()->admin));
		$check->setReadOnly(!$this->isUserAdmin());
		$form->addXmlnukeObject($check);
		$boxButton = new XmlInputButtons();
		$boxButton->addSubmit($this->myWords->Value("TXT_UPDATE"), "");
		$form->addXmlnukeObject($boxButton);
		$tabview->addTabItem($this->myWords->Value("TABEDITUSER"), $form);

		// -------------------------------------------------------------------
		// ALTERAR SENHA
		$form =  new XmlFormCollection($this->_context, $this->url, "");
		$form->setJSValidate(true);
		$form->addXmlnukeObject(new XmlInputHidden("action", "changepassword"));
		$form->addXmlnukeObject(new XmlInputHidden("curpage", $this->_context->get("curpage")));
		$form->addXmlnukeObject(new XmlInputHidden("valueid", $uid));
		$textbox = new XmlInputTextBox($this->myWords->Value("FORMNEWPASSWORD"), "password", "");
		$textbox->setInputTextBoxType(InputTextBoxType::PASSWORD);
		$textbox->setDataType(INPUTTYPE::TEXT);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		$boxButton = new XmlInputButtons();
		$boxButton->addSubmit($this->myWords->Value("TXT_CHANGE"), "");
		$form->addXmlnukeObject($boxButton);
		$tabview->addTabItem($this->myWords->Value("TABCHANGEPASSWD"), $form);

		// -------------------------------------------------------------------
		// REMOVER USUARIO
		$form =  new XmlFormCollection($this->_context, $this->url, "");
		$form->setJSValidate(true);
		$form->addXmlnukeObject(new XmlInputHidden("action", "delete"));
		$form->addXmlnukeObject(new XmlInputHidden("curpage", $this->_context->get("curpage")));
		$form->addXmlnukeObject(new XmlInputHidden("valueid", $uid));
		$boxButton = new XmlInputButtons();
		$boxButton->addSubmit($this->myWords->Value("BUTTONREMOVE"), "");
		$form->addXmlnukeObject($boxButton);
		$tabview->addTabItem($this->myWords->Value("TABREMOVEUSER"), $form);

		// -------------------------------------------------------------------
		// REMOVER PAPEL DO USUARIO
		$para = new XmlParagraphCollection();
		$form =  new XmlFormCollection($this->_context, $this->url, $this->myWords->Value("FORMEDITROLES"));
		$form->setJSValidate(true);
		$form->addXmlnukeObject(new XmlInputHidden("action", "removerole"));
		$form->addXmlnukeObject(new XmlInputHidden("curpage", $this->_context->get("curpage")));
		$form->addXmlnukeObject(new XmlInputHidden("valueid", $uid));
		$userroles = $users->returnUserProperty($uid, UserProperty::Role);
		$userroles = is_null($userroles)?array():$userroles;
		$roles = array();
		foreach ($userroles as $i)
		{
			$roles[$i] = $i;
		}
		$form->addXmlnukeObject(new XmlEasyList(EasyListType::SELECTLIST, "role", $this->myWords->Value("FORMUSERROLES"), $roles));
		$boxButton = new XmlInputButtons();
		$boxButton->addSubmit($this->myWords->Value("TXT_REMOVE"), "");
		$form->addXmlnukeObject($boxButton);
		$para->addXmlnukeObject($form);

		// -------------------------------------------------------------------
		// ADICIONAR PAPEL AO USUARIO
		$form =  new XmlFormCollection($this->_context, $this->url, "");
		$form->setJSValidate(true);
		$form->addXmlnukeObject(new XmlInputHidden("action", "addrole"));
		$form->addXmlnukeObject(new XmlInputHidden("curpage", $this->_context->get("curpage")));
		$form->addXmlnukeObject(new XmlInputHidden("valueid", $uid));
//		$textbox = new XmlInputTextBox($this->myWords->Value("FORMROLES"), "role", "", 10);
//		$textbox->setInputTextBoxType(InputTextBoxType::TEXT);
//		$textbox->setDataType(INPUTTYPE::TEXT);
//		$textbox->setRequired(true);
//		$form->addXmlnukeObject($textbox);

//		$form->addXmlnukeObject(new XmlInputLabelField("DEFAULT ROLES", "EDITOR"));
//		$form->addXmlnukeObject(new XmlInputLabelField("", "DESIGNER"));
//		$form->addXmlnukeObject(new XmlInputLabelField("", "MANAGER"));
		$roleData = $this->getAllRoles($users);
		$selectRole = new XmlEasyList(EasyListType::SELECTLIST , "role", $this->myWords->Value("FORMROLES"), $roleData);
		$form->addXmlnukeObject($selectRole);
		$boxButton = new XmlInputButtons();
		$boxButton->addSubmit($this->myWords->Value("TXT_ADD"), "");
		$form->addXmlnukeObject($boxButton);
		$para->addXmlnukeObject($form);
		$tabview->addTabItem($this->myWords->Value("TABMANROLE"), $para, (($this->_action == "addrole")||($this->_action == "removerole")));

        //------------------------------------------------------------------------
        // CUSTOM FIELDS
        //------------------------------------------------------------------------

		$block2 = new XmlnukeSpanCollection();

		$table = new XmlTableCollection();
		$row = new XmlTableRowCollection();

		$col = new XmlTableColumnCollection();
        $col->addXmlnukeObject(new XmlnukeText($this->myWords->Value("ACTION"), true));
        $row->addXmlnukeObject($col);

		$col = new XmlTableColumnCollection();
        $col->addXmlnukeObject(new XmlnukeText($this->myWords->Value("GRIDFIELD"), true));
        $row->addXmlnukeObject($col);

		$col = new XmlTableColumnCollection();
        $col->addXmlnukeObject(new XmlnukeText($this->myWords->Value("GRIDVALUE"), true));
        $row->addXmlnukeObject($col);
        $table->addXmlnukeObject($row);

		$fields = $user->getFieldNames();
		$fieldsLength = count($fields);

        foreach( $fields as $fldValue=>$fldName)
        {
        	$values = $user->getFieldArray($fldName);

        	foreach ($values as $value)
        	{
				$row = new XmlTableRowCollection();

				$col = new XmlTableColumnCollection();
				if (($fldName != $users->getUserTable()->name) && ($fldName != $users->getUserTable()->username) && ($fldName != $users->getUserTable()->email) && ($fldName != $users->getUserTable()->password) && ($fldName != $users->getUserTable()->created) && ($fldName != $users->getUserTable()->admin) && ($fldName != $users->getUserTable()->id))
				{
					$href = new XmlAnchorCollection("module:Xmlnuke.Admin.ManageUsers?action=removecustomvalue&customfield=" . $fldName . "&valueid=".$uid."&customvalue=" . urlencode($value), "");
					$href->addXmlnukeObject(new XmlnukeText($this->myWords->Value("TXT_REMOVE")));
					$col->addXmlnukeObject($href);
				}
				else
				{
					$col->addXmlnukeObject(new XmlnukeText("---"));
				}
				$row->addXmlnukeObject($col);

				$col = new XmlTableColumnCollection();
				$col->addXmlnukeObject(new XmlnukeText($fldName));
				$row->addXmlnukeObject($col);

				$col = new XmlTableColumnCollection();
				$col->addXmlnukeObject(new XmlnukeText($value));
				$row->addXmlnukeObject($col);
				$table->addXmlnukeObject($row);
        	}
        }

		$paragraph = new XmlParagraphCollection();
		$paragraph->addXmlnukeObject($table);
        $block2->addXmlnukeObject($paragraph);

		$table = new XmlTableCollection();
		$row = new XmlTableRowCollection();

		$col = new XmlTableColumnCollection();

		$form =  new XmlFormCollection($this->_context, $this->url, $this->myWords->Value("GRIDVALUE"));
		$form->setJSValidate(true);
		$form->addXmlnukeObject(new XmlInputHidden("action", "addcustomvalue"));
		$form->addXmlnukeObject(new XmlInputHidden("curpage", $this->_context->get("curpage")));
		$form->addXmlnukeObject(new XmlInputHidden("valueid", $uid));
		$textbox = new XmlInputTextBox($this->myWords->Value("FORMFIELD"), "customfield", "", 20);
		$textbox->setInputTextBoxType(InputTextBoxType::TEXT);
		$textbox->setDataType(INPUTTYPE::TEXT);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		$textbox = new XmlInputTextBox($this->myWords->Value("FORMVALUE"), "customvalue", "", 40);
		$textbox->setInputTextBoxType(InputTextBoxType::TEXT);
		$textbox->setDataType(INPUTTYPE::TEXT);
		$textbox->setRequired(true);
		$form->addXmlnukeObject($textbox);
		$boxButton = new XmlInputButtons();
		$boxButton->addSubmit($this->myWords->Value("TXT_ADD"), "");
		$form->addXmlnukeObject($boxButton);
		$col->addXmlnukeObject($form);

		$row->addXmlnukeObject($col);
		$table->addXmlnukeObject($row);
		$paragraph = new XmlParagraphCollection();
		$paragraph->addXmlnukeObject($table);
        $block2->addXmlnukeObject($paragraph);

        $tabview->addTabItem($this->myWords->Value("TABCUSTOMVALUE"), $block2, (($this->_action == "addcustomvalue")||($this->_action == "removecustomvalue")));
        $this->defaultXmlnukeDocument->addXmlnukeObject($block);
	}

	/**
	 * Get all roles
	 *
	 * @param UsersBase $users
	 * @return array
	 */
	protected function getAllRoles($users)
	{
		$dataArray = array();
		$it = $users->getRolesIterator('_all');
		while ($it->hasNext()) {
			$sr = $it->moveNext();
//			$site = $sr->getField($users->getRolesTable()->Site);
			$dataArrayRoles = $sr->getFieldArray($users->getRolesTable()->Role);
			if (sizeof($dataArrayRoles) > 0) {
				foreach ($dataArrayRoles as $roles) {
					$dataArray[$roles] = $roles;
				}
			}
		}
		return $dataArray;
	}

}
?>
