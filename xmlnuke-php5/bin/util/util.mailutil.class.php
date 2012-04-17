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
		$config = new AnyDataSet($configFile);
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


class MailEnvelope
{
	protected $_from = "";
	protected $_to = "";
	protected $_subject = "";
	protected $_replyTo = "";
	protected $_cc = "";
	protected $_bcc = "";
	protected $_body = "";
	protected $_isHtml = false;
	protected $_isEmbbed = false;
	
	protected $_attachment = null;

	/**
	 *   [0] => --IGNORE--
	 *   [1] => TIPO: smtp / ssl
	 *   [2] => USERNAME
	 *   [3] => PASSWORD
	 *   [4] => SERVER
	 *   [5] => PORT
	 *   
	 *   smtp://[USERNAME:PASSWORD@]SERVER[:PORT]
	 * 
	 * @var array 
	 */
	protected static $_smtpParts = null;

	public function __construct($to = "", $subject = "", $body = "", $isHtml = false) 
	{
		$this->_from = MailUtil::getEmailFromID();
		$this->_subject = $subject;
		$this->_isHtml = $isHtml;
		$this->_body = $body;
		$this->_to = $to;
		
		if (MailEnvelope::$_smtpParts == null)
		{
			$smtpString = Context::getInstance()->ContextValue("xmlnuke.SMTPSERVER");

			// Define if uses SMTP server or just sendemail
			if ($smtpString != "")
			{
				$pat = "/(smtp|ssl):\/\/(?:([\w\d\.\-#@!$%&\/]+):([\w\d\.\-#@!$%&\/]+)@)?(?:([\w\d\-]+(?:\.[\w\d\-]+)*))(?::([\d]+))?/";
				MailEnvelope::$_smtpParts = preg_split ( $pat, $smtpString, - 1, PREG_SPLIT_DELIM_CAPTURE );
			}
		}
	}
	
	public function addAttachment($name, $value)
	{
		if ($this->_attachment == null)
			$this->_attachment = array();
		
		$this->_attachment[$name] = $value;
	}
	
	public function getFrom()
	{
		return $this->_from;
	}
	public function setFrom($value)
	{
		$this->_from = $value;
	}

	public function getTo()
	{
		return $this->_to;
	}
	public function setTo($value)
	{
		$this->_to = $value;
	}

	public function getSubject()
	{
		return $this->_subject;
	}
	public function setSubject($value)
	{
		$this->_subject = $value;
	}

	public function getReplyTo()
	{
		return $this->_replyTo == "" ? $this->getFrom() : $this->_replyTo;
	}
	public function setReplyTo($value)
	{
		$this->_replyTo = $value;
	}

	public function getCC()
	{
		return $this->_cc;
	}
	public function setCC($value)
	{
		$this->_cc = $value;
	}

	public function getBCC()
	{
		return $this->_bcc;
	}
	public function setBCC($value)
	{
		$this->_bcc = $value;
	}

	public function getBody() 
	{
		return $this->_body;
	}
	public function setBody($value) 
	{
		$this->_body = $value;
	}

	public function getIsHtml() 
	{
		return $this->_isHtml;
	}
	public function setIsHtml($value) 
	{
		$this->_isHtml = $value;
	}
	
	public function getIsEmbbed() 
	{
		return $this->_isEmbbed;
	}

	public function setIsEmbbed($value) 
	{
		$this->_isEmbbed = $value;
	}
	
	public function getAttachments()
	{
		return $this->_attachment;
	}

		
	public function Send($to = "")
	{
		$context = Context::getInstance();
		
		if ($this->getTo() == "" && $to == "")
		{
			throw new ModuleException("Destination Email was not provided");
		}
		elseif ($to != "")
		{
			$this->setTo($to);
		}
		
		if ($this->getFrom() == "")
		{
			throw new ModuleException("Source Email was not provided");
		}

		$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
		$mail->Subject = ConvertFromUTF8::ISO88591_ASCII($this->getSubject());
		$mail->CharSet = "utf-8";
		if ($this->getIsHtml())
		{
			$mail->MsgHTML($this->getBody());
		}
		else
		{
			$mail->Body = $this->getBody();
		}


		// Define if uses SMTP server or just sendemail
		if (isset(MailEnvelope::$_smtpParts[1]) && MailEnvelope::$_smtpParts[1] != "sendmail")
		{
			if (MailEnvelope::$_smtpParts[4] != "")
			{
				$mail->IsSMTP(); // telling the class to use SMTP
		
				$mail->Host = MailEnvelope::$_smtpParts[4];
				$mail->Port = (MailEnvelope::$_smtpParts[5] != "" ? MailEnvelope::$_smtpParts[5] : 25);
				
				if (MailEnvelope::$_smtpParts[2] != "")
				{
					$mail->SMTPAuth = true;
  					$mail->Username = MailEnvelope::$_smtpParts[2]; // SMTP account username
  					$mail->Password = MailEnvelope::$_smtpParts[3];        // SMTP account password
				}
				
				if (MailEnvelope::$_smtpParts[1]=="ssl")
				{
					$mail->SMTPSecure = "ssl";
				}
			}
		}
		
		$replyTo = MailUtil::getEmailPair($this->getReplyTo());
		$mail->AddReplyTo($replyTo["email"], $replyTo["name"]);
		
		// Define From email
		$from = MailUtil::getEmailPair($this->getFrom());
		$mail->SetFrom($from["email"], $from["name"]);
		
		// Add Recipients
		if (is_array($this->getTo()))
		{
			foreach($this->getTo() as $toItem)
			{
				$to = MailUtil::getEmailPair($toItem);
				$mail->AddAddress($to["email"], $to["name"]);
			}
		}
		elseif ($this->getTo() != "")
		{
			$to = MailUtil::getEmailPair($this->getTo());
			$mail->AddAddress($to["email"], $to["name"]);
		}

		// Add Carbon Copy
		if (is_array($this->getCC()))
		{
			foreach($this->getCC() as $ccItem)
			{
				$to = MailUtil::getEmailPair($toItem);
				$mail->AddCC($to["email"], $to["name"]);
			}
		}
		elseif ($this->getCC() != "")
		{
			$to = MailUtil::getEmailPair($this->getCC());
			$mail->AddCC($to["email"], $to["name"]);
		}

		// Add Blind Carbon Copy
		if (is_array($this->getBCC()))
		{
			foreach($this->getBCC() as $toItem)
			{
				$to = MailUtil::getEmailPair($toItem);
				$mail->AddBCC($to["email"], $to["name"]);
			}
		}
		elseif ($this->getBCC() != "")
		{
			$to = MailUtil::getEmailPair($this->getBCC());
			$mail->AddBCC($to["email"], $to["name"]);
		}

		// Attachments
		if (!is_null($this->getAttachments()))
		{
			foreach ($this->getAttachments() as $key=>$value)
			{
				if ($this->getIsEmbbed())
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
	
}

?>