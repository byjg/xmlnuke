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
	public static function Mail($context, $fromEmail, $toEmail, $subject, $cc, $bcc, $body, $htmlemail=false, $multparttext=null, $multpartfiles=null)
	{
		if ($toEmail == "")
		{
			throw new ModuleException("Destination Email was not provided");
		}

		if ($fromEmail == "")
		{
			throw new ModuleException("Source Email for was not provided");
		}

		//Adjust for UTF8 Enconding
		$fromEmail = utf8_decode($fromEmail);
		$toEmail = utf8_decode($toEmail);
		$subject = ConvertFromUTF8::ISO88591_ASCII($subject);
		$body = utf8_decode($body);

		// Write the users message to the body of the email
		/*
		$body .= "\n\n--------\n" .
		"eMail sent from site " . $context->ContextValue("SERVER_NAME") . " at " . date("Y-m-d H:i:s") .
		"\nuser " . $context->ContextValue("Remote_Host") .
		"\nengine " . $context->XmlNukeVersion() .
		"\nxmlnuke.com";
		*/

		$headers  = "X-Mailer: " .$context->XmlNukeVersion(). "\n";
		$headers  .= "X-Host: " .$context->ContextValue("SERVER_NAME"). "\n";
		$headers .= "From: ".$fromEmail."\n";

		$sep0 = "===xml01";
		$sep1 = "===xml02";
		$multpart = false;
		
		// Send the message
		if($htmlemail)
		{
			$headers .= "MIME-Version: 1.0\n";
			if (is_null($multparttext) && is_null($multpartfiles))
			{
				$headers .= "Content-type: text/html; charset=iso-8859-1\n";
			}
			else 
			{
				$headers .= "Content-type: multipart/related; type=\"multipart/alternative\";\n";
				$headers .= "              boundary=\"$sep0\"\n";
				$multpart = true;			
			}
		}
		
		if ($multpart)
		{
			$multipartfilesmessage = "";
			if (!is_null($multpartfiles))
			{
				foreach ($multpartfiles as $key=>$value)
				{
					// Load Images
					$handle = fopen($value, "rb");
					$content = fread($handle, filesize($value));
					fclose($handle);
					$txtEnc = chunk_split(base64_encode($content));
					$cid = basename($value);
					
					$path_parts = pathinfo(basename($value));
					$ext = $path_parts['extension'];
					if ($ext == "jpg")
						$ext = "jpeg";

					// Aqui o código para uma imagem.
					// para mais imagens, copie e cole, alterando o nome "top"
					$multipartfilesmessage.= "--$sep0\n";
					$multipartfilesmessage.= "Content-Type: image/" . $ext . "; name=\"" . basename($value) ."\"\n";
					$multipartfilesmessage.= "Content-Transfer-Encoding: base64\n";
					$multipartfilesmessage.= "Content-ID: <$cid>\n";
					$multipartfilesmessage.= "\n$txtEnc\n";
					$multipartfilesmessage.= "\n";
					
					// Change CID
					$body = str_replace($key, "cid:$cid", $body);
				}
			}

			// Message			
			$mensagem = "--$sep0\n";
			$mensagem.= "Content-Type: multipart/alternative; boundary=\"$sep1\"\n";
			$mensagem.= "\n";

			if ( is_null($multparttext) || ($multparttext == "") )
			{
				$multparttext = "Your mail client doesn't support HTML messages.";
			}
			$mensagem.= "--$sep1\n";
			$mensagem.= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
			$mensagem.= "Content-Transfer-Encoding: 7bit\n";
			$mensagem.= "\n$multparttext\n";
			$mensagem.= "\n";
			
			$mensagem.= "--$sep1\n";
			$mensagem.= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
			$mensagem.= "Content-Transfer-Encoding: 7bit\n";
			$mensagem.= "\n$body\n";
			$mensagem.= "\n";
			
			$mensagem.= "--$sep1--\n";
			$mensagem.= "\n";
			
			// Add Multipart files
			$mensagem .= $multipartfilesmessage;

			// End of message
			$mensagem.= "--$sep0--";
			
			// Define the FULL message;
			$body = $mensagem;
		}
		

		@mail($toEmail, $subject, $body, $headers);

		if($cc != "")
		{
			@mail($cc, $subject, $body, $headers);
		}
		if($bcc != "")
		{
			@mail($bcc, $subject, $body, $headers);
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
		return "\"" . ConvertFromUTF8::ISO88591_ASCII($name) . "\" <".$email.">";
	}
}
?>