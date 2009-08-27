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
require_once(PHPXMLNUKEDIR . "bin/modules/phpmailer/class.phpmailer.php");

class MailUtil
{
	/**
	 * Send email using Smtp send mail object.
	 * Some code from: http://forum.wmonline.com.br/index.php?showtopic=119131
	 *
	 * @param Context $context
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
	public static function Mail($context, $fromEmail, $toEmail, $subject, $cc, $bcc, $body, $htmlemail=false, $attachments=null, $embed=false)
	{
		if ($toEmail == "")
		{
			throw new ModuleException("Destination Email was not provided");
		}

		if ($fromEmail == "")
		{
			throw new ModuleException("Source Email was not provided");
		}

		$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
		$mail->Subject = ConvertFromUTF8::ISO88591_ASCII($subject);
		$mail->CharSet = "utf-8";
		if ($htmlemail)
		{
			$mail->MsgHTML($body);
		}
		else
		{
			$mail->Body = $body;
		}

		/*
	    [0] => --IGNORE--
	    [1] => USERNAME
	    [2] => PASSWORD
	    [3] => SERVER
	    [4] => PORT
	    
	    smtp://[USERNAME:PASSWORD@]SERVER[:PORT]
    	*/

		$smtpString = $context->ContextValue("xmlnuke.SMTPSERVER");

		// Define if uses SMTP server or just sendemail
		if ($smtpString != "")
		{
			$pat = "/(smtp|ssl):\/\/(?:([\w\d\.\-#@!$%&]+):([\w\d\.\-#@!$%&]+)@)?(?:([\w\d\-]+(?:\.[\w\d\-]+)*))(?::([\d]+))?/";
			$parts = preg_split ( $pat, $smtpString, - 1, PREG_SPLIT_DELIM_CAPTURE );
			
			if ($parts[4] != "")
			{
				$mail->IsSMTP(); // telling the class to use SMTP
		
				$mail->Host = $parts[4];
				$mail->Port = ($parts[5] != "" ? $parts[5] : 25);
				
				if ($parts[2] != "")
				{
					$mail->SMTPAuth = true;
  					$mail->Username = $parts[2]; // SMTP account username
  					$mail->Password = $parts[3];        // SMTP account password
				}
				if ($parts[1]=="ssl")
				{
					$mail->SMTPSecure = "ssl";
				}
			}
		}
		
		// Define From email
		$from = MailUtil::getEmailPair($fromEmail);
		$mail->SetFrom($from["email"], $from["name"]);
		$mail->AddReplyTo($from["email"], $from["name"]);
		
		// Add Recipients
		if (is_array($toEmail))
		{
			foreach($toEmail as $toItem)
			{
				$to = MailUtil::getEmailPair($toItem);
				$mail->AddAddress($to["email"], $to["name"]);
			}
		}
		elseif (!empty($toEmail))
		{
			$to = MailUtil::getEmailPair($toEmail);
			$mail->AddAddress($to["email"], $to["name"]);
		}

		// Add Carbon Copy
		if (is_array($cc))
		{
			foreach($cc as $ccItem)
			{
				$to = MailUtil::getEmailPair($toItem);
				$mail->AddCC($to["email"], $to["name"]);
			}
		}
		elseif (!empty($cc))
		{
			$to = MailUtil::getEmailPair($cc);
			$mail->AddCC($to["email"], $to["name"]);
		}

		// Add Blind Carbon Copy
		if (is_array($bcc))
		{
			foreach($bcc as $toItem)
			{
				$to = MailUtil::getEmailPair($toItem);
				$mail->AddBCC($to["email"], $to["name"]);
			}
		}
		elseif (!empty($bcc))
		{
			$to = MailUtil::getEmailPair($bcc);
			$mail->AddBCC($to["email"], $to["name"]);
		}

		// Attachments
		if (!is_null($attachments))
		{
			foreach ($attachments as $key=>$value)
			{
				if ($embed)
				{
					$mail->AddEmbeddedImage($value, $key);
				}
				else
				{
					$mail->AddAttachment($value);
				}
			}
		}
		
		if (!$mail->Send())
		{
			throw new Exception($mail->ErrorInfo);
		}
	}

	/**
	 * Get email from Id
	 *
	 * @param Context $context
	 * @param String $IDEmail
	 * @return String
	 */
	public static function getEmailFromID($context, $IDEmail)
	{
		$configFile = new AnydatasetFilenameProcessor("_configemail", $context);		
		$config = new AnyDataSet($configFile);
		$filter = new IteratorFilter();
		$filter->addRelation("destination_id", Relation::Equal, $IDEmail);
		$it = $config->getIterator($filter);
		if ($it->hasNext())
		{       //string
			$data = $it->moveNext();
			return MailUtil::getFullEmailName($data->getField("name"), $data->getField("email"));
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
		return "\"" . $name . "\" <".$email.">";
	}
	
	public static function getEmailPair($fullEmail)
	{
		$pat = "/[\"']?([\pL\w\d\s\.&\(\)#$%]*)[\"']?\s*<(.*)>/";
		$parts = preg_split ( $pat, $fullEmail, - 1, PREG_SPLIT_DELIM_CAPTURE );
		
		if ($parts[2] == "")
		{
			return array("email"=>$fullEmail, "name"=>"");
		}
		else
		{
			return array("email"=>$parts[2], "name"=>ConvertFromUTF8::ISO88591_ASCII($parts[1]));
		}
	}

	public static function isValidEmail($email)
	{
		return PHPMailer::ValidateAddress($email);
	}
}
?>