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
 * Login is a default module descendant from BaseModule class.
 * This class shows/edit the profile from the current user.
 *
 * @package xmlnuke
 */
abstract class LoginBase extends BaseModule
{

	/**
	 * Store return url
	 *
	 * @var String
	 */
	protected $_urlReturn;

	/**
	 * Default constructor
	 *
	 * @return Login
	 */
	public function LoginBase()
	{}

	/**
	 * Returns if use cache
	 *
	 * @return bool
	 */
	public function useCache()
	{
		return false;
	}

	/**
	 * Return the LanguageCollection used in this module
	 *
	 * @return LanguageCollection
	 */
	public function WordCollection()
	{
		$myWords = parent::WordCollection();

		if (!$myWords->loadedFromFile())
		{
			// English Words
			$myWords->addText("en-us", "TITLE", "Module Login");

			// Portuguese Words
			$myWords->addText("pt-br", "TITLE", "Módulo de Login");
		}

		return $myWords;
	}

	/**
	 * Update Info
	 *
	 * @param String $usernamevalid
	 * @param String $id
	 */
	protected function updateInfo($usernamevalid, $id)
	{
		$this->_context->MakeLogin($usernamevalid, $id);
		$url = XmlnukeManageUrl::decodeParam($this->_urlReturn);
		$this->_context->redirectUrl($url);
	}

	/**
	 * Make a random password
	 *
	 * @return string
	 */
	public function getRandomPassword()
	{
		//Random rand = new Random();
		//int type, number;
		$password = "";
		for($i=0; $i<7; $i++)
		{
			$type = rand(0,21) % 3;
			$number = rand(0,25);
			if ($type == 1)
			{
				$password = $password . chr(48 + ($number%10));
			}
			else
			{
				if ($type == 2)
				{
					$password = $password  . chr(65 + $number);

				}
				else
				{
					$password  = $password . chr(97 + $number);

				}
			}
		}
		return $password;
	}

	/**
	 * Send a email with user data profile
	 *
	 * @param LanguageCollection $myWords
	 * @param String $name
	 * @param String $user
	 * @param String $email
	 * @param String $password
	 */
	protected function sendWelcomeMessage($myWords, $name, $user, $email, $password)
	{
		$path = $this->_context->ContextValue("SCRIPT_NAME");
		$path = substr($path,0,strrpos($path,"/")+1);
		$url = "http://" . $this->_context->ContextValue("SERVER_NAME").$path;
		$body = $myWords->ValueArgs("WELCOMEMESSAGE", array($name, $this->_context->ContextValue("SERVER_NAME"), $user, $password, $url.$this->_context->bindModuleUrl("UserProfile")));

		$envelope = new MailEnvelope(
			MailUtil::getFullEmailName($name, $email),
			$myWords->Value("SUBJECTMESSAGE", "[" . $this->_context->ContextValue("SERVER_NAME") . "]"),
			$body
		);
		$envelope->Send();
	}
}
?>
