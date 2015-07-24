<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
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
namespace Xmlnuke\Util;

use PHPMailer;
use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\IteratorFilter;
use Xmlnuke\Core\Classes\MailEnvelope;
use Xmlnuke\Core\Enum\Relation;
use Xmlnuke\Core\Processor\AnydatasetFilenameProcessor;

class MailUtil
{
	/**
	 * Send email using Smtp send mail object.
	 * Some code from: http://forum.wmonline.com.br/index.php?showtopic=119131
	 *
	 * @param String $fromEmail
	 * @param String $toEmail
	 * @param String $subject
	 * @param String $cc
	 * @param String $bcc
	 * @param String $body
	 * @param bool $htmlemail
	 * @param string $multparttext
	 * @param array() $multpartfiles
	 */
	public static function Mail($fromEmail, $toEmail, $subject, $cc, $bcc, $body, $htmlemail=false, $attachments=null, $embed=false)
	{
		$envelope = new MailEnvelope($toEmail, $subject, $body, $htmlemail);
		$envelope->setFrom($fromEmail);
		$envelope->setCC($cc);
		$envelope->setBCC($bcc);

		if (is_array($attachments))
		{
			foreach ($attachments as $name=>$value)
				$envelope->addAttachment($name, $value);
		}
		$envelope->setIsEmbbed($embed);
		
		$envelope->Send();
	}

	/**
	 * Get email from Id. If no parameter is passed assume the id "DEFAULT"
	 *
	 * @param String $IDEmail
	 * @return String
	 */
	public static function getEmailFromID($IDEmail = "DEFAULT", $name = "")
	{
		$configFile = new AnydatasetFilenameProcessor("_configemail");
		$config = new AnyDataset($configFile);
		$filter = new IteratorFilter();
		$filter->addRelation("destination_id", Relation::Equal, $IDEmail);
		$it = $config->getIterator($filter);
		if ($it->hasNext())
		{       //string
			$data = $it->moveNext();
			return MailUtil::getFullEmailName($name == "" ? $data->getField("name") : $name, $data->getField("email"));
		}
		else
		{
			return "";
		}
	}

	
	/**
	 * Get Full Email Name
	 *
	 * @param String $name
	 * @param String $email
	 * @return String
	 */
	public static function getFullEmailName($name, $email)
	{
		if (!empty($name))
			return "\"" . $name . "\" <".$email.">";
		else
			return $email;
	}
	
	public static function getEmailPair($fullEmail)
	{
		$pat = "/[\"'](?P<name>[\S\s]*)[\"']\s+<(?P<email>.*)>/";
		$pat2 = "/<(?P<email>.*)>/";

		$email = $fullEmail;
		$name = "";
		
		if (preg_match ( $pat, $fullEmail, $parts ))
		{
			if (array_key_exists("name", $parts))
				$name = ConvertFromUTF8::ISO88591_ASCII($parts["name"]);

			if (array_key_exists("email", $parts))
				$email = $parts["email"];
		}
		else if (preg_match($pat2, $fullEmail, $parts))
		{
			if (array_key_exists("email", $parts))
				$email = $parts["email"];			
		}
		
		return array("email"=>$email, "name"=>$name);
	}

	public static function isValidEmail($email)
	{
		$ret = PHPMailer::ValidateAddress($email);
		return (is_numeric($ret) ? $ret == 1 : $ret);
	}
}


?>